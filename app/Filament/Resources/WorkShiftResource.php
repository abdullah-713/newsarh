<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WorkShiftResource\Pages;
use App\Filament\Resources\WorkShiftResource\RelationManagers;
use App\Models\WorkShift;
use Filament\Forms;
use Filament\Forms\Form;
use App\Filament\Resources\SecureResource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class WorkShiftResource extends SecureResource
{
    protected static ?string $model = WorkShift::class;
    
    protected static ?string $permissionPrefix = 'shifts';

    protected static ?string $navigationIcon = 'heroicon-o-calendar';
    
    protected static ?string $navigationLabel = 'الورديات';
    
    protected static ?string $modelLabel = 'وردية';
    
    protected static ?string $pluralModelLabel = 'الورديات';
    
    protected static ?string $navigationGroup = 'إدارة الورديات';
    
    protected static ?int $navigationSort = 30;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required(),
                Forms\Components\TextInput::make('code')
                    ->required(),
                Forms\Components\TextInput::make('start_time')
                    ->required(),
                Forms\Components\TextInput::make('end_time')
                    ->required(),
                Forms\Components\Toggle::make('is_overnight')
                    ->required(),
                Forms\Components\TextInput::make('grace_period_minutes')
                    ->required()
                    ->numeric()
                    ->default(15),
                Forms\Components\TextInput::make('min_working_hours')
                    ->required()
                    ->numeric()
                    ->default(8),
                Forms\Components\TextInput::make('max_working_hours')
                    ->required()
                    ->numeric()
                    ->default(12),
                Forms\Components\Toggle::make('is_active')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('code')
                    ->searchable(),
                Tables\Columns\TextColumn::make('start_time'),
                Tables\Columns\TextColumn::make('end_time'),
                Tables\Columns\IconColumn::make('is_overnight')
                    ->boolean(),
                Tables\Columns\TextColumn::make('grace_period_minutes')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('min_working_hours')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('max_working_hours')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean(),
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
            'index' => Pages\ListWorkShifts::route('/'),
            'create' => Pages\CreateWorkShift::route('/create'),
            'edit' => Pages\EditWorkShift::route('/{record}/edit'),
        ];
    }
}
