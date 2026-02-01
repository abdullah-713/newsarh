<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Auth;

class PermissionService
{
    /**
     * Check if user has a specific permission
     */
    public static function can(string $permission, ?User $user = null): bool
    {
        $user = $user ?? Auth::user();
        
        if (!$user) {
            return false;
        }

        // Super Admin bypass
        if ($user->is_super_admin) {
            return true;
        }

        // Get user permissions (custom + role permissions)
        $userPermissions = self::getUserPermissions($user);

        return in_array($permission, $userPermissions);
    }

    /**
     * Check if user has ANY of the given permissions
     */
    public static function canAny(array $permissions, ?User $user = null): bool
    {
        foreach ($permissions as $permission) {
            if (self::can($permission, $user)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if user has ALL of the given permissions
     */
    public static function canAll(array $permissions, ?User $user = null): bool
    {
        foreach ($permissions as $permission) {
            if (!self::can($permission, $user)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get all user permissions (role + custom)
     */
    public static function getUserPermissions(User $user): array
    {
        $permissions = [];

        // Get role permissions
        if ($user->role) {
            $rolePermissions = $user->role->permissions;
            if (is_string($rolePermissions)) {
                $rolePermissions = json_decode($rolePermissions, true) ?? [];
            }
            $permissions = array_merge($permissions, $rolePermissions ?? []);
        }

        // Get custom user permissions
        $userPermissions = $user->permissions;
        if (is_string($userPermissions)) {
            $userPermissions = json_decode($userPermissions, true) ?? [];
        }
        $permissions = array_merge($permissions, $userPermissions ?? []);

        return array_unique($permissions);
    }

    /**
     * Check if user has minimum role level
     */
    public static function hasRoleLevel(int $minLevel, ?User $user = null): bool
    {
        $user = $user ?? Auth::user();
        
        if (!$user || !$user->role) {
            return false;
        }

        // Super Admin bypass
        if ($user->is_super_admin) {
            return true;
        }

        return $user->role->role_level >= $minLevel;
    }

    /**
     * Get all available permissions
     */
    public static function getAllPermissions(): array
    {
        return [
            'users' => [
                'users.view' => 'عرض المستخدمين',
                'users.create' => 'إضافة مستخدمين',
                'users.edit' => 'تعديل المستخدمين',
                'users.delete' => 'حذف المستخدمين',
            ],
            'attendance' => [
                'attendance.view' => 'عرض الحضور',
                'attendance.create' => 'تسجيل حضور',
                'attendance.edit' => 'تعديل الحضور',
                'attendance.delete' => 'حذف سجلات الحضور',
                'attendance.export' => 'تصدير الحضور',
            ],
            'branches' => [
                'branches.view' => 'عرض الفروع',
                'branches.create' => 'إضافة فروع',
                'branches.edit' => 'تعديل الفروع',
                'branches.delete' => 'حذف الفروع',
            ],
            'departments' => [
                'departments.view' => 'عرض الأقسام',
                'departments.create' => 'إضافة أقسام',
                'departments.edit' => 'تعديل الأقسام',
                'departments.delete' => 'حذف الأقسام',
            ],
            'shifts' => [
                'shifts.view' => 'عرض الورديات',
                'shifts.create' => 'إضافة ورديات',
                'shifts.edit' => 'تعديل الورديات',
                'shifts.delete' => 'حذف الورديات',
            ],
            'gamification' => [
                'gamification.view' => 'عرض التحفيز',
                'gamification.manage' => 'إدارة النقاط والشارات',
            ],
            'rewards' => [
                'rewards.view' => 'عرض المكافآت',
                'rewards.manage' => 'إدارة المكافآت',
            ],
            'reports' => [
                'reports.view' => 'عرض التقارير',
                'reports.export' => 'تصدير التقارير',
                'analytics.view' => 'عرض التحليلات',
            ],
            'traps' => [
                'traps.view' => 'عرض الفخاخ',
                'traps.manage' => 'إدارة الفخاخ',
                'integrity.view' => 'عرض تقارير النزاهة',
            ],
            'settings' => [
                'settings.view' => 'عرض الإعدادات',
                'settings.edit' => 'تعديل الإعدادات',
            ],
            'roles' => [
                'roles.view' => 'عرض الأدوار',
                'roles.create' => 'إضافة أدوار',
                'roles.edit' => 'تعديل الأدوار',
                'roles.delete' => 'حذف الأدوار',
            ],
            'system' => [
                'system.superadmin' => 'Super Admin - صلاحيات كاملة',
                'system.bypass_restrictions' => 'تجاوز القيود',
            ],
        ];
    }
}
