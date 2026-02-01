<?php

namespace App\Filament\Pages;

use App\Models\SystemSetting;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class SystemSettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';
    
    protected static ?string $navigationLabel = 'إعدادات النظام';
    
    protected static ?string $title = 'إعدادات النظام';
    
    protected static string $view = 'filament.pages.system-settings';
    
    protected static ?int $navigationSort = 100;

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill($this->getSettings());
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('إعدادات الحضور')
                    ->description('إعدادات افتراضية لنظام الحضور والانصراف')
                    ->schema([
                        TimePicker::make('default_start_time')
                            ->label('وقت البداية الافتراضي')
                            ->seconds(false)
                            ->default('08:00')
                            ->required(),

                        TextInput::make('grace_period_minutes')
                            ->label('فترة السماح (بالدقائق)')
                            ->numeric()
                            ->default(15)
                            ->suffix('دقيقة')
                            ->required()
                            ->minValue(0)
                            ->maxValue(60),

                        TextInput::make('penalty_points_per_minute')
                            ->label('نقاط الخصم لكل دقيقة تأخير')
                            ->numeric()
                            ->default(0.5)
                            ->step(0.1)
                            ->required()
                            ->minValue(0)
                            ->maxValue(10),
                    ])->columns(3),

                Section::make('إعدادات التلعيب')
                    ->description('نقاط المكافآت والشارات')
                    ->schema([
                        TextInput::make('base_attendance_points')
                            ->label('نقاط الحضور الأساسية')
                            ->numeric()
                            ->default(10)
                            ->suffix('نقطة')
                            ->required(),

                        TextInput::make('on_time_bonus_points')
                            ->label('نقاط مكافأة الالتزام بالموعد')
                            ->numeric()
                            ->default(5)
                            ->suffix('نقطة')
                            ->required(),

                        TextInput::make('location_verified_bonus')
                            ->label('نقاط مكافأة التحقق من الموقع')
                            ->numeric()
                            ->default(10)
                            ->suffix('نقطة')
                            ->required(),
                    ])->columns(3),

                Section::make('إعدادات الأمان')
                    ->description('إعدادات أمان النظام')
                    ->schema([
                        TextInput::make('max_login_attempts')
                            ->label('الحد الأقصى لمحاولات تسجيل الدخول')
                            ->numeric()
                            ->default(5)
                            ->required()
                            ->minValue(3)
                            ->maxValue(10),

                        TextInput::make('session_timeout_minutes')
                            ->label('مهلة انتهاء الجلسة (بالدقائق)')
                            ->numeric()
                            ->default(120)
                            ->suffix('دقيقة')
                            ->required(),

                        TextInput::make('password_min_length')
                            ->label('الحد الأدنى لطول كلمة المرور')
                            ->numeric()
                            ->default(8)
                            ->required()
                            ->minValue(6)
                            ->maxValue(20),
                    ])->columns(3),
            ])
            ->statePath('data');
    }

    protected function getSettings(): array
    {
        $settings = SystemSetting::pluck('setting_value', 'setting_key')->toArray();

        return [
            'default_start_time' => $settings['default_start_time'] ?? '08:00:00',
            'grace_period_minutes' => $settings['grace_period_minutes'] ?? 15,
            'penalty_points_per_minute' => $settings['penalty_points_per_minute'] ?? 0.5,
            'base_attendance_points' => $settings['base_attendance_points'] ?? 10,
            'on_time_bonus_points' => $settings['on_time_bonus_points'] ?? 5,
            'location_verified_bonus' => $settings['location_verified_bonus'] ?? 10,
            'max_login_attempts' => $settings['max_login_attempts'] ?? 5,
            'session_timeout_minutes' => $settings['session_timeout_minutes'] ?? 120,
            'password_min_length' => $settings['password_min_length'] ?? 8,
        ];
    }

    public function save(): void
    {
        $data = $this->form->getState();

        foreach ($data as $key => $value) {
            SystemSetting::updateOrCreate(
                ['setting_key' => $key],
                [
                    'setting_value' => $value,
                    'setting_type' => $this->getSettingType($value),
                ]
            );
        }

        Notification::make()
            ->success()
            ->title('تم الحفظ')
            ->body('تم حفظ إعدادات النظام بنجاح')
            ->send();
    }

    protected function getSettingType(mixed $value): string
    {
        return match (true) {
            is_int($value) => 'integer',
            is_float($value) => 'decimal',
            is_bool($value) => 'boolean',
            default => 'string',
        };
    }

    protected function getFormActions(): array
    {
        return [
            \Filament\Actions\Action::make('save')
                ->label('حفظ الإعدادات')
                ->action('save')
                ->color('success')
                ->icon('heroicon-o-check'),
        ];
    }
}
