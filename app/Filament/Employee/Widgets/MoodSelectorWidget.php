<?php

namespace App\Filament\Employee\Widgets;

use App\Models\UserMood;
use Filament\Notifications\Notification;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Auth;

class MoodSelectorWidget extends Widget
{
    protected static string $view = 'filament.employee.widgets.mood-selector-widget';
    protected static ?int $sort = 1;

    public $todayMood = null;

    public function mount(): void
    {
        $this->todayMood = UserMood::where('user_id', Auth::id())
            ->where('date', today())
            ->first();
    }

    public function saveMood(string $mood): void
    {
        try {
            UserMood::updateOrCreate(
                [
                    'user_id' => Auth::id(),
                    'date' => today(),
                ],
                [
                    'mood' => $mood,
                ]
            );

            $this->todayMood = UserMood::where('user_id', Auth::id())
                ->where('date', today())
                ->first();

            Notification::make()
                ->success()
                ->title('تم حفظ مزاجك!')
                ->body('شكراً على مشاركتنا شعورك اليوم')
                ->send();
        } catch (\Exception $e) {
            Notification::make()
                ->danger()
                ->title('خطأ')
                ->body('فشل في حفظ المزاج')
                ->send();
        }
    }
}
