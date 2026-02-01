<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\Branch;
use App\Models\User;
use App\Models\UserShiftAssignment;
use App\Models\SystemSetting;
use App\Services\GamificationService;
use Carbon\Carbon;

class AttendanceService
{
    /**
     * Calculate distance between two coordinates using Haversine formula
     * 
     * @param float $lat1 Latitude of point 1
     * @param float $lon1 Longitude of point 1
     * @param float $lat2 Latitude of point 2
     * @param float $lon2 Longitude of point 2
     * @return float Distance in meters
     */
    public function calculateDistance(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadius = 6371000; // Earth's radius in meters

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        $distance = $earthRadius * $c;

        return round($distance, 2);
    }

    /**
     * Check if user location is within branch geofence
     * 
     * @param float $userLat User's latitude
     * @param float $userLon User's longitude
     * @param Branch $branch The branch to check against
     * @return bool True if within range
     */
    public function isWithinRange(float $userLat, float $userLon, Branch $branch): bool
    {
        if (!$branch->latitude || !$branch->longitude || !$branch->geofence_radius) {
            return false;
        }

        $distance = $this->calculateDistance(
            $userLat,
            $userLon,
            (float) $branch->latitude,
            (float) $branch->longitude
        );

        return $distance <= $branch->geofence_radius;
    }

    /**
     * Process check-in for a user
     * 
     * @param User $user
     * @param float $lat
     * @param float $lon
     * @return array
     */
    public function checkIn(User $user, float $lat, float $lon): array
    {
        // Check if user already checked in today
        $today = Carbon::today();
        $existingAttendance = Attendance::where('user_id', $user->id)
            ->whereDate('date', $today)
            ->first();

        if ($existingAttendance && $existingAttendance->check_in_time) {
            return [
                'success' => false,
                'message' => 'لقد قمت بتسجيل الحضور بالفعل اليوم'
            ];
        }

        // Validate location
        if (!$user->branch) {
            return [
                'success' => false,
                'message' => 'لا يوجد فرع مرتبط بحسابك'
            ];
        }

        $distance = $this->calculateDistance(
            $lat,
            $lon,
            (float) $user->branch->latitude,
            (float) $user->branch->longitude
        );

        $withinRange = $this->isWithinRange($lat, $lon, $user->branch);

        // Calculate late minutes based on shift
        $lateCalculation = $this->calculateLateMinutes($user, Carbon::now());

        // Create or update attendance record
        $attendance = Attendance::updateOrCreate(
            [
                'user_id' => $user->id,
                'date' => $today,
            ],
            [
                'check_in_time' => Carbon::now(),
                'check_in_lat' => $lat,
                'check_in_lng' => $lon,
                'check_in_distance' => $distance,
                'check_in_location_verified' => $withinRange,
                'branch_id' => $user->branch_id,
                'late_minutes' => $lateCalculation['late_minutes'],
                'is_late' => $lateCalculation['is_late'],
                'shift_id' => $lateCalculation['shift_id'],
            ]
        );

        return [
            'success' => true,
            'message' => 'تم تسجيل الحضور بنجاح',
            'attendance' => $attendance,
            'distance' => $distance,
            'within_range' => $withinRange
        ];
    }

    /**
     * Process check-out for a user
     * 
     * @param User $user
     * @param float $lat
     * @param float $lon
     * @return array
     */
    public function checkOut(User $user, float $lat, float $lon): array
    {
        $today = Carbon::today();
        $attendance = Attendance::where('user_id', $user->id)
            ->whereDate('date', $today)
            ->first();

        if (!$attendance) {
            return [
                'success' => false,
                'message' => 'لم تقم بتسجيل الحضور اليوم'
            ];
        }

        if (!$attendance->check_in_time) {
            return [
                'success' => false,
                'message' => 'يجب تسجيل الحضور أولاً'
            ];
        }

        if ($attendance->check_out_time) {
            return [
                'success' => false,
                'message' => 'لقد قمت بتسجيل الانصراف بالفعل'
            ];
        }

        $distance = $this->calculateDistance(
            $lat,
            $lon,
            (float) $user->branch->latitude,
            (float) $user->branch->longitude
        );

        $withinRange = $this->isWithinRange($lat, $lon, $user->branch);

        // Calculate work duration
        $checkInTime = Carbon::parse($attendance->check_in_time);
        $checkOutTime = Carbon::now();
        $workMinutes = $checkOutTime->diffInMinutes($checkInTime);

        $attendance->update([
            'check_out_time' => $checkOutTime,
            'check_out_lat' => $lat,
            'check_out_lng' => $lon,
            'check_out_distance' => $distance,
            'check_out_location_verified' => $withinRange,
            'work_minutes' => $workMinutes,
        ]);

        // Trigger Gamification System
        $gamificationService = app(GamificationService::class);
        $awardedBadges = $gamificationService->checkAndAwardBadges($user);
        
        // Calculate and add points for this attendance
        $points = $gamificationService->calculateAttendancePoints($attendance);
        if ($points > 0) {
            $user->increment('current_points', $points);
            $user->increment('total_points_earned', $points);
        }

        return [
            'success' => true,
            'message' => 'تم تسجيل الانصراف بنجاح',
            'attendance' => $attendance,
            'distance' => $distance,
            'within_range' => $withinRange,
            'work_minutes' => $workMinutes,
            'points_earned' => $points,
            'badges_awarded' => $awardedBadges,
        ];
    }

    /**
     * Calculate late minutes based on user's shift
     * 
     * @param User $user
     * @param Carbon $checkInTime
     * @return array
     */
    private function calculateLateMinutes(User $user, Carbon $checkInTime): array
    {
        $today = Carbon::today();
        $dayOfWeek = $checkInTime->dayOfWeek; // 0 = Sunday, 6 = Saturday

        // Try to find active shift assignment for today
        $shiftAssignment = UserShiftAssignment::where('user_id', $user->id)
            ->where('is_active', true)
            ->where('effective_from', '<=', $today)
            ->where(function ($query) use ($today) {
                $query->whereNull('effective_until')
                    ->orWhere('effective_until', '>=', $today);
            })
            ->first();

        if (!$shiftAssignment || !$shiftAssignment->workShift) {
            // No shift assigned - use system default
            return $this->calculateLateFromSystemDefaults($checkInTime);
        }

        $shift = $shiftAssignment->workShift;
        
        // Parse shift start time
        $shiftStartTime = Carbon::parse($shift->start_time);
        $expectedCheckIn = $checkInTime->copy()
            ->setHour($shiftStartTime->hour)
            ->setMinute($shiftStartTime->minute)
            ->setSecond(0);

        // Get grace period from system settings or default to 15 minutes
        $gracePeriod = $this->getGracePeriod();
        $allowedCheckIn = $expectedCheckIn->copy()->addMinutes($gracePeriod);

        // Calculate late minutes
        $lateMinutes = 0;
        $isLate = false;

        if ($checkInTime->gt($allowedCheckIn)) {
            $lateMinutes = $checkInTime->diffInMinutes($expectedCheckIn);
            $isLate = true;
        }

        return [
            'late_minutes' => $lateMinutes,
            'is_late' => $isLate,
            'shift_id' => $shift->id,
            'expected_time' => $expectedCheckIn->format('H:i:s'),
        ];
    }

    /**
     * Calculate late minutes using system default settings
     * 
     * @param Carbon $checkInTime
     * @return array
     */
    private function calculateLateFromSystemDefaults(Carbon $checkInTime): array
    {
        // Get default start time from system settings (default 08:00)
        $defaultStartTime = $this->getSystemSetting('default_start_time', '08:00:00');
        $gracePeriod = $this->getGracePeriod();

        $expectedCheckIn = $checkInTime->copy()
            ->setTimeFromTimeString($defaultStartTime);
        
        $allowedCheckIn = $expectedCheckIn->copy()->addMinutes($gracePeriod);

        $lateMinutes = 0;
        $isLate = false;

        if ($checkInTime->gt($allowedCheckIn)) {
            $lateMinutes = $checkInTime->diffInMinutes($expectedCheckIn);
            $isLate = true;
        }

        return [
            'late_minutes' => $lateMinutes,
            'is_late' => $isLate,
            'shift_id' => null,
            'expected_time' => $expectedCheckIn->format('H:i:s'),
        ];
    }

    /**
     * Get grace period in minutes from system settings
     * 
     * @return int
     */
    private function getGracePeriod(): int
    {
        return (int) $this->getSystemSetting('grace_period_minutes', 15);
    }

    /**
     * Get system setting value
     * 
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    private function getSystemSetting(string $key, mixed $default = null): mixed
    {
        $setting = SystemSetting::where('setting_key', $key)->first();
        return $setting ? $setting->setting_value : $default;
    }

    /**
     * Get today's attendance for a user
     * 
     * @param User $user
     * @return Attendance|null
     */
    public function getTodayAttendance(User $user): ?Attendance
    {
        return Attendance::where('user_id', $user->id)
            ->whereDate('date', Carbon::today())
            ->first();
    }
}
