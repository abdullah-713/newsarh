<?php

namespace App\Filament\Employee\Widgets;

use App\Models\User;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;

class LeaderboardWidget extends BaseWidget
{
    protected static ?string $heading = '🏆 قائمة المتصدرين';
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                User::query()
                    ->where('is_active', true)
                    ->whereMonth('created_at', '<=', now())
                    ->orderBy('current_points', 'desc')
                    ->limit(10)
            )
            ->columns([
                Tables\Columns\TextColumn::make('rank')
                    ->label('الترتيب')
                    ->state(function ($rowLoop) {
                        $rank = $rowLoop->iteration;
                        return match($rank) {
                            1 => '🥇 ' . $rank,
                            2 => '🥈 ' . $rank,
                            3 => '🥉 ' . $rank,
                            default => $rank
                        };
                    })
                    ->badge()
                    ->color(fn ($rowLoop) => match($rowLoop->iteration) {
                        1 => 'warning',
                        2 => 'gray',
                        3 => 'orange',
                        default => 'primary'
                    }),
                    
                Tables\Columns\TextColumn::make('full_name')
                    ->label('الاسم')
                    ->searchable()
                    ->weight('bold'),
                    
                Tables\Columns\TextColumn::make('current_points')
                    ->label('النقاط')
                    ->suffix(' ⭐')
                    ->numeric()
                    ->sortable()
                    ->weight('bold')
                    ->color('success'),
                    
                Tables\Columns\TextColumn::make('userBadges_count')
                    ->label('الشارات')
                    ->counts('userBadges')
                    ->suffix(' 🏅')
                    ->badge()
                    ->color('info'),
                    
                Tables\Columns\TextColumn::make('streak_count')
                    ->label('التتابع')
                    ->suffix(' 🔥')
                    ->default(0)
                    ->badge()
                    ->color('danger'),
            ])
            ->paginated(false)
            ->defaultSort('current_points', 'desc');
    }
}
