<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TrapConfigurationResource\Pages;
use App\Models\TrapConfiguration;
use Filament\Forms;
use Filament\Forms\Form;
use App\Filament\Resources\SecureResource;
use Filament\Tables;
use Filament\Tables\Table;

class TrapConfigurationResource extends SecureResource
{
    protected static ?string $model = TrapConfiguration::class;

    protected static ?string $navigationIcon = 'heroicon-o-shield-exclamation';
    
    protected static ?string $navigationLabel = 'إعدادات الفخاخ';
    
    protected static ?string $navigationGroup = 'النزاهة والأمان';
    
    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('trap_type')
                    ->label('نوع الفخ')
                    ->options([
                        'fake_button' => 'زر وهمي',
                        'prohibited_section' => 'قسم محظور',
                        'fake_file_download' => 'تحميل ملف وهمي',
                        'screenshot_detector' => 'كاشف لقطة الشاشة',
                        'copy_paste_detector' => 'كاشف النسخ واللصق',
                    ])
                    ->required(),

                Forms\Components\TextInput::make('trap_name_ar')
                    ->label('اسم الفخ بالعربية')
                    ->helperText('النص الذي سيظهر للموظف (مثل: "تسريب الرواتب", "قسم السرية")')
                    ->required()
                    ->maxLength(100),

                Forms\Components\TextInput::make('trap_name')
                    ->label('اسم الفخ بالإنجليزية')
                    ->helperText('للاستخدام الإداري')
                    ->maxLength(100),

                Forms\Components\Textarea::make('description')
                    ->label('وصف الفخ')
                    ->helperText('وصف داخلي للإدارة')
                    ->rows(3),

                Forms\Components\Select::make('trigger_action')
                    ->label('إجراء التفعيل')
                    ->options([
                        'log_only' => 'تسجيل فقط',
                        'log_and_alert' => 'تسجيل وتنبيه',
                        'log_and_flag_user' => 'تسجيل ووضع علامة على المستخدم',
                        'log_and_suspend' => 'تسجيل وتعليق حساب مؤقت',
                    ])
                    ->default('log_only')
                    ->helperText('سيتم تخزينه في حقل settings كـ JSON'),

                Forms\Components\TextInput::make('trigger_chance')
                    ->label('نسبة الظهور')
                    ->numeric()
                    ->minValue(0.01)
                    ->maxValue(1.00)
                    ->step(0.01)
                    ->default(0.10)
                    ->helperText('0.10 = 10% من الموظفين سيرون هذا الفخ'),

                Forms\Components\TextInput::make('cooldown_minutes')
                    ->label('مدة الانتظار (بالدقائق)')
                    ->numeric()
                    ->default(10080)
                    ->helperText('المدة بين تفعيلين متتاليين (10080 = أسبوع)'),

                Forms\Components\TextInput::make('min_role_level')
                    ->label('الحد الأدنى لمستوى الصلاحية')
                    ->numeric()
                    ->minValue(1)
                    ->maxValue(10)
                    ->default(1),

                Forms\Components\TextInput::make('max_role_level')
                    ->label('الحد الأقصى لمستوى الصلاحية')
                    ->numeric()
                    ->minValue(1)
                    ->maxValue(10)
                    ->default(7),

                Forms\Components\Toggle::make('is_active')
                    ->label('مفعّل')
                    ->default(true),

                Forms\Components\KeyValue::make('settings')
                    ->label('إعدادات إضافية')
                    ->keyLabel('المفتاح')
                    ->valueLabel('القيمة')
                    ->helperText('إعدادات إضافية مثل trigger_action, severity_level, target_panel'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('trap_type')
                    ->label('نوع الفخ')
                    ->badge()
                    ->formatStateUsing(fn ($state) => match($state) {
                        'fake_button' => 'زر وهمي',
                        'prohibited_section' => 'قسم محظور',
                        'fake_file_download' => 'تحميل ملف',
                        'screenshot_detector' => 'كاشف لقطة الشاشة',
                        'copy_paste_detector' => 'كاشف النسخ',
                        default => $state
                    }),

                Tables\Columns\TextColumn::make('trap_name_ar')
                    ->label('اسم الفخ')
                    ->searchable(),

                Tables\Columns\TextColumn::make('trigger_chance')
                    ->label('نسبة الظهور')
                    ->formatStateUsing(fn ($state) => ($state * 100) . '%')
                    ->badge()
                    ->color('info'),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('الحالة')
                    ->boolean(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('تاريخ الإنشاء')
                    ->dateTime('Y-m-d H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('trap_type')
                    ->label('نوع الفخ')
                    ->options([
                        'fake_button' => 'زر وهمي',
                        'prohibited_section' => 'قسم محظور',
                        'fake_file_download' => 'تحميل ملف',
                    ]),

                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('الحالة'),
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
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTrapConfigurations::route('/'),
            'create' => Pages\CreateTrapConfiguration::route('/create'),
            'edit' => Pages\EditTrapConfiguration::route('/{record}/edit'),
        ];
    }
}
