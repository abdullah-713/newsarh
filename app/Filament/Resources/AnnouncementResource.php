<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AnnouncementResource\Pages;
use App\Filament\Resources\AnnouncementResource\RelationManagers;
use App\Models\Announcement;
use Filament\Forms;
use Filament\Forms\Form;
use App\Filament\Resources\SecureResource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class AnnouncementResource extends SecureResource
{
    protected static ?string $model = Announcement::class;
    
    protected static ?string $permissionPrefix = 'settings';

    protected static ?string $navigationIcon = 'heroicon-o-megaphone';
    
    protected static ?string $navigationLabel = 'الإعلانات';
    
    protected static ?string $modelLabel = 'إعلان';
    
    protected static ?string $pluralModelLabel = 'الإعلانات';
    
    protected static ?string $navigationGroup = 'إدارة المحتوى';
    
    protected static ?int $navigationSort = 50;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('محتوى الإعلان')
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->label('عنوان الإعلان')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),

                        Forms\Components\RichEditor::make('content')
                            ->label('المحتوى')
                            ->required()
                            ->columnSpanFull(),

                        Forms\Components\Select::make('priority')
                            ->label('الأولوية')
                            ->options([
                                'low' => '🟢 منخفضة',
                                'normal' => '🟡 عادية',
                                'high' => '🔴 عالية',
                                'urgent' => '⚠️ عاجل',
                            ])
                            ->default('normal')
                            ->required(),

                        Forms\Components\Toggle::make('is_published')
                            ->label('منشور')
                            ->default(true)
                            ->inline(false),
                    ]),

                Forms\Components\Section::make('الاستهداف')
                    ->schema([
                        Forms\Components\DateTimePicker::make('published_at')
                            ->label('تاريخ النشر')
                            ->default(now())
                            ->required(),

                        Forms\Components\DateTimePicker::make('expires_at')
                            ->label('تاريخ الانتهاء')
                            ->nullable()
                            ->after('published_at'),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('العنوان')
                    ->searchable()
                    ->sortable()
                    ->limit(50)
                    ->icon('heroicon-o-megaphone'),
                
                Tables\Columns\BadgeColumn::make('priority')
                    ->label('الأولوية')
                    ->formatStateUsing(fn ($state) => match($state) {
                        'low' => '🟢 منخفضة',
                        'normal' => '🟡 عادية',
                        'high' => '🔴 عالية',
                        'urgent' => '⚠️ عاجل',
                        default => $state,
                    })
                    ->colors([
                        'success' => 'low',
                        'info' => 'normal',
                        'warning' => 'high',
                        'danger' => 'urgent',
                    ])
                    ->sortable(),
                
                Tables\Columns\IconColumn::make('is_published')
                    ->label('منشور')
                    ->boolean()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('published_at')
                    ->label('تاريخ النشر')
                    ->dateTime('Y-m-d H:i')
                    ->sortable()
                    ->icon('heroicon-o-calendar'),
                
                Tables\Columns\TextColumn::make('expires_at')
                    ->label('تاريخ الانتهاء')
                    ->dateTime('Y-m-d H:i')
                    ->sortable()
                    ->placeholder('لا ينتهي')
                    ->toggleable(),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->label('تاريخ الإنشاء')
                    ->dateTime('Y-m-d H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('priority')
                    ->label('الأولوية')
                    ->options([
                        'low' => '🟢 منخفضة',
                        'normal' => '🟡 عادية',
                        'high' => '🔴 عالية',
                        'urgent' => '⚠️ عاجل',
                    ])
                    ->multiple(),
                
                Tables\Filters\TernaryFilter::make('is_published')
                    ->label('منشور'),
                
                Tables\Filters\Filter::make('active')
                    ->label('الإعلانات النشطة')
                    ->query(fn ($query) => $query
                        ->where('is_published', true)
                        ->where('published_at', '<=', now())
                        ->where(fn ($q) => $q
                            ->whereNull('expires_at')
                            ->orWhere('expires_at', '>', now())
                        )
                    )
                    ->default(),
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
            ])
            ->defaultSort('published_at', 'desc');
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
            'index' => Pages\ListAnnouncements::route('/'),
            'create' => Pages\CreateAnnouncement::route('/create'),
            'edit' => Pages\EditAnnouncement::route('/{record}/edit'),
        ];
    }
}
