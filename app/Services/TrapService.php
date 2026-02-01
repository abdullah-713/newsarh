<?php

namespace App\Services;

use App\Models\TrapConfiguration;
use App\Models\TrapLog;
use App\Models\User;
use Illuminate\Support\Facades\Request;

class TrapService
{
    /**
     * تسجيل تفعيل فخ من قبل موظف
     */
    public function logTrapTrigger(
        int $trapConfigId,
        int $userId,
        array $additionalData = []
    ): TrapLog {
        $trap = TrapConfiguration::find($trapConfigId);
        
        if (!$trap) {
            throw new \Exception("Trap configuration not found");
        }

        // إنشاء سجل الفخ
        $trapLog = TrapLog::create([
            'trap_configuration_id' => $trapConfigId,
            'user_id' => $userId,
            'triggered_at' => now(),
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
            'metadata' => array_merge([
                'url' => Request::fullUrl(),
                'method' => Request::method(),
                'referer' => Request::header('referer'),
            ], $additionalData),
        ]);

        // تنفيذ الإجراء المحدد
        $this->executeTrapAction($trap, $trapLog);

        return $trapLog;
    }

    /**
     * تنفيذ الإجراء المحدد للفخ
     */
    protected function executeTrapAction(TrapConfiguration $trap, TrapLog $trapLog): void
    {
        $user = User::find($trapLog->user_id);

        switch ($trap->trigger_action) {
            case 'log_only':
                // لا شيء إضافي - فقط السجل
                break;

            case 'log_and_alert':
                // إرسال تنبيه للإدارة
                $this->sendAdminAlert($trap, $trapLog);
                break;

            case 'log_and_flag_user':
                // وضع علامة على المستخدم
                $this->flagUser($user, $trap);
                $this->sendAdminAlert($trap, $trapLog);
                break;

            case 'log_and_suspend':
                // تعليق حساب مؤقت
                $this->suspendUser($user, $trap);
                $this->sendAdminAlert($trap, $trapLog);
                break;
        }
    }

    /**
     * إرسال تنبيه للإدارة
     */
    protected function sendAdminAlert(TrapConfiguration $trap, TrapLog $trapLog): void
    {
        // TODO: Implement notification system
        // يمكن استخدام Laravel Notifications أو الجدول notifications
        \Log::warning("⚠️ TRAP TRIGGERED: {$trap->trap_name_ar} by User #{$trapLog->user_id}");
    }

    /**
     * وضع علامة على المستخدم
     */
    protected function flagUser(User $user, TrapConfiguration $trap): void
    {
        // إضافة علامة في ملف المستخدم
        $user->increment('trap_trigger_count');
        
        // يمكن إضافة حقل is_flagged أو إنشاء سجل في integrity_logs
    }

    /**
     * تعليق حساب مؤقت
     */
    protected function suspendUser(User $user, TrapConfiguration $trap): void
    {
        // تعليق الحساب لمدة محددة
        $user->update([
            'is_active' => false,
            'suspended_at' => now(),
        ]);
        
        // يمكن إضافة سجل في integrity_logs
    }

    /**
     * الحصول على الفخاخ النشطة للوحة محددة
     */
    public function getActiveTraps(string $panel = 'employee'): \Illuminate\Database\Eloquent\Collection
    {
        return TrapConfiguration::where('is_active', true)
            ->where(function($query) use ($panel) {
                $query->whereJsonContains('settings->target_panel', $panel)
                      ->orWhereJsonContains('settings->target_panel', 'both');
            })
            ->get();
    }

    /**
     * إنشاء فخ وهمي سريع
     */
    public function createQuickTrap(string $type, string $label, string $labelEn = ''): TrapConfiguration
    {
        return TrapConfiguration::create([
            'trap_type' => $type,
            'trap_name_ar' => $label,
            'trap_name' => $labelEn ?: $label,
            'is_active' => true,
            'settings' => [
                'trigger_action' => 'log_and_flag_user',
                'severity_level' => 7,
                'target_panel' => 'employee',
            ],
        ]);
    }
}
