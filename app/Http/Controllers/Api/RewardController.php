<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Reward;
use App\Services\GamificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RewardController extends Controller
{
    protected GamificationService $gamificationService;

    public function __construct(GamificationService $gamificationService)
    {
        $this->gamificationService = $gamificationService;
    }

    /**
     * Redeem a reward
     */
    public function redeem(Request $request, Reward $reward): JsonResponse
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'غير مصرح',
            ], 401);
        }

        // Check if reward is active
        if (!$reward->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'المكافأة غير نشطة',
            ], 400);
        }

        // Check if user has enough points
        if ($user->gamification_points < $reward->points_required) {
            return response()->json([
                'success' => false,
                'message' => 'نقاط غير كافية. لديك ' . $user->gamification_points . ' نقطة فقط',
                'required_points' => $reward->points_required,
                'user_points' => $user->gamification_points,
            ], 400);
        }

        // Check if reward is available
        if ($reward->quantity_available <= 0) {
            return response()->json([
                'success' => false,
                'message' => 'المكافأة غير متوفرة حالياً',
            ], 400);
        }

        DB::beginTransaction();
        try {
            // Lock the reward row to prevent race conditions
            $reward = Reward::lockForUpdate()->findOrFail($reward->id);

            // Double-check quantity after lock
            if ($reward->quantity_available <= 0) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'المكافأة نفدت للتو',
                ], 400);
            }

            // Deduct points using GamificationService
            $deducted = $this->gamificationService->deductPoints(
                $user, 
                $reward->points_required, 
                'reward_redemption'
            );

            if (!$deducted) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'فشل خصم النقاط',
                ], 500);
            }

            // Decrease quantity
            $reward->decrement('quantity_available');

            // Log redemption
            Log::info('Reward redeemed', [
                'user_id' => $user->id,
                'reward_id' => $reward->id,
                'points_spent' => $reward->points_required,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'تم استبدال المكافأة بنجاح',
                'data' => [
                    'reward_name' => $reward->name,
                    'points_spent' => $reward->points_required,
                    'remaining_points' => $user->fresh()->gamification_points,
                ],
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Failed to redeem reward', [
                'user_id' => $user->id,
                'reward_id' => $reward->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء استبدال المكافأة',
            ], 500);
        }
    }

    /**
     * Get available rewards
     */
    public function index(Request $request): JsonResponse
    {
        $rewards = Reward::where('is_active', true)
            ->where('quantity_available', '>', 0)
            ->orderBy('points_required')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $rewards,
        ]);
    }
}
