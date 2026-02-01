<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\Badge;
use App\Models\User;
use App\Models\UserBadge;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class GamificationService
{
    /**
     * Check and award badges to a user based on attendance patterns
     * 
     * @param User $user
     * @return array
     */
    public function checkAndAwardBadges(User $user): array
    {
        $awardedBadges = [];

        // Check Consistency Streak (7 consecutive days not late)
        if ($this->checkConsistencyStreak($user)) {
            $badge = $this->awardBadge($user, 'consistency_streak_7', [
                'badge_name' => 'نجم الانضباط',
                'badge_name_en' => 'Consistency Star',
                'description' => '7 أيام متتالية بدون تأخير',
                'icon' => '⭐',
                'criteria' => '7_days_no_late',
                'criteria_value' => 7,
                'points_reward' => 50,
            ]);

            if ($badge) {
                $awardedBadges[] = $badge;
            }
        }

        // Check Early Bird (30 mins before shift)
        if ($this->checkEarlyBird($user)) {
            $badge = $this->awardBadge($user, 'early_bird', [
                'badge_name' => 'الطائر المبكر',
                'badge_name_en' => 'Early Bird',
                'description' => 'حضور مبكر 30 دقيقة قبل الوردية',
                'icon' => '🐦',
                'criteria' => 'early_30_mins',
                'criteria_value' => 30,
                'points_reward' => 20,
            ]);

            if ($badge) {
                $awardedBadges[] = $badge;
            }
        }

        // Check Perfect Week (7 days with location verified)
        if ($this->checkPerfectWeek($user)) {
            $badge = $this->awardBadge($user, 'perfect_week', [
                'badge_name' => 'الأسبوع المثالي',
                'badge_name_en' => 'Perfect Week',
                'description' => 'أسبوع كامل بحضور موثق من الموقع الصحيح',
                'icon' => '🏆',
                'criteria' => 'week_location_verified',
                'criteria_value' => 7,
                'points_reward' => 100,
            ]);

            if ($badge) {
                $awardedBadges[] = $badge;
            }
        }

        // Update user points
        if (!empty($awardedBadges)) {
            $totalPoints = collect($awardedBadges)->sum('points_reward');
            $this->addPointsToUser($user, $totalPoints);
        }

        return $awardedBadges;
    }

    /**
     * Check if user has 7 consecutive days without being late
     * 
     * @param User $user
     * @return bool
     */
    private function checkConsistencyStreak(User $user): bool
    {
        $sevenDaysAgo = Carbon::now()->subDays(7);
        
        $attendances = Attendance::where('user_id', $user->id)
            ->where('date', '>=', $sevenDaysAgo)
            ->whereNotNull('check_in_time')
            ->orderBy('date', 'desc')
            ->take(7)
            ->get();

        // Must have at least 7 days of attendance
        if ($attendances->count() < 7) {
            return false;
        }

        // Check if all 7 days are not late
        $allOnTime = $attendances->every(function ($attendance) {
            return $attendance->is_late === false || $attendance->is_late === 0;
        });

        // Check if user already has this badge in the last 7 days
        if ($allOnTime && !$this->userHasBadgeRecently($user, 'consistency_streak_7', 7)) {
            return true;
        }

        return false;
    }

    /**
     * Check if user arrived 30 minutes before shift start
     * 
     * @param User $user
     * @return bool
     */
    private function checkEarlyBird(User $user): bool
    {
        $todayAttendance = Attendance::where('user_id', $user->id)
            ->whereDate('date', Carbon::today())
            ->whereNotNull('check_in_time')
            ->first();

        if (!$todayAttendance) {
            return false;
        }

        // If late_minutes is negative, it means they were early
        if (isset($todayAttendance->late_minutes) && $todayAttendance->late_minutes <= -30) {
            // Check if already awarded today
            return !$this->userHasBadgeRecently($user, 'early_bird', 1);
        }

        return false;
    }

    /**
     * Check if user has perfect week (7 days with verified location)
     * 
     * @param User $user
     * @return bool
     */
    private function checkPerfectWeek(User $user): bool
    {
        $sevenDaysAgo = Carbon::now()->subDays(7);
        
        $verifiedCount = Attendance::where('user_id', $user->id)
            ->where('date', '>=', $sevenDaysAgo)
            ->where('check_in_location_verified', true)
            ->where('check_out_location_verified', true)
            ->whereNotNull('check_out_time')
            ->count();

        if ($verifiedCount >= 7 && !$this->userHasBadgeRecently($user, 'perfect_week', 7)) {
            return true;
        }

        return false;
    }

    /**
     * Award badge to user
     * 
     * @param User $user
     * @param string $criteriaSlug
     * @param array $badgeData
     * @return Badge|null
     */
    private function awardBadge(User $user, string $criteriaSlug, array $badgeData): ?Badge
    {
        // Find or create badge
        $badge = Badge::firstOrCreate(
            ['criteria' => $criteriaSlug],
            $badgeData
        );

        // Check if user already has this badge
        $existingUserBadge = UserBadge::where('user_id', $user->id)
            ->where('badge_id', $badge->id)
            ->first();

        if (!$existingUserBadge) {
            // Award badge to user
            UserBadge::create([
                'user_id' => $user->id,
                'badge_id' => $badge->id,
                'earned_at' => Carbon::now(),
            ]);

            return $badge;
        }

        return null;
    }

    /**
     * Check if user has received a badge recently
     * 
     * @param User $user
     * @param string $criteriaSlug
     * @param int $days
     * @return bool
     */
    private function userHasBadgeRecently(User $user, string $criteriaSlug, int $days = 7): bool
    {
        $badge = Badge::where('criteria', $criteriaSlug)->first();
        
        if (!$badge) {
            return false;
        }

        $recentBadge = UserBadge::where('user_id', $user->id)
            ->where('badge_id', $badge->id)
            ->where('earned_at', '>=', Carbon::now()->subDays($days))
            ->exists();

        return $recentBadge;
    }

    /**
     * Add points to user
     * 
     * @param User $user
     * @param int $points
     * @return void
     */
    private function addPointsToUser(User $user, int $points): void
    {
        $user->increment('current_points', $points);
        $user->increment('total_points_earned', $points);
    }

    /**
     * Calculate points based on attendance record
     * 
     * @param Attendance $attendance
     * @return int
     */
    public function calculateAttendancePoints(Attendance $attendance): int
    {
        $points = 0;

        // Base points for showing up
        $points += 10;

        // Bonus for being on time
        if (!$attendance->is_late) {
            $points += 5;
        }

        // Bonus for location verification
        if ($attendance->check_in_location_verified && $attendance->check_out_location_verified) {
            $points += 10;
        }

        // Penalty for being late
        if ($attendance->is_late && $attendance->late_minutes > 0) {
            $penaltyRate = $this->getPenaltyPointsPerMinute();
            $penalty = min($attendance->late_minutes * $penaltyRate, 20); // Max 20 points penalty
            $points -= $penalty;
        }

        return max($points, 0); // Don't allow negative points
    }

    /**
     * Get penalty points per minute from system settings
     * 
     * @return float
     */
    private function getPenaltyPointsPerMinute(): float
    {
        $setting = \App\Models\SystemSetting::where('setting_key', 'penalty_points_per_minute')->first();
        return $setting ? (float) $setting->setting_value : 0.5;
    }
}
