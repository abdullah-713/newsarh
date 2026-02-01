<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserShiftAssignmentResource\Pages;
use App\Filament\Resources\UserShiftAssignmentResource\RelationManagers;
use App\Models\UserShiftAssignment;
use Filament\Forms;
use Filament\Forms\Form;
use App\Filament\Resources\SecureResource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class UserShiftAssignmentResource extends SecureResource
{
    protected static ?string $model = UserShiftAssignment::class;
    
    protected static ?string $permissionPrefix = 'shifts';

    protected static ?string $navigationIcon = 'heroicon-o-arrow-path';
    
    protected static ?string $navigationLabel = 'تعيين الورديات';
    
    protected static ?string $modelLabel = 'تعيين وردية';
    
    protected static ?string $pluralModelLabel = 'تعيين الورديات';
    
    protected static ?string $navigationGroup = 'إدارة الورديات';
    
    protected static ?int $navigationSort = 32;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('user_id')
                    ->relationship('user', 'id')
                    ->required(),
                Forms\Components\Select::make('shift_id')
                    ->relationship('shift', 'name')
                    ->required(),
                Forms\Components\DatePicker::make('effective_from')
                    ->required(),
                Forms\Components\DatePicker::make('effective_until'),
                Forms\Components\TextInput::make('assigned_by')
                    ->numeric(),
                Forms\Components\Textarea::make('notes')
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
                Tables\Columns\TextColumn::make('shift.name')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('effective_from')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('effective_until')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('assigned_by')
                    ->numeric()
                    ->sortable(),
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
            'index' => Pages\ListUserShiftAssignments::route('/'),
            'create' => Pages\CreateUserShiftAssignment::route('/create'),
            'edit' => Pages\EditUserShiftAssignment::route('/{record}/edit'),
        ];
    }
}
