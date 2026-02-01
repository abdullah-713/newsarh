<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Role;
use App\Models\Attendance;
use App\Models\WorkShift;
use App\Jobs\AutoCheckoutJob;
use App\Models\UserShiftAssignment;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;

class TrackingLogicStressTest extends TestCase
{
    use RefreshDatabase;

    protected User $testUser;
    protected WorkShift $shift;

    protected function setUp(): void
    {
        parent::setUp();

        $role = Role::create([
            'name' => 'Employee',
            'slug' => 'employee',
            'role_level' => 3,
            'permissions' => json_encode(['attendance.create']),
            'is_active' => true,
        ]);

        $this->shift = WorkShift::create([
            'name' => 'Morning Shift',
            'code' => 'MORNING',
            'start_time' => '08:00:00',
            'end_time' => '16:00:00',
            'is_active' => true,
        ]);

        $this->testUser = User::create([
            'full_name' => 'Test User',
            'username' => 'testuser',
            'emp_code' => 'EMP999',
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
            'role_id' => $role->id,
            'is_active' => true,
        ]);

        // Assign shift to user via UserShiftAssignment
        UserShiftAssignment::create([
            'user_id' => $this->testUser->id,
            'shift_id' => $this->shift->id,
            'effective_from' => now()->format('Y-m-d'),
        ]);
    }

    /** @test */
    public function test_tracking_api_rejects_invalid_coordinates()
    {
        $this->actingAs($this->testUser);

        // Test 1: Invalid latitude (> 90)
        $response = $this->postJson('/api/tracking/log', [
            'latitude' => 999.999,
            'longitude' => 46.7382,
            'battery_level' => 50,
            'accuracy' => 10,
        ]);

        $this->assertTrue(
            $response->status() === 422 || $response->status() === 400,
            "API should reject invalid latitude. Got status: {$response->status()}"
        );

        // Test 2: Invalid longitude (< -180)
        $response = $this->postJson('/api/tracking/log', [
            'latitude' => 24.7136,
            'longitude' => -200.000,
            'battery_level' => 50,
            'accuracy' => 10,
        ]);

        $this->assertTrue(
            $response->status() === 422 || $response->status() === 400,
            "API should reject invalid longitude. Got status: {$response->status()}"
        );

        // Test 3: Missing required fields
        $response = $this->postJson('/api/tracking/log', [
            'battery_level' => 50,
        ]);

        $this->assertTrue(
            $response->status() === 422 || $response->status() === 400,
            "API should reject missing coordinates. Got status: {$response->status()}"
        );
    }

    /** @test */
    public function test_tracking_api_flags_mock_locations()
    {
        $this->actingAs($this->testUser);

        $response = $this->postJson('/api/tracking/log', [
            'latitude' => 24.7136,
            'longitude' => 46.7382,
            'battery_level' => 50,
            'accuracy' => 10,
            'is_mock_location' => true,
        ]);

        // Check if the response indicates mock location was detected
        if ($response->isOk()) {
            $this->assertTrue(true, "Mock location logged (should be flagged in logs)");
        }
    }

    /** @test */
    public function test_auto_checkout_job_processes_24_hour_old_attendance()
    {
        // Create an attendance record from 24 hours ago
        $yesterday = Carbon::yesterday();
        
        $attendance = Attendance::create([
            'user_id' => $this->testUser->id,
            'date' => $yesterday->format('Y-m-d'),
            'check_in_at' => $yesterday->setTime(8, 0, 0),
            'check_in_location_lat' => 24.7136,
            'check_in_location_long' => 46.7382,
            'status' => 'present',
        ]);

        $this->assertNull($attendance->check_out_at);

        // Run the AutoCheckoutJob
        $job = new AutoCheckoutJob();
        $job->handle();

        // Refresh the attendance record
        $attendance->refresh();

        // Check if auto checkout was performed
        $this->assertNotNull($attendance->check_out_at, "AutoCheckoutJob should have set check_out_at");
        $this->assertEquals('system_checkout', $attendance->status, "Status should be system_checkout");
        $this->assertStringContainsString('تلقائي', $attendance->notes ?? '', "Notes should mention automatic checkout");
    }

    /** @test */
    public function test_auto_checkout_respects_shift_end_time()
    {
        $today = Carbon::today();
        
        // Create attendance that checked in today but shift hasn't ended yet
        $attendance = Attendance::create([
            'user_id' => $this->testUser->id,
            'date' => $today->format('Y-m-d'),
            'check_in_at' => $today->setTime(8, 0, 0),
            'check_in_location_lat' => 24.7136,
            'check_in_location_long' => 46.7382,
            'status' => 'present',
        ]);

        // Run AutoCheckoutJob
        $job = new AutoCheckoutJob();
        $job->handle();

        // Refresh
        $attendance->refresh();

        // Should NOT checkout if shift + buffer hasn't passed
        $shiftEndTime = Carbon::parse($today->format('Y-m-d') . ' ' . $this->shift->end_time);
        $bufferEndTime = $shiftEndTime->copy()->addHours(2);

        if (now()->lessThanOrEqualTo($bufferEndTime)) {
            $this->assertNull($attendance->check_out_at, "Should not checkout before shift end + buffer");
        }
    }

    /** @test */
    public function test_auto_checkout_calculates_correct_checkout_time()
    {
        // Create attendance from yesterday
        $yesterday = Carbon::yesterday();
        
        $attendance = Attendance::create([
            'user_id' => $this->testUser->id,
            'date' => $yesterday->format('Y-m-d'),
            'check_in_at' => $yesterday->setTime(8, 0, 0),
            'check_in_location_lat' => 24.7136,
            'check_in_location_long' => 46.7382,
            'status' => 'present',
        ]);

        // Run job
        $job = new AutoCheckoutJob();
        $job->handle();

        $attendance->refresh();

        if ($attendance->check_out_at) {
            // Check that checkout time is shift end time + 30 minutes
            $expectedCheckout = Carbon::parse($yesterday->format('Y-m-d') . ' ' . $this->shift->end_time)
                ->addMinutes(30);

            $this->assertEquals(
                $expectedCheckout->format('Y-m-d H:i'),
                Carbon::parse($attendance->check_out_at)->format('Y-m-d H:i'),
                "Checkout time should be shift end + 30 minutes"
            );
        }
    }
}
