<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TrapLogResource\Pages;
use App\Filament\Resources\TrapLogResource\RelationManagers;
use App\Models\TrapLog;
use Filament\Forms;
use Filament\Forms\Form;
use App\Filament\Resources\SecureResource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class TrapLogResource extends SecureResource
{
    protected static ?string $model = TrapLog::class;
    
    protected static ?string $permissionPrefix = 'traps';

    protected static ?string $navigationIcon = 'heroicon-o-bug-ant';
    
    protected static ?string $navigationLabel = 'سجل الفخاخ';
    
    protected static ?string $modelLabel = 'سجل فخ';
    
    protected static ?string $pluralModelLabel = 'سجل الفخاخ';
    
    protected static ?string $navigationGroup = 'أمن النظام';
    
    protected static ?int $navigationSort = 61;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('user_id')
                    ->relationship('user', 'id')
                    ->required(),
                Forms\Components\TextInput::make('trap_type')
                    ->required(),
                Forms\Components\TextInput::make('trap_config_id')
                    ->numeric(),
                Forms\Components\TextInput::make('action_taken')
                    ->required(),
                Forms\Components\TextInput::make('action_category')
                    ->required(),
                Forms\Components\TextInput::make('score_change')
                    ->required()
                    ->numeric()
                    ->default(0),
                Forms\Components\TextInput::make('trust_delta')
                    ->required()
                    ->numeric()
                    ->default(0),
                Forms\Components\TextInput::make('curiosity_delta')
                    ->required()
                    ->numeric()
                    ->default(0),
                Forms\Components\TextInput::make('integrity_delta')
                    ->required()
                    ->numeric()
                    ->default(0),
                Forms\Components\TextInput::make('response_time_ms')
                    ->numeric(),
                Forms\Components\Textarea::make('context_data')
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('ip_address'),
                Forms\Components\Textarea::make('user_agent')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('trap_type')
                    ->searchable(),
                Tables\Columns\TextColumn::make('trap_config_id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('action_taken')
                    ->searchable(),
                Tables\Columns\TextColumn::make('action_category')
                    ->searchable(),
                Tables\Columns\TextColumn::make('score_change')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('trust_delta')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('curiosity_delta')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('integrity_delta')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('response_time_ms')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('ip_address')
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->label('عرض'),
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
            'index' => Pages\ListTrapLogs::route('/'),
            'create' => Pages\CreateTrapLog::route('/create'),
            'edit' => Pages\EditTrapLog::route('/{record}/edit'),
        ];
    }
}
