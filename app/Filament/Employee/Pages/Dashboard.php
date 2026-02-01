<?php

namespace App\Filament\Employee\Pages;

use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    protected static ?string $navigationLabel = 'لوحة التحكم';
    
    protected static string $view = 'filament.employee.pages.dashboard';

    public function getWidgets(): array
    {
        return [
            \App\Filament\Employee\Widgets\AnnouncementsWidget::class,
            \App\Filament\Employee\Widgets\MoodSelectorWidget::class,
            \App\Livewire\Dashboard\AttendanceWidget::class,
            \App\Filament\Employee\Widgets\LeaderboardWidget::class,
        ];
    }
}
