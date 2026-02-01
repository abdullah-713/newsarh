<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\SecureResource;
use App\Models\User;
use App\Models\Role;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;

class UserResource extends SecureResource
{
    protected static ?string $model = User::class;
    
    protected static ?string $permissionPrefix = 'users';

    protected static ?string $navigationIcon = 'heroicon-o-users';
    
    protected static ?string $navigationLabel = 'المستخدمين';
    
    protected static ?string $modelLabel = 'مستخدم';
    
    protected static ?string $pluralModelLabel = 'المستخدمين';
    
    protected static ?string $navigationGroup = 'إدارة النظام';
    
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Tabs::make('المعلومات')
                    ->tabs([
                        Forms\Components\Tabs\Tab::make('المعلومات الأساسية')
                            ->icon('heroicon-o-user')
                            ->schema([
                                Forms\Components\Section::make()
                                    ->schema([
                                        Forms\Components\TextInput::make('full_name')
                                            ->label('الاسم الكامل')
                                            ->required()
                                            ->maxLength(255),

                                        Forms\Components\TextInput::make('username')
                                            ->label('اسم المستخدم')
                                            ->required()
                                            ->unique(ignoreRecord: true)
                                            ->maxLength(255)
                                            ->alphaDash(),

                                        Forms\Components\TextInput::make('emp_code')
                                            ->label('رقم الموظف')
                                            ->required()
                                            ->unique(ignoreRecord: true)
                                            ->maxLength(255),

                                        Forms\Components\TextInput::make('email')
                                            ->label('البريد الإلكتروني')
                                            ->email()
                                            ->required()
                                            ->unique(ignoreRecord: true)
                                            ->maxLength(255),

                                        Forms\Components\TextInput::make('phone')
                                            ->label('رقم الجوال')
                                            ->tel()
                                            ->maxLength(20),

                                        Forms\Components\TextInput::make('national_id')
                                            ->label('رقم الهوية')
                                            ->maxLength(20),

                                        Forms\Components\TextInput::make('password')
                                            ->label('كلمة المرور')
                                            ->password()
                                            ->dehydrateStateUsing(fn ($state) => $state ? Hash::make($state) : null)
                                            ->dehydrated(fn ($state) => filled($state))
                                            ->required(fn (string $context): bool => $context === 'create')
                                            ->minLength(6)
                                            ->maxLength(255),

                                        Forms\Components\DatePicker::make('hire_date')
                                            ->label('تاريخ التوظيف')
                                            ->default(now()),
                                    ])
                                    ->columns(2),
                            ]),

                        Forms\Components\Tabs\Tab::make('الدور والصلاحيات')
                            ->icon('heroicon-o-shield-check')
                            ->schema([
                                Forms\Components\Section::make('الدور الوظيفي')
                                    ->schema([
                                        Forms\Components\Select::make('role_id')
                                            ->label('الدور')
                                            ->relationship('role', 'name')
                                            ->required()
                                            ->searchable()
                                            ->preload()
                                            ->live()
                                            ->afterStateUpdated(function ($state, Forms\Set $set) {
                                                if ($state) {
                                                    $role = Role::find($state);
                                                    if ($role) {
                                                        // نسخ صلاحيات الدور كأساس
                                                        $set('permissions', $role->permissions);
                                                    }
                                                }
                                            }),

                                        Forms\Components\Toggle::make('is_super_admin')
                                            ->label('Super Admin')
                                            ->helperText('صلاحيات كاملة على النظام')
                                            ->visible(fn () => auth()->user()?->is_super_admin === true)
                                            ->default(false),

                                        Forms\Components\Toggle::make('is_active')
                                            ->label('مفعّل')
                                            ->default(true),
                                    ])
                                    ->columns(3),

                                Forms\Components\Section::make('صلاحيات مخصصة')
                                    ->description('يمكنك تخصيص صلاحيات إضافية أو إزالة صلاحيات موجودة من الدور')
                                    ->schema([
                                        Forms\Components\CheckboxList::make('permissions')
                                            ->label('الصلاحيات المخصصة')
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
                            ]),

                        Forms\Components\Tabs\Tab::make('التنظيم الوظيفي')
                            ->icon('heroicon-o-building-office')
                            ->schema([
                                Forms\Components\Section::make()
                                    ->schema([
                                        Forms\Components\Select::make('branch_id')
                                            ->label('الفرع')
                                            ->relationship('branch', 'name')
                                            ->searchable()
                                            ->preload(),

                                        Forms\Components\Select::make('department_id')
                                            ->label('القسم')
                                            ->relationship('department', 'name')
                                            ->searchable()
                                            ->preload(),

                                        Forms\Components\Select::make('team_id')
                                            ->label('الفريق')
                                            ->relationship('team', 'name')
                                            ->searchable()
                                            ->preload(),

                                        Forms\Components\Select::make('job_title_id')
                                            ->label('المسمى الوظيفي')
                                            ->relationship('jobTitle', 'name')
                                            ->searchable()
                                            ->preload(),

                                        Forms\Components\Select::make('managed_by')
                                            ->label('المدير المباشر')
                                            ->relationship('manager', 'full_name')
                                            ->searchable()
                                            ->preload(),
                                    ])
                                    ->columns(2),
                            ]),

                        Forms\Components\Tabs\Tab::make('التحفيز والنقاط')
                            ->icon('heroicon-o-trophy')
                            ->schema([
                                Forms\Components\Section::make()
                                    ->schema([
                                        Forms\Components\TextInput::make('current_points')
                                            ->label('النقاط الحالية')
                                            ->numeric()
                                            ->default(0)
                                            ->readOnly(),

                                        Forms\Components\TextInput::make('total_points_earned')
                                            ->label('إجمالي النقاط المكتسبة')
                                            ->numeric()
                                            ->default(0)
                                            ->readOnly(),

                                        Forms\Components\TextInput::make('total_points_deducted')
                                            ->label('إجمالي النقاط المخصومة')
                                            ->numeric()
                                            ->default(0)
                                            ->readOnly(),

                                        Forms\Components\TextInput::make('streak_count')
                                            ->label('سلسلة الحضور')
                                            ->numeric()
                                            ->default(0)
                                            ->readOnly()
                                            ->helperText('عدد أيام الحضور المتتالية'),
                                    ])
                                    ->columns(2),
                            ]),
                    ])
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('emp_code')
                    ->label('رقم الموظف')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->badge()
                    ->color('primary'),

                Tables\Columns\TextColumn::make('full_name')
                    ->label('الاسم')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('email')
                    ->label('البريد الإلكتروني')
                    ->searchable()
                    ->copyable()
                    ->icon('heroicon-o-envelope'),

                Tables\Columns\TextColumn::make('role.name')
                    ->label('الدور')
                    ->badge()
                    ->color(fn ($record) => match (true) {
                        $record->is_super_admin => 'danger',
                        $record->role?->role_level >= 7 => 'warning',
                        $record->role?->role_level >= 4 => 'info',
                        default => 'success',
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('branch.name')
                    ->label('الفرع')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('department.name')
                    ->label('القسم')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\IconColumn::make('is_super_admin')
                    ->label('Super Admin')
                    ->boolean()
                    ->trueIcon('heroicon-o-shield-check')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('danger')
                    ->falseColor('gray')
                    ->toggleable(),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('مفعّل')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),

                Tables\Columns\TextColumn::make('current_points')
                    ->label('النقاط')
                    ->numeric()
                    ->sortable()
                    ->badge()
                    ->color('warning')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('last_login_at')
                    ->label('آخر تسجيل دخول')
                    ->dateTime('Y-m-d H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('تاريخ الإنشاء')
                    ->dateTime('Y-m-d')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('role_id')
                    ->label('الدور')
                    ->relationship('role', 'name')
                    ->searchable()
                    ->preload(),

                Tables\Filters\SelectFilter::make('branch_id')
                    ->label('الفرع')
                    ->relationship('branch', 'name')
                    ->searchable()
                    ->preload(),

                Tables\Filters\TernaryFilter::make('is_super_admin')
                    ->label('Super Admin')
                    ->placeholder('الكل')
                    ->trueLabel('Super Admin فقط')
                    ->falseLabel('عادي فقط'),

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
            ->defaultSort('created_at', 'desc');
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
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }
}
