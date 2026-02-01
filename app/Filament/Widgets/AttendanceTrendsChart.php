<?php

namespace App\Filament\Widgets;

use App\Services\AnalyticsService;
use Filament\Widgets\ChartWidget;

class AttendanceTrendsChart extends ChartWidget
{
    protected static ?string $heading = '📈 اتجاهات الحضور';
    protected static ?int $sort = 2;

    protected function getData(): array
    {
        $analyticsService = app(AnalyticsService::class);
        $data = $analyticsService->getAttendanceTrends('month');
        
        return [
            'datasets' => [
                [
                    'label' => 'حاضر',
                    'data' => $data['datasets'][0]['data'],
                    'backgroundColor' => 'rgb(34, 197, 94)',
                ],
                [
                    'label' => 'متأخر',
                    'data' => $data['datasets'][1]['data'],
                    'backgroundColor' => 'rgb(234, 179, 8)',
                ],
                [
                    'label' => 'غائب',
                    'data' => $data['datasets'][2]['data'],
                    'backgroundColor' => 'rgb(239, 68, 68)',
                ],
            ],
            'labels' => $data['labels'],
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
