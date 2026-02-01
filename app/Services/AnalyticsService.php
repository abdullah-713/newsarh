<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AnalyticsService
{
    /**
     * Get attendance trends for a period
     */
    public function getAttendanceTrends(string $period = 'month', ?int $branchId = null): array
    {
        $query = Attendance::query();

        if ($branchId) {
            $query->whereHas('user', fn($q) => $q->where('branch_id', $branchId));
        }

        $startDate = match($period) {
            'week' => now()->startOfWeek(),
            'month' => now()->startOfMonth(),
            'year' => now()->startOfYear(),
            default => now()->startOfMonth(),
        };

        $data = $query->where('date', '>=', $startDate)
            ->select(
                DB::raw('DATE(date) as day'),
                DB::raw('COUNT(*) as total'),
                DB::raw('SUM(CASE WHEN status = "present" THEN 1 ELSE 0 END) as present'),
                DB::raw('SUM(CASE WHEN status = "late" THEN 1 ELSE 0 END) as late'),
                DB::raw('SUM(CASE WHEN status = "absent" THEN 1 ELSE 0 END) as absent')
            )
            ->groupBy('day')
            ->orderBy('day')
            ->get();

        return [
            'labels' => $data->pluck('day')->map(fn($d) => Carbon::parse($d)->format('M d'))->toArray(),
            'datasets' => [
                [
                    'label' => 'حاضر',
                    'data' => $data->pluck('present')->toArray(),
                    'color' => 'success',
                ],
                [
                    'label' => 'متأخر',
                    'data' => $data->pluck('late')->toArray(),
                    'color' => 'warning',
                ],
                [
                    'label' => 'غائب',
                    'data' => $data->pluck('absent')->toArray(),
                    'color' => 'danger',
                ],
            ],
        ];
    }

    /**
     * Detect users with late anomalies (late more than 3 times per week)
     */
    public function getLateAnomalies(): array
    {
        $startOfWeek = now()->startOfWeek();
        
        $lateUsers = Attendance::where('status', 'late')
            ->where('date', '>=', $startOfWeek)
            ->select('user_id', DB::raw('COUNT(*) as late_count'))
            ->groupBy('user_id')
            ->having('late_count', '>', 3)
            ->with('user:id,full_name,employee_id,branch_id')
            ->get();

        return $lateUsers->map(function ($item) {
            return [
                'user_id' => $item->user_id,
                'user_name' => $item->user->full_name ?? 'Unknown',
                'employee_id' => $item->user->employee_id ?? '',
                'late_count' => $item->late_count,
                'severity' => $item->late_count > 5 ? 'high' : 'medium',
            ];
        })->toArray();
    }

    /**
     * Get employee productivity score
     */
    public function getProductivityScore(User $user, string $period = 'month'): array
    {
        $startDate = match($period) {
            'week' => now()->startOfWeek(),
            'month' => now()->startOfMonth(),
            'year' => now()->startOfYear(),
            default => now()->startOfMonth(),
        };

        $attendances = Attendance::where('user_id', $user->id)
            ->where('date', '>=', $startDate)
            ->get();

        $totalDays = $attendances->count();
        $presentDays = $attendances->where('status', 'present')->count();
        $lateDays = $attendances->where('status', 'late')->count();
        $absentDays = $attendances->where('status', 'absent')->count();
        
        $avgWorkHours = $attendances->avg('total_hours') ?? 0;
        $totalWorkHours = $attendances->sum('total_hours') ?? 0;

        // Calculate score (0-100)
        $attendanceScore = $totalDays > 0 ? ($presentDays / $totalDays) * 40 : 0;
        $punctualityScore = $totalDays > 0 ? (($totalDays - $lateDays) / $totalDays) * 30 : 0;
        $workHoursScore = min(($avgWorkHours / 8) * 30, 30);

        $totalScore = round($attendanceScore + $punctualityScore + $workHoursScore, 1);

        return [
            'score' => $totalScore,
            'grade' => $this->getGrade($totalScore),
            'metrics' => [
                'total_days' => $totalDays,
                'present_days' => $presentDays,
                'late_days' => $lateDays,
                'absent_days' => $absentDays,
                'avg_work_hours' => round($avgWorkHours, 2),
                'total_work_hours' => round($totalWorkHours, 2),
            ],
        ];
    }

    /**
     * Get grade based on score
     */
    protected function getGrade(float $score): string
    {
        return match(true) {
            $score >= 90 => 'A+',
            $score >= 85 => 'A',
            $score >= 80 => 'B+',
            $score >= 75 => 'B',
            $score >= 70 => 'C+',
            $score >= 65 => 'C',
            $score >= 60 => 'D+',
            $score >= 50 => 'D',
            default => 'F',
        };
    }

    /**
     * Get department statistics
     */
    public function getDepartmentStats(): array
    {
        $stats = User::select('branch_id', DB::raw('COUNT(*) as total_employees'))
            ->where('is_active', true)
            ->groupBy('branch_id')
            ->with('branch:id,name')
            ->get();

        return $stats->map(function ($item) {
            $attendanceRate = $this->calculateBranchAttendanceRate($item->branch_id);
            
            return [
                'branch_id' => $item->branch_id,
                'branch_name' => $item->branch->name ?? 'Unknown',
                'total_employees' => $item->total_employees,
                'attendance_rate' => $attendanceRate,
            ];
        })->toArray();
    }

    /**
     * Calculate branch attendance rate
     */
    protected function calculateBranchAttendanceRate(int $branchId): float
    {
        $startOfMonth = now()->startOfMonth();
        
        $totalAttendances = Attendance::whereHas('user', fn($q) => $q->where('branch_id', $branchId))
            ->where('date', '>=', $startOfMonth)
            ->count();

        $presentAttendances = Attendance::whereHas('user', fn($q) => $q->where('branch_id', $branchId))
            ->where('date', '>=', $startOfMonth)
            ->whereIn('status', ['present', 'late'])
            ->count();

        return $totalAttendances > 0 ? round(($presentAttendances / $totalAttendances) * 100, 1) : 0;
    }
}
