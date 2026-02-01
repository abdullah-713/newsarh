<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RoleResource\Pages;
use App\Filament\Resources\SecureResource;
use App\Models\Role;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class RoleResource extends SecureResource
{
    protected static ?string $model = Role::class;
    
    protected static ?string $permissionPrefix = 'roles';

    protected static ?string $navigationIcon = 'heroicon-o-shield-check';
    
    protected static ?string $navigationLabel = 'الأدوار';
    
    protected static ?string $modelLabel = 'دور';
    
    protected static ?string $pluralModelLabel = 'الأدوار';
    
    protected static ?string $navigationGroup = 'إدارة النظام';
    
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('معلومات الدور')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('اسم الدور (English)')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true)
                            ->columnSpanFull(),

                        Forms\Components\TextInput::make('slug')
                            ->label('المعرف (Slug)')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true)
                            ->helperText('مثال: super-admin, manager, employee')
                            ->columnSpanFull(),

                        Forms\Components\Textarea::make('description')
                            ->label('الوصف')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('التصنيف والمظهر')
                    ->schema([
                        Forms\Components\TextInput::make('role_level')
                            ->label('مستوى الدور (1-10)')
                            ->required()
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(10)
                            ->default(1)
                            ->helperText('10 = Super Admin, 7-9 = إدارة عليا, 4-6 = مشرف, 1-3 = موظف'),

                        Forms\Components\ColorPicker::make('color')
                            ->label('اللون')
                            ->default('#6c757d'),

                        Forms\Components\TextInput::make('icon')
                            ->label('الأيقونة')
                            ->default('heroicon-o-user')
                            ->helperText('Heroicons: heroicon-o-*'),

                        Forms\Components\Toggle::make('is_active')
                            ->label('مفعّل')
                            ->default(true),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('الصلاحيات')
                    ->schema([
                        Forms\Components\CheckboxList::make('permissions')
                            ->label('الصلاحيات')
                            ->options([
                                // إدارة المستخدمين
                                'users.view' => '👥 عرض المستخدمين',
                                'users.create' => '➕ إضافة مستخدمين',
                                'users.edit' => '✏️ تعديل المستخدمين',
                                'users.delete' => '🗑️ حذف المستخدمين',
                                
                                // إدارة الحضور
                                'attendance.view' => '📊 عرض الحضور',
                                'attendance.create' => '✅ تسجيل حضور',
                                'attendance.edit' => '✏️ تعديل الحضور',
                                'attendance.delete' => '🗑️ حذف سجلات الحضور',
                                'attendance.export' => '📤 تصدير الحضور',
                                
                                // إدارة الفروع
                                'branches.view' => '🏢 عرض الفروع',
                                'branches.create' => '➕ إضافة فروع',
                                'branches.edit' => '✏️ تعديل الفروع',
                                'branches.delete' => '🗑️ حذف الفروع',
                                
                                // إدارة الأقسام
                                'departments.view' => '📁 عرض الأقسام',
                                'departments.create' => '➕ إضافة أقسام',
                                'departments.edit' => '✏️ تعديل الأقسام',
                                'departments.delete' => '🗑️ حذف الأقسام',
                                
                                // إدارة الورديات
                                'shifts.view' => '⏰ عرض الورديات',
                                'shifts.create' => '➕ إضافة ورديات',
                                'shifts.edit' => '✏️ تعديل الورديات',
                                'shifts.delete' => '🗑️ حذف الورديات',
                                
                                // التحفيز والمكافآت
                                'gamification.view' => '🎮 عرض التحفيز',
                                'gamification.manage' => '🏆 إدارة النقاط والشارات',
                                'rewards.view' => '🎁 عرض المكافآت',
                                'rewards.manage' => '💰 إدارة المكافآت',
                                
                                // التقارير والتحليلات
                                'reports.view' => '📈 عرض التقارير',
                                'reports.export' => '📤 تصدير التقارير',
                                'analytics.view' => '📊 عرض التحليلات',
                                
                                // نظام الفخاخ والنزاهة
                                'traps.view' => '🔍 عرض الفخاخ',
                                'traps.manage' => '⚙️ إدارة الفخاخ',
                                'integrity.view' => '🛡️ عرض تقارير النزاهة',
                                
                                // الإعدادات
                                'settings.view' => '⚙️ عرض الإعدادات',
                                'settings.edit' => '🔧 تعديل الإعدادات',
                                
                                // الأدوار والصلاحيات
                                'roles.view' => '🔐 عرض الأدوار',
                                'roles.create' => '➕ إضافة أدوار',
                                'roles.edit' => '✏️ تعديل الأدوار',
                                'roles.delete' => '🗑️ حذف الأدوار',
                                
                                // صلاحيات خاصة
                                'system.superadmin' => '👑 Super Admin - صلاحيات كاملة',
                                'system.bypass_restrictions' => '🚫 تجاوز القيود',
                            ])
                            ->columns(2)
                            ->gridDirection('row')
                            ->bulkToggleable()
                            ->searchable()
                            ->afterStateHydrated(function (Forms\Components\CheckboxList $component, $state) {
                                if (is_string($state)) {
                                    $component->state(json_decode($state, true) ?? []);
                                }
                            })
                            ->dehydrateStateUsing(fn ($state) => json_encode($state))
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('اسم الدور')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('slug')
                    ->label('المعرف')
                    ->searchable()
                    ->badge()
                    ->color('gray'),

                Tables\Columns\TextColumn::make('role_level')
                    ->label('المستوى')
                    ->sortable()
                    ->badge()
                    ->color(fn ($state) => match (true) {
                        $state >= 10 => 'danger',
                        $state >= 7 => 'warning',
                        $state >= 4 => 'info',
                        default => 'success',
                    })
                    ->formatStateUsing(fn ($state) => "Level $state"),

                Tables\Columns\ColorColumn::make('color')
                    ->label('اللون'),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('الحالة')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),

                Tables\Columns\TextColumn::make('users_count')
                    ->label('عدد المستخدمين')
                    ->counts('users')
                    ->badge()
                    ->color('primary'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('تاريخ الإنشاء')
                    ->dateTime('Y-m-d H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('role_level')
                    ->label('المستوى')
                    ->options([
                        10 => 'Super Admin (10)',
                        9 => 'Admin (9)',
                        8 => 'Senior Manager (8)',
                        7 => 'Manager (7)',
                        6 => 'Team Leader (6)',
                        5 => 'Supervisor (5)',
                        4 => 'Senior Employee (4)',
                        3 => 'Employee (3)',
                        2 => 'Junior Employee (2)',
                        1 => 'Trainee (1)',
                    ]),

                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('مفعّل')
                    ->placeholder('الكل')
                    ->trueLabel('مفعّل فقط')
                    ->falseLabel('معطّل فقط'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->label('عرض'),
                Tables\Actions\EditAction::make()
                    ->label('تعديل'),
                Tables\Actions\DeleteAction::make()
                    ->label('حذف')
                    ->requiresConfirmation(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('حذف المحدد'),
                ]),
            ])
            ->defaultSort('role_level', 'desc');
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
            'index' => Pages\ListRoles::route('/'),
            'create' => Pages\CreateRole::route('/create'),
            'edit' => Pages\EditRole::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }
}
