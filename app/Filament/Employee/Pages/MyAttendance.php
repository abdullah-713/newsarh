<?php

namespace App\Filament\Employee\Pages;

use App\Models\Attendance;
use Carbon\Carbon;
use Filament\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;

class MyAttendance extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-calendar';
    
    protected static ?string $navigationLabel = 'سجل الحضور';
    
    protected static ?string $title = 'سجل حضوري';
    
    protected static string $view = 'filament.employee.pages.my-attendance';
    
    protected static ?int $navigationSort = 2;

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Attendance::query()
                    ->where('user_id', auth()->id())
                    ->orderBy('date', 'desc')
            )
            ->columns([
                TextColumn::make('date')
                    ->label('التاريخ')
                    ->date('Y-m-d')
                    ->sortable(),
                
                TextColumn::make('check_in_time')
                    ->label('وقت الحضور')
                    ->time('H:i')
                    ->badge()
                    ->color('success'),
                
                TextColumn::make('check_out_time')
                    ->label('وقت الانصراف')
                    ->time('H:i')
                    ->badge()
                    ->color('primary'),
                
                TextColumn::make('late_minutes')
                    ->label('دقائق التأخير')
                    ->badge()
                    ->color(fn ($state) => $state > 0 ? 'danger' : 'success')
                    ->formatStateUsing(fn ($state) => $state > 0 ? "$state دقيقة" : 'في الموعد'),
                
                TextColumn::make('work_minutes')
                    ->label('ساعات العمل')
                    ->formatStateUsing(function ($state) {
                        if (!$state) return '-';
                        $hours = floor($state / 60);
                        $mins = $state % 60;
                        return sprintf("%d:%02d", $hours, $mins);
                    })
                    ->badge()
                    ->color('info'),
                
                TextColumn::make('check_in_location_verified')
                    ->label('موقع الحضور')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state ? '✓ موثق' : '✗ غير موثق')
                    ->color(fn ($state) => $state ? 'success' : 'warning'),
            ])
            ->filters([
                // Add filters if needed
            ])
            ->defaultSort('date', 'desc');
    }
}
