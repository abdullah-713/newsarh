<?php

namespace App\Filament\Widgets;

use App\Services\AnalyticsService;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class LateAnomalyWidget extends BaseWidget
{
    protected static ?int $sort = 3;

    protected function getStats(): array
    {
        $analyticsService = app(AnalyticsService::class);
        $anomalies = $analyticsService->getLateAnomalies();
        
        $totalAnomalies = count($anomalies);
        $highSeverity = collect($anomalies)->where('severity', 'high')->count();
        
        $stats = [
            Stat::make('⚠️ موظفين متأخرين متكررًا', $totalAnomalies)
                ->description('تأخروا أكثر من 3 مرات هذا الأسبوع')
                ->color($totalAnomalies > 0 ? 'warning' : 'success')
                ->icon('heroicon-o-exclamation-triangle'),
                
            Stat::make('🔥 حالات حرجة', $highSeverity)
                ->description('تأخروا أكثر من 5 مرات')
                ->color($highSeverity > 0 ? 'danger' : 'success')
                ->icon('heroicon-o-fire'),
        ];
        
        // Add top 3 late users
        $topLate = collect($anomalies)->sortByDesc('late_count')->take(3);
        foreach ($topLate as $user) {
            $stats[] = Stat::make($user['user_name'], $user['late_count'] . ' مرة')
                ->description('رقم الموظف: ' . $user['employee_id'])
                ->color('danger');
        }
        
        return $stats;
    }
}
