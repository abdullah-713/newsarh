<?php

namespace App\Filament\Employee\Pages;

use App\Models\UserBadge;
use App\Models\Badge;
use Filament\Pages\Page;

class MyBadges extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-trophy';
    
    protected static ?string $navigationLabel = 'الشارات والإنجازات';
    
    protected static ?string $title = 'شاراتي وإنجازاتي';
    
    protected static string $view = 'filament.employee.pages.my-badges';
    
    protected static ?int $navigationSort = 3;

    public function getUserBadges()
    {
        return UserBadge::with('badge')
            ->where('user_id', auth()->id())
            ->orderBy('awarded_at', 'desc')
            ->get();
    }

    public function getAvailableBadges()
    {
        $userBadgeIds = UserBadge::where('user_id', auth()->id())
            ->pluck('badge_id')
            ->toArray();

        return Badge::where('is_active', true)
            ->whereNotIn('id', $userBadgeIds)
            ->get();
    }

    public function getTotalPoints()
    {
        return auth()->user()->points ?? 0;
    }
}
