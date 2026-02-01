<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AttendanceResource\Pages;
use App\Filament\Resources\AttendanceResource\RelationManagers;
use App\Filament\Resources\SecureResource;
use App\Models\Attendance;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class AttendanceResource extends SecureResource
{
    protected static ?string $model = Attendance::class;
    
    protected static ?string $permissionPrefix = 'attendance';

    protected static ?string $navigationIcon = 'heroicon-o-clock';
    
    protected static ?string $navigationLabel = 'الحضور والانصراف';
    
    protected static ?string $modelLabel = 'سجل حضور';
    
    protected static ?string $pluralModelLabel = 'الحضور والانصراف';
    
    protected static ?string $navigationGroup = 'إدارة الموارد البشرية';
    
    protected static ?int $navigationSort = 10;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('معلومات الموظف')
                    ->schema([
                        Forms\Components\Select::make('user_id')
                            ->label('الموظف')
                            ->relationship('user', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->columnSpanFull(),
                        
                        Forms\Components\DatePicker::make('date')
                            ->label('التاريخ')
                            ->required()
                            ->default(now())
                            ->native(false)
                            ->displayFormat('Y-m-d')
                            ->maxDate(now()),
                    ])
                    ->columns(2),
                
                Forms\Components\Section::make('وقت الحضور')
                    ->schema([
                        Forms\Components\TimePicker::make('check_in_time')
                            ->label('وقت الحضور')
                            ->seconds(false)
                            ->native(false),
                        
                        Forms\Components\TextInput::make('check_in_lat')
                            ->label('خط العرض - الحضور')
                            ->numeric()
                            ->step(0.0000001)
                            ->maxLength(20),
                        
                        Forms\Components\TextInput::make('check_in_lng')
                            ->label('خط الطول - الحضور')
                            ->numeric()
                            ->step(0.0000001)
                            ->maxLength(20),
                        
                        Forms\Components\TextInput::make('check_in_address')
                            ->label('عنوان الحضور')
                            ->maxLength(255),
                        
                        Forms\Components\TextInput::make('check_in_distance')
                            ->label('المسافة من الفرع (م)')
                            ->numeric()
                            ->suffix('متر'),
                        
                        Forms\Components\Select::make('check_in_method')
                            ->label('طريقة التسجيل')
                            ->options([
                                'manual' => '🖐 يدوي',
                                'auto_gps' => '📍 GPS تلقائي',
                            ])
                            ->default('manual'),
                    ])
                    ->columns(2),
                
                Forms\Components\Section::make('وقت الانصراف')
                    ->schema([
                        Forms\Components\TimePicker::make('check_out_time')
                            ->label('وقت الانصراف')
                            ->seconds(false)
                            ->native(false),
                        
                        Forms\Components\TextInput::make('check_out_lat')
                            ->label('خط العرض - الانصراف')
                            ->numeric()
                            ->step(0.0000001)
                            ->maxLength(20),
                        
                        Forms\Components\TextInput::make('check_out_lng')
                            ->label('خط الطول - الانصراف')
                            ->numeric()
                            ->step(0.0000001)
                            ->maxLength(20),
                        
                        Forms\Components\TextInput::make('check_out_address')
                            ->label('عنوان الانصراف')
                            ->maxLength(255),
                        
                        Forms\Components\TextInput::make('check_out_distance')
                            ->label('المسافة من الفرع (م)')
                            ->numeric()
                            ->suffix('متر'),
                    ])
                    ->columns(2),
                
                Forms\Components\Section::make('الحسابات والنقاط')
                    ->schema([
                        Forms\Components\TextInput::make('work_minutes')
                            ->label('دقائق العمل')
                            ->numeric()
                            ->suffix('دقيقة')
                            ->disabled(),
                        
                        Forms\Components\TextInput::make('late_minutes')
                            ->label('دقائق التأخير')
                            ->numeric()
                            ->default(0)
                            ->suffix('دقيقة'),
                        
                        Forms\Components\TextInput::make('early_leave_minutes')
                            ->label('دقائق المغادرة المبكرة')
                            ->numeric()
                            ->default(0)
                            ->suffix('دقيقة'),
                        
                        Forms\Components\TextInput::make('overtime_minutes')
                            ->label('دقائق الإضافي')
                            ->numeric()
                            ->default(0)
                            ->suffix('دقيقة'),
                        
                        Forms\Components\TextInput::make('penalty_points')
                            ->label('نقاط الخصم')
                            ->numeric()
                            ->default(0)
                            ->step(0.01)
                            ->prefix('📉'),
                        
                        Forms\Components\TextInput::make('bonus_points')
                            ->label('نقاط المكافأة')
                            ->numeric()
                            ->default(0)
                            ->step(0.01)
                            ->prefix('📈'),
                    ])
                    ->columns(3),
                
                Forms\Components\Section::make('الحالة والملاحظات')
                    ->schema([
                        Forms\Components\Select::make('branch_id')
                            ->label('الفرع المخصص')
                            ->relationship('branch', 'name')
                            ->searchable()
                            ->preload(),
                        
                        Forms\Components\Select::make('recorded_branch_id')
                            ->label('الفرع المسجل فيه')
                            ->relationship('recordedBranch', 'name')
                            ->searchable()
                            ->preload(),
                        
                        Forms\Components\Select::make('status')
                            ->label('الحالة')
                            ->options([
                                'present' => '✅ حاضر',
                                'absent' => '❌ غائب',
                                'late' => '⏰ متأخر',
                                'half_day' => '🕐 نصف يوم',
                                'leave' => '🏖 إجازة',
                                'holiday' => '🎉 عطلة',
                            ])
                            ->default('present')
                            ->required(),
                        
                        Forms\Components\TextInput::make('mood_score')
                            ->label('درجة المزاج')
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(5)
                            ->suffix('⭐'),
                        
                        Forms\Components\Toggle::make('is_locked')
                            ->label('مقفل؟')
                            ->helperText('عند القفل لا يمكن التعديل')
                            ->default(false),
                        
                        Forms\Components\Textarea::make('notes')
                            ->label('ملاحظات')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('الموظف')
                    ->searchable()
                    ->sortable()
                    ->icon('heroicon-o-user'),
                
                Tables\Columns\TextColumn::make('date')
                    ->label('التاريخ')
                    ->date('Y-m-d')
                    ->sortable()
                    ->icon('heroicon-o-calendar'),
                
                Tables\Columns\TextColumn::make('check_in_time')
                    ->label('الحضور')
                    ->time('H:i')
                    ->icon('heroicon-o-arrow-down-on-square')
                    ->color('success'),
                
                Tables\Columns\TextColumn::make('check_out_time')
                    ->label('الانصراف')
                    ->time('H:i')
                    ->icon('heroicon-o-arrow-up-on-square')
                    ->color('danger'),
                
                Tables\Columns\TextColumn::make('work_minutes')
                    ->label('ساعات العمل')
                    ->formatStateUsing(fn ($state) => $state ? round($state / 60, 1) . ' س' : '-')
                    ->icon('heroicon-o-clock')
                    ->alignCenter(),
                
                Tables\Columns\TextColumn::make('late_minutes')
                    ->label('التأخير')
                    ->formatStateUsing(fn ($state) => $state > 0 ? $state . ' د' : '-')
                    ->color(fn ($state) => $state > 0 ? 'warning' : 'gray')
                    ->icon('heroicon-o-exclamation-triangle'),
                
                Tables\Columns\BadgeColumn::make('status')
                    ->label('الحالة')
                    ->colors([
                        'success' => 'present',
                        'danger' => 'absent',
                        'warning' => 'late',
                        'info' => 'half_day',
                        'primary' => 'leave',
                        'secondary' => 'holiday',
                    ])
                    ->formatStateUsing(fn ($state) => match($state) {
                        'present' => '✅ حاضر',
                        'absent' => '❌ غائب',
                        'late' => '⏰ متأخر',
                        'half_day' => '🕐 نصف يوم',
                        'leave' => '🏖 إجازة',
                        'holiday' => '🎉 عطلة',
                        default => $state,
                    }),
                
                Tables\Columns\TextColumn::make('branch.name')
                    ->label('الفرع')
                    ->icon('heroicon-o-building-office')
                    ->toggleable(isToggledHiddenByDefault: true),
                
                Tables\Columns\IconColumn::make('is_locked')
                    ->label('مقفل')
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                Tables\Columns\TextColumn::make('penalty_points')
                    ->label('خصم')
                    ->formatStateUsing(fn ($state) => $state > 0 ? $state : '-')
                    ->color('danger')
                    ->toggleable(isToggledHiddenByDefault: true),
                
                Tables\Columns\TextColumn::make('bonus_points')
                    ->label('مكافأة')
                    ->formatStateUsing(fn ($state) => $state > 0 ? $state : '-')
                    ->color('success')
                    ->toggleable(isToggledHiddenByDefault: true),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->label('تاريخ الإنشاء')
                    ->dateTime('Y-m-d H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('الحالة')
                    ->options([
                        'present' => '✅ حاضر',
                        'absent' => '❌ غائب',
                        'late' => '⏰ متأخر',
                        'half_day' => '🕐 نصف يوم',
                        'leave' => '🏖 إجازة',
                        'holiday' => '🎉 عطلة',
                    ])
                    ->multiple(),
                
                Tables\Filters\Filter::make('date')
                    ->form([
                        Forms\Components\DatePicker::make('from')
                            ->label('من تاريخ'),
                        Forms\Components\DatePicker::make('until')
                            ->label('إلى تاريخ'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['from'], fn ($q, $date) => $q->whereDate('date', '>=', $date))
                            ->when($data['until'], fn ($q, $date) => $q->whereDate('date', '<=', $date));
                    }),
                
                Tables\Filters\TernaryFilter::make('is_locked')
                    ->label('مقفل'),
                
                Tables\Filters\SelectFilter::make('branch_id')
                    ->label('الفرع')
                    ->relationship('branch', 'name')
                    ->searchable()
                    ->preload(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->label('عرض'),
                Tables\Actions\EditAction::make()
                    ->label('تعديل'),
                Tables\Actions\DeleteAction::make()
                    ->label('حذف'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('حذف المحدد'),
                    Tables\Actions\ExportBulkAction::make()
                        ->label('تصدير CSV'),
                ]),
            ])
            ->headerActions([
                Tables\Actions\ExportAction::make()
                    ->label('تصدير الكل')
                    ->color('success'),
            ])
            ->defaultSort('date', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAttendances::route('/'),
            'create' => Pages\CreateAttendance::route('/create'),
            'edit' => Pages\EditAttendance::route('/{record}/edit'),
        ];
    }
}
