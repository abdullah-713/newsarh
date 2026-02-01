<?php

namespace App\Filament\Resources;

use App\Filament\Resources\GeofenceResource\Pages;
use App\Filament\Resources\GeofenceResource\RelationManagers;
use App\Models\Geofence;
use Filament\Forms;
use Filament\Forms\Form;
use App\Filament\Resources\SecureResource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class GeofenceResource extends SecureResource
{
    protected static ?string $model = Geofence::class;
    
    protected static ?string $permissionPrefix = 'attendance';

    protected static ?string $navigationIcon = 'heroicon-o-map-pin';
    
    protected static ?string $navigationLabel = 'السياج الجغرافي';
    
    protected static ?string $modelLabel = 'سياج جغرافي';
    
    protected static ?string $pluralModelLabel = 'السياج الجغرافي';
    
    protected static ?string $navigationGroup = 'إدارة الموارد البشرية';
    
    protected static ?int $navigationSort = 11;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('معلومات السياج الجغرافي')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('اسم الموقع')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),

                        Forms\Components\Textarea::make('description')
                            ->label('الوصف')
                            ->rows(3)
                            ->columnSpanFull(),

                        Forms\Components\Select::make('branch_id')
                            ->label('الفرع')
                            ->relationship('branch', 'name')
                            ->searchable()
                            ->preload()
                            ->columnSpanFull(),
                    ]),

                Forms\Components\Section::make('الموقع الجغرافي')
                    ->description('حدد موقع السياج الجغرافي على الخريطة')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('latitude')
                                    ->label('خط العرض (Latitude)')
                                    ->required()
                                    ->numeric()
                                    ->step(0.0000001)
                                    ->default(24.7136)
                                    ->helperText('مثال: 24.7136 (الرياض)')
                                    ->reactive(),

                                Forms\Components\TextInput::make('longitude')
                                    ->label('خط الطول (Longitude)')
                                    ->required()
                                    ->numeric()
                                    ->step(0.0000001)
                                    ->default(46.6753)
                                    ->helperText('مثال: 46.6753 (الرياض)')
                                    ->reactive(),
                            ]),

                        Forms\Components\TextInput::make('radius')
                            ->label('نصف القطر (متر)')
                            ->required()
                            ->numeric()
                            ->default(100)
                            ->suffix('متر')
                            ->helperText('المسافة المسموحة من نقطة المركز')
                            ->minValue(10)
                            ->maxValue(10000),

                        Forms\Components\ViewField::make('map')
                            ->label('الخريطة التفاعلية')
                            ->view('filament.forms.components.leaflet-map')
                            ->columnSpanFull(),
                    ]),

                Forms\Components\Section::make('إعدادات التنبيه')
                    ->schema([
                        Forms\Components\Select::make('alert_type')
                            ->label('نوع التنبيه')
                            ->options([
                                'entry' => '🟢 عند الدخول فقط',
                                'exit' => '🔴 عند الخروج فقط',
                                'both' => '🟡 عند الدخول والخروج',
                            ])
                            ->default('exit')
                            ->required(),

                        Forms\Components\Toggle::make('is_active')
                            ->label('مفعّل')
                            ->default(true)
                            ->inline(false),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('اسم الموقع')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('branch.name')
                    ->label('الفرع')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('latitude')
                    ->label('خط العرض')
                    ->numeric(7)
                    ->toggleable(),

                Tables\Columns\TextColumn::make('longitude')
                    ->label('خط الطول')
                    ->numeric(7)
                    ->toggleable(),

                Tables\Columns\TextColumn::make('radius')
                    ->label('نصف القطر')
                    ->suffix(' م')
                    ->sortable(),

                Tables\Columns\BadgeColumn::make('alert_type')
                    ->label('نوع التنبيه')
                    ->formatStateUsing(fn ($state) => match($state) {
                        'entry' => 'دخول',
                        'exit' => 'خروج',
                        'both' => 'دخول وخروج',
                        default => $state,
                    })
                    ->colors([
                        'success' => 'entry',
                        'danger' => 'exit',
                        'warning' => 'both',
                    ]),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('الحالة')
                    ->boolean()
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('تاريخ الإنشاء')
                    ->dateTime('Y-m-d H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('branch_id')
                    ->label('الفرع')
                    ->relationship('branch', 'name'),

                Tables\Filters\SelectFilter::make('alert_type')
                    ->label('نوع التنبيه')
                    ->options([
                        'entry' => 'دخول',
                        'exit' => 'خروج',
                        'both' => 'دخول وخروج',
                    ]),

                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('مفعّل')
                    ->placeholder('الكل')
                    ->trueLabel('مفعّل')
                    ->falseLabel('معطّل'),
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

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListGeofences::route('/'),
            'create' => Pages\CreateGeofence::route('/create'),
            'edit' => Pages\EditGeofence::route('/{record}/edit'),
        ];
    }
}
