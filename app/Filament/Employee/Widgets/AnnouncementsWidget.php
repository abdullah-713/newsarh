<?php

namespace App\Filament\Employee\Widgets;

use App\Models\Announcement;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Auth;

class AnnouncementsWidget extends Widget
{
    protected static string $view = 'filament.employee.widgets.announcements-widget';
    protected static ?int $sort = 0;

    public function getAnnouncements()
    {
        $userRoleId = Auth::user()->role_id;

        return Announcement::active()
            ->where(function($query) use ($userRoleId) {
                $query->whereNull('target_roles')
                      ->orWhereJsonContains('target_roles', $userRoleId);
            })
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
    }
}
