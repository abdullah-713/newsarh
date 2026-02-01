<?php

namespace App\Services;

use App\Models\Geofence;
use App\Models\TrackingLog;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class TrackingService
{
    protected NotificationService $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Log user location
     *
     * @param User $user
     * @param array $data
     * @return TrackingLog|null
     */
    public function logLocation(User $user, array $data): ?TrackingLog
    {
        try {
            $trackingLog = TrackingLog::create([
                'user_id' => $user->id,
                'latitude' => $data['latitude'],
                'longitude' => $data['longitude'],
                'battery_level' => $data['battery_level'] ?? null,
                'speed' => $data['speed'] ?? null,
                'accuracy' => $data['accuracy'] ?? null,
                'type' => $data['type'] ?? 'ping',
                'activity_type' => $data['activity_type'] ?? null,
                'is_mock_location' => $data['is_mock_location'] ?? false,
                'tracked_at' => $data['tracked_at'] ?? now(),
            ]);

            // Update user's last known location
            $user->update([
                'last_latitude' => $data['latitude'],
                'last_longitude' => $data['longitude'],
                'last_activity_at' => now(),
            ]);

            // Check geofence alerts
            $this->checkGeofenceAlerts($user, $data['latitude'], $data['longitude']);

            // Log suspicious activity (mock locations, etc.)
            if ($data['is_mock_location'] ?? false) {
                Log::warning('Mock location detected', [
                    'user_id' => $user->id,
                    'latitude' => $data['latitude'],
                    'longitude' => $data['longitude'],
                ]);
            }

            return $trackingLog;
        } catch (\Exception $e) {
            Log::error('Failed to log location', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Check if user has exited or entered any geofences
     *
     * @param User $user
     * @param float $latitude
     * @param float $longitude
     * @return void
     */
    protected function checkGeofenceAlerts(User $user, float $latitude, float $longitude): void
    {
        $activeGeofences = Geofence::where('is_active', true)->get();

        foreach ($activeGeofences as $geofence) {
            $isInside = $geofence->contains($latitude, $longitude);
            $wasInside = $this->wasUserInsideGeofence($user, $geofence);

            // Exit alert
            if ($wasInside && !$isInside && in_array($geofence->alert_type, ['exit', 'both'])) {
                $this->sendGeofenceAlert($user, $geofence, 'exit');
            }

            // Entry alert
            if (!$wasInside && $isInside && in_array($geofence->alert_type, ['entry', 'both'])) {
                $this->sendGeofenceAlert($user, $geofence, 'entry');
            }
        }
    }

    /**
     * Check if user was inside geofence based on last tracking log
     *
     * @param User $user
     * @param Geofence $geofence
     * @return bool
     */
    protected function wasUserInsideGeofence(User $user, Geofence $geofence): bool
    {
        $lastLog = TrackingLog::where('user_id', $user->id)
            ->where('id', '!=', TrackingLog::latest()->first()?->id)
            ->latest('tracked_at')
            ->first();

        if (!$lastLog) {
            return false;
        }

        return $geofence->contains($lastLog->latitude, $lastLog->longitude);
    }

    /**
     * Send geofence alert notification
     *
     * @param User $user
     * @param Geofence $geofence
     * @param string $alertType
     * @return void
     */
    protected function sendGeofenceAlert(User $user, Geofence $geofence, string $alertType): void
    {
        $messages = [
            'exit' => [
                'title' => '⚠️ تنبيه خروج من المنطقة',
                'body' => "لقد خرجت من منطقة: {$geofence->name}",
            ],
            'entry' => [
                'title' => '✅ دخول إلى المنطقة',
                'body' => "لقد دخلت إلى منطقة: {$geofence->name}",
            ],
        ];

        $message = $messages[$alertType] ?? $messages['exit'];

        $this->notificationService->sendDatabaseNotification(
            $user,
            $message['title'],
            $message['body'],
            'warning',
            null,
            [
                'geofence_id' => $geofence->id,
                'geofence_name' => $geofence->name,
                'alert_type' => $alertType,
            ]
        );

        Log::info('Geofence alert sent', [
            'user_id' => $user->id,
            'geofence_id' => $geofence->id,
            'alert_type' => $alertType,
        ]);
    }

    /**
     * Get user's location history
     *
     * @param User $user
     * @param int $limit
     * @param string|null $type
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getLocationHistory(User $user, int $limit = 100, ?string $type = null)
    {
        $query = TrackingLog::where('user_id', $user->id)
            ->orderBy('tracked_at', 'desc')
            ->limit($limit);

        if ($type) {
            $query->where('type', $type);
        }

        return $query->get();
    }

    /**
     * Get user's route for a specific date
     *
     * @param User $user
     * @param string $date
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getUserRoute(User $user, string $date)
    {
        return TrackingLog::where('user_id', $user->id)
            ->whereDate('tracked_at', $date)
            ->orderBy('tracked_at', 'asc')
            ->get();
    }

    /**
     * Calculate total distance traveled
     *
     * @param User $user
     * @param string $date
     * @return float Distance in kilometers
     */
    public function calculateDistanceTraveled(User $user, string $date): float
    {
        $route = $this->getUserRoute($user, $date);
        $totalDistance = 0;

        for ($i = 1; $i < $route->count(); $i++) {
            $prevPoint = $route[$i - 1];
            $currPoint = $route[$i];

            $distance = $prevPoint->distanceFrom(
                $currPoint->latitude,
                $currPoint->longitude
            );

            $totalDistance += $distance;
        }

        // Convert to kilometers
        return round($totalDistance / 1000, 2);
    }

    /**
     * Delete old tracking logs
     *
     * @param int $daysOld
     * @return int
     */
    public function deleteOldLogs(int $daysOld = 90): int
    {
        try {
            return TrackingLog::where('tracked_at', '<', now()->subDays($daysOld))
                ->delete();
        } catch (\Exception $e) {
            Log::error('Failed to delete old tracking logs', [
                'error' => $e->getMessage(),
            ]);

            return 0;
        }
    }
}
