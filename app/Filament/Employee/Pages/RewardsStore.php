<?php

namespace App\Filament\Employee\Pages;

use App\Models\Reward;
use App\Services\GamificationService;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;

class RewardsStore extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-gift';
    protected static ?string $navigationLabel = 'متجر المكافآت';
    protected static ?string $title = 'متجر المكافآت';
    protected static ?int $navigationSort = 3;
    protected static string $view = 'filament.employee.pages.rewards-store';

    public $rewards = [];
    public $userPoints = 0;

    public function mount(): void
    {
        $this->loadRewards();
        $this->userPoints = Auth::user()->current_points ?? 0;
    }

    protected function loadRewards(): void
    {
        $this->rewards = Reward::where('is_active', true)
            ->where('stock', '>', 0)
            ->orderBy('points_required', 'asc')
            ->get()
            ->toArray();
    }

    public function redeemReward(int $rewardId): void
    {
        try {
            $gamificationService = app(GamificationService::class);
            $user = Auth::user();
            $reward = Reward::find($rewardId);

            if (!$reward) {
                Notification::make()
                    ->danger()
                    ->title('خطأ')
                    ->body('المكافأة غير موجودة')
                    ->send();
                return;
            }

            $result = $gamificationService->redeem($user, $reward);

            if ($result['success']) {
                Notification::make()
                    ->success()
                    ->title('تم الاستبدال بنجاح!')
                    ->body($result['message'])
                    ->send();

                $this->loadRewards();
                $this->userPoints = $user->fresh()->current_points;
            } else {
                Notification::make()
                    ->danger()
                    ->title('فشل الاستبدال')
                    ->body($result['message'])
                    ->send();
            }
        } catch (\Exception $e) {
            Notification::make()
                ->danger()
                ->title('حدث خطأ')
                ->body('فشل في استبدال المكافأة')
                ->send();
        }
    }
}
