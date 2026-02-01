<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        // إنشاء دور Super Admin إذا لم يكن موجوداً
        $role = Role::firstOrCreate(
            ['slug' => 'super-admin'],
            [
                'name' => 'Super Admin',
                'role_level' => 10,
                'color' => '#FF0000',
                'is_active' => true,
                'permissions' => json_encode(['*']),
            ]
        );

        // إنشاء مستخدم Super Admin
        $user = User::updateOrCreate(
            ['email' => 'admin@sarh.com'],
            [
                'emp_code' => 'ADMIN001',
                'username' => 'admin',
                'full_name' => 'Super Admin',
                'password' => Hash::make('admin123'),
                'role_id' => $role->id,
                'is_super_admin' => true,
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        $this->command->info('✅ تم إنشاء Super Admin بنجاح!');
        $this->command->info('البريد: admin@sarh.com');
        $this->command->info('كلمة المرور: admin123');
    }
}
