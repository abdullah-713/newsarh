<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    /**
     * Send a database notification to a user
     *
     * @param User $user
     * @param string $title
     * @param string $body
     * @param string $type (info, success, warning, error)
     * @param string|null $actionUrl
     * @param array $data Additional data
     * @return Notification|null
     */
    public function sendDatabaseNotification(
        User $user,
        string $title,
        string $body,
        string $type = 'info',
        ?string $actionUrl = null,
        array $data = []
    ): ?Notification {
        try {
            $notification = Notification::create([
                'user_id' => $user->id,
                'title' => $title,
                'body' => $body,
                'type' => $type,
                'action_url' => $actionUrl,
                'data' => $data,
                'is_read' => false,
                'read_at' => null,
            ]);

            Log::info('Database notification sent', [
                'user_id' => $user->id,
                'title' => $title,
                'type' => $type,
            ]);

            return $notification;
        } catch (\Exception $e) {
            Log::error('Failed to send database notification', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Send database notifications to multiple users
     *
     * @param array|\Illuminate\Support\Collection $users
     * @param string $title
     * @param string $body
     * @param string $type
     * @param string|null $actionUrl
     * @param array $data
     * @return int Number of notifications sent
     */
    public function sendBulkDatabaseNotifications(
        $users,
        string $title,
        string $body,
        string $type = 'info',
        ?string $actionUrl = null,
        array $data = []
    ): int {
        $sentCount = 0;

        foreach ($users as $user) {
            if ($this->sendDatabaseNotification($user, $title, $body, $type, $actionUrl, $data)) {
                $sentCount++;
            }
        }

        Log::info('Bulk database notifications sent', [
            'total_users' => count($users),
            'sent_count' => $sentCount,
        ]);

        return $sentCount;
    }

    /**
     * Send a push notification (Web Push API)
     * Future implementation - placeholder for now
     *
     * @param User $user
     * @param string $title
     * @param string $body
     * @param array $options
     * @return bool
     */
    public function sendPushNotification(
        User $user,
        string $title,
        string $body,
        array $options = []
    ): bool {
        // TODO: Implement Web Push Notification
        // This will use the PushSubscription model to send notifications
        // to the user's subscribed devices
        
        Log::info('Push notification queued (not yet implemented)', [
            'user_id' => $user->id,
            'title' => $title,
        ]);

        return false;
    }

    /**
     * Mark a notification as read
     *
     * @param Notification $notification
     * @return bool
     */
    public function markAsRead(Notification $notification): bool
    {
        try {
            $notification->update([
                'is_read' => true,
                'read_at' => now(),
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to mark notification as read', [
                'notification_id' => $notification->id,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Mark all notifications as read for a user
     *
     * @param User $user
     * @return int Number of notifications marked
     */
    public function markAllAsRead(User $user): int
    {
        try {
            return Notification::where('user_id', $user->id)
                ->where('is_read', false)
                ->update([
                    'is_read' => true,
                    'read_at' => now(),
                ]);
        } catch (\Exception $e) {
            Log::error('Failed to mark all notifications as read', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);

            return 0;
        }
    }

    /**
     * Get unread notifications for a user
     *
     * @param User $user
     * @param int $limit
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getUnreadNotifications(User $user, int $limit = 10)
    {
        return Notification::where('user_id', $user->id)
            ->where('is_read', false)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get all notifications for a user
     *
     * @param User $user
     * @param int $limit
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAllNotifications(User $user, int $limit = 50)
    {
        return Notification::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Delete old read notifications
     *
     * @param int $daysOld
     * @return int Number of deleted notifications
     */
    public function deleteOldNotifications(int $daysOld = 30): int
    {
        try {
            return Notification::where('is_read', true)
                ->where('read_at', '<', now()->subDays($daysOld))
                ->delete();
        } catch (\Exception $e) {
            Log::error('Failed to delete old notifications', [
                'error' => $e->getMessage(),
            ]);

            return 0;
        }
    }

    /**
     * Send attendance reminder notification
     *
     * @param User $user
     * @param string $reminderType (check_in, check_out)
     * @return Notification|null
     */
    public function sendAttendanceReminder(User $user, string $reminderType): ?Notification
    {
        $messages = [
            'check_in' => [
                'title' => 'تذكير بتسجيل الدخول',
                'body' => 'لا تنسَ تسجيل دخولك للعمل اليوم!',
            ],
            'check_out' => [
                'title' => 'تذكير بتسجيل الخروج',
                'body' => 'لا تنسَ تسجيل خروجك من العمل!',
            ],
        ];

        $message = $messages[$reminderType] ?? $messages['check_in'];

        return $this->sendDatabaseNotification(
            $user,
            $message['title'],
            $message['body'],
            'info',
            route('filament.employee.pages.dashboard')
        );
    }

    /**
     * Send gamification notification
     *
     * @param User $user
     * @param string $event (badge_earned, points_earned, level_up)
     * @param array $data
     * @return Notification|null
     */
    public function sendGamificationNotification(User $user, string $event, array $data = []): ?Notification
    {
        $messages = [
            'badge_earned' => [
                'title' => '🏆 شارة جديدة!',
                'body' => "مبروك! لقد حصلت على شارة: {$data['badge_name']}",
                'type' => 'success',
            ],
            'points_earned' => [
                'title' => '⭐ نقاط جديدة!',
                'body' => "لقد كسبت {$data['points']} نقطة!",
                'type' => 'success',
            ],
            'level_up' => [
                'title' => '🎉 مستوى جديد!',
                'body' => "تهانينا! لقد وصلت إلى المستوى {$data['level']}",
                'type' => 'success',
            ],
        ];

        $message = $messages[$event] ?? [
            'title' => 'إشعار',
            'body' => 'لديك تحديث جديد',
            'type' => 'info',
        ];

        return $this->sendDatabaseNotification(
            $user,
            $message['title'],
            $message['body'],
            $message['type'],
            route('filament.employee.pages.my-badges'),
            $data
        );
    }
}
