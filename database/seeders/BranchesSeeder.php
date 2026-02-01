<?php

namespace Database\Seeders;

use App\Models\Branch;
use Illuminate\Database\Seeder;

class BranchesSeeder extends Seeder
{
    /**
     * Run the database seeder.
     */
    public function run(): void
    {
        $branches = [
            [
                'name' => 'صرح الاتقان الرئيسي',
                'code' => 'MAIN-001',
                'address' => 'الرياض، المملكة العربية السعودية',
                'city' => 'الرياض',
                'latitude' => 24.7136,  // سيتم تحديثها من لوحة الإدارة
                'longitude' => 46.6753,
                'geofence_radius' => 17,
                'phone' => null,
                'email' => null,
                'is_active' => true,
                'settings' => json_encode([
                    'working_hours' => [
                        'saturday' => ['from' => '08:00', 'to' => '17:00'],
                        'sunday' => ['from' => '08:00', 'to' => '17:00'],
                        'monday' => ['from' => '08:00', 'to' => '17:00'],
                        'tuesday' => ['from' => '08:00', 'to' => '17:00'],
                        'wednesday' => ['from' => '08:00', 'to' => '17:00'],
                        'thursday' => ['from' => '08:00', 'to' => '17:00'],
                    ],
                    'map_link' => 'https://maps.app.goo.gl/W6idPdF8ktbCw7dM8',
                ]),
            ],
            [
                'name' => 'صرح الاتقان كورنر',
                'code' => 'CORNER-002',
                'address' => 'الرياض، المملكة العربية السعودية',
                'city' => 'الرياض',
                'latitude' => 24.7136,  // سيتم تحديثها من لوحة الإدارة
                'longitude' => 46.6753,
                'geofence_radius' => 17,
                'phone' => null,
                'email' => null,
                'is_active' => true,
                'settings' => json_encode([
                    'working_hours' => [
                        'saturday' => ['from' => '08:00', 'to' => '17:00'],
                        'sunday' => ['from' => '08:00', 'to' => '17:00'],
                        'monday' => ['from' => '08:00', 'to' => '17:00'],
                        'tuesday' => ['from' => '08:00', 'to' => '17:00'],
                        'wednesday' => ['from' => '08:00', 'to' => '17:00'],
                        'thursday' => ['from' => '08:00', 'to' => '17:00'],
                    ],
                    'map_link' => 'https://maps.app.goo.gl/8zWU9cRhCmWPbqUp6',
                ]),
            ],
            [
                'name' => 'صرح الاتقان 2',
                'code' => 'BRANCH-003',
                'address' => 'الرياض، المملكة العربية السعودية',
                'city' => 'الرياض',
                'latitude' => 24.7136,  // سيتم تحديثها من لوحة الإدارة
                'longitude' => 46.6753,
                'geofence_radius' => 17,
                'phone' => null,
                'email' => null,
                'is_active' => true,
                'settings' => json_encode([
                    'working_hours' => [
                        'saturday' => ['from' => '08:00', 'to' => '17:00'],
                        'sunday' => ['from' => '08:00', 'to' => '17:00'],
                        'monday' => ['from' => '08:00', 'to' => '17:00'],
                        'tuesday' => ['from' => '08:00', 'to' => '17:00'],
                        'wednesday' => ['from' => '08:00', 'to' => '17:00'],
                        'thursday' => ['from' => '08:00', 'to' => '17:00'],
                    ],
                    'map_link' => 'https://maps.app.goo.gl/rf4JGxxcPGSxyb1Q7',
                ]),
            ],
            [
                'name' => 'فضاء المحركات 1',
                'code' => 'ENGINE-004',
                'address' => 'الرياض، المملكة العربية السعودية',
                'city' => 'الرياض',
                'latitude' => 24.7136,  // سيتم تحديثها من لوحة الإدارة
                'longitude' => 46.6753,
                'geofence_radius' => 17,
                'phone' => null,
                'email' => null,
                'is_active' => true,
                'settings' => json_encode([
                    'working_hours' => [
                        'saturday' => ['from' => '08:00', 'to' => '17:00'],
                        'sunday' => ['from' => '08:00', 'to' => '17:00'],
                        'monday' => ['from' => '08:00', 'to' => '17:00'],
                        'tuesday' => ['from' => '08:00', 'to' => '17:00'],
                        'wednesday' => ['from' => '08:00', 'to' => '17:00'],
                        'thursday' => ['from' => '08:00', 'to' => '17:00'],
                    ],
                    'map_link' => 'https://maps.app.goo.gl/rf4JGxxcPGSxyb1Q7',
                ]),
            ],
            [
                'name' => 'فضاء المحركات 2',
                'code' => 'ENGINE-005',
                'address' => 'الرياض، المملكة العربية السعودية',
                'city' => 'الرياض',
                'latitude' => 24.7136,  // سيتم تحديثها من لوحة الإدارة
                'longitude' => 46.6753,
                'geofence_radius' => 17,
                'phone' => null,
                'email' => null,
                'is_active' => true,
                'settings' => json_encode([
                    'working_hours' => [
                        'saturday' => ['from' => '08:00', 'to' => '17:00'],
                        'sunday' => ['from' => '08:00', 'to' => '17:00'],
                        'monday' => ['from' => '08:00', 'to' => '17:00'],
                        'tuesday' => ['from' => '08:00', 'to' => '17:00'],
                        'wednesday' => ['from' => '08:00', 'to' => '17:00'],
                        'thursday' => ['from' => '08:00', 'to' => '17:00'],
                    ],
                    'map_link' => 'https://maps.app.goo.gl/hMMTqQCP3dKDfk2CA',
                ]),
            ],
        ];

        foreach ($branches as $branchData) {
            Branch::updateOrCreate(
                ['code' => $branchData['code']],
                $branchData
            );
        }

        $this->command->info('✅ تم إضافة 5 فروع بنجاح!');
        $this->command->warn('⚠️ تنبيه: يرجى تحديث الإحداثيات الفعلية من لوحة الإدارة باستخدام الخريطة التفاعلية');
    }
}
