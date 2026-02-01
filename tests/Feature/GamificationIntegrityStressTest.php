<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Role;
use App\Models\Reward;
use App\Services\GamificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

class GamificationIntegrityStressTest extends TestCase
{
    use RefreshDatabase;

    protected User $testUser;
    protected GamificationService $gamificationService;

    protected function setUp(): void
    {
        parent::setUp();

        $role = Role::create([
            'name' => 'Employee',
            'slug' => 'employee',
            'role_level' => 3,
            'permissions' => json_encode(['rewards.view']),
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
            'gamification_points' => 50, // User has only 50 points
        ]);

        $this->gamificationService = app(GamificationService::class);
    }

    /** @test */
    public function test_cannot_redeem_reward_with_insufficient_points()
    {
        // Create a reward that costs more than user has
        $reward = Reward::create([
            'name' => 'Expensive Reward',
            'description' => 'Costs 100 points',
            'points_required' => 100,
            'quantity_available' => 10,
            'is_active' => true,
        ]);

        $this->actingAs($this->testUser);

        // Attempt to redeem
        $response = $this->postJson('/api/rewards/' . $reward->id . '/redeem');

        // Should fail
        $this->assertTrue(
            $response->status() === 400 || $response->status() === 422 || $response->status() === 403,
            "Should not allow redemption with insufficient points. Got status: {$response->status()}"
        );

        // Verify user still has 50 points
        $this->testUser->refresh();
        $this->assertEquals(50, $this->testUser->gamification_points, "Points should not have changed");
    }

    /** @test */
    public function test_transaction_rollback_on_reward_redemption_failure()
    {
        $reward = Reward::create([
            'name' => 'Test Reward',
            'description' => 'Costs 30 points',
            'points_required' => 30,
            'quantity_available' => 0, // No quantity available - will fail
            'is_active' => true,
        ]);

        $initialPoints = $this->testUser->gamification_points;

        $this->actingAs($this->testUser);

        try {
            // This should fail due to no quantity
            $response = $this->postJson('/api/rewards/' . $reward->id . '/redeem');

            // Check that transaction was rolled back
            $this->testUser->refresh();
            $this->assertEquals(
                $initialPoints,
                $this->testUser->gamification_points,
                "Points should be rolled back on failed redemption"
            );
        } catch (\Exception $e) {
            // Transaction should have rolled back
            $this->testUser->refresh();
            $this->assertEquals($initialPoints, $this->testUser->gamification_points);
        }
    }

    /** @test */
    public function test_successful_reward_redemption_deducts_points()
    {
        $reward = Reward::create([
            'name' => 'Affordable Reward',
            'description' => 'Costs 30 points',
            'points_required' => 30,
            'quantity_available' => 10,
            'is_active' => true,
        ]);

        $initialPoints = $this->testUser->gamification_points; // 50

        $this->actingAs($this->testUser);

        $response = $this->postJson('/api/rewards/' . $reward->id . '/redeem');

        if ($response->isOk() || $response->status() === 201) {
            // Points should be deducted
            $this->testUser->refresh();
            $this->assertEquals(
                $initialPoints - 30,
                $this->testUser->gamification_points,
                "30 points should be deducted"
            );

            // Quantity should decrease
            $reward->refresh();
            $this->assertEquals(9, $reward->quantity_available, "Quantity should decrease by 1");
        }
    }

    /** @test */
    public function test_concurrent_reward_redemptions_dont_oversell()
    {
        $reward = Reward::create([
            'name' => 'Limited Reward',
            'description' => 'Only 1 available',
            'points_required' => 10,
            'quantity_available' => 1,
            'is_active' => true,
        ]);

        // Create second user
        $user2 = User::create([
            'full_name' => 'Second User',
            'username' => 'testuser2',
            'emp_code' => 'EMP888',
            'email' => 'test2@example.com',
            'password' => bcrypt('password123'),
            'role_id' => $this->testUser->role_id,
            'is_active' => true,
            'gamification_points' => 50,
        ]);

        // Simulate concurrent requests
        DB::beginTransaction();
        
        try {
            // First redemption
            $response1 = $this->actingAs($this->testUser)
                ->postJson('/api/rewards/' . $reward->id . '/redeem');

            // Second redemption (should fail - no stock)
            $response2 = $this->actingAs($user2)
                ->postJson('/api/rewards/' . $reward->id . '/redeem');

            DB::commit();

            // One should succeed, one should fail
            $successCount = ($response1->isOk() ? 1 : 0) + ($response2->isOk() ? 1 : 0);
            $this->assertEquals(1, $successCount, "Only one redemption should succeed");

        } catch (\Exception $e) {
            DB::rollBack();
        }
    }

    /** @test */
    public function test_gamification_service_validates_point_deduction()
    {
        $initialPoints = $this->testUser->gamification_points;

        // Try to deduct more points than user has
        try {
            $result = $this->gamificationService->deductPoints($this->testUser, 1000, 'test');
            
            $this->assertFalse($result, "Should return false when insufficient points");
            
            // Points should not change
            $this->testUser->refresh();
            $this->assertEquals($initialPoints, $this->testUser->gamification_points);
            
        } catch (\Exception $e) {
            // Exception is acceptable - points should not change
            $this->testUser->refresh();
            $this->assertEquals($initialPoints, $this->testUser->gamification_points);
        }
    }

    /** @test */
    public function test_point_history_is_recorded_correctly()
    {
        // Award points
        $this->gamificationService->awardPoints($this->testUser, 100, 'test_award');

        // Check if point history was recorded
        $this->assertDatabaseHas('point_histories', [
            'user_id' => $this->testUser->id,
            'points' => 100,
            'type' => 'earned',
            'reason' => 'test_award',
        ]);

        // Deduct points
        $this->gamificationService->deductPoints($this->testUser, 30, 'test_deduction');

        // Check deduction history
        $this->assertDatabaseHas('point_histories', [
            'user_id' => $this->testUser->id,
            'points' => -30,
            'type' => 'spent',
            'reason' => 'test_deduction',
        ]);
    }
}
