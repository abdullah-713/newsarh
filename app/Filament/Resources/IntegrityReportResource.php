<?php

namespace App\Filament\Resources;

use App\Filament\Resources\IntegrityReportResource\Pages;
use App\Filament\Resources\IntegrityReportResource\RelationManagers;
use App\Models\IntegrityReport;
use Filament\Forms;
use Filament\Forms\Form;
use App\Filament\Resources\SecureResource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class IntegrityReportResource extends SecureResource
{
    protected static ?string $model = IntegrityReport::class;
    
    protected static ?string $permissionPrefix = 'integrity';

    protected static ?string $navigationIcon = 'heroicon-o-shield-exclamation';
    
    protected static ?string $navigationLabel = 'تقارير النزاهة';
    
    protected static ?string $modelLabel = 'تقرير نزاهة';
    
    protected static ?string $pluralModelLabel = 'تقارير النزاهة';
    
    protected static ?string $navigationGroup = 'أمن النظام';
    
    protected static ?int $navigationSort = 60;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('sender_id')
                    ->relationship('sender', 'id')
                    ->required(),
                Forms\Components\TextInput::make('reported_id')
                    ->numeric(),
                Forms\Components\TextInput::make('report_type')
                    ->required(),
                Forms\Components\Textarea::make('content')
                    ->required()
                    ->columnSpanFull(),
                Forms\Components\Textarea::make('evidence_files')
                    ->columnSpanFull(),
                Forms\Components\Toggle::make('is_anonymous_claim')
                    ->required(),
                Forms\Components\Textarea::make('admin_notes')
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('status')
                    ->required(),
                Forms\Components\TextInput::make('resolved_by')
                    ->numeric(),
                Forms\Components\DateTimePicker::make('resolved_at'),
                Forms\Components\Textarea::make('sender_revealed_to')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('sender.id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('reported_id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('report_type')
                    ->searchable(),
                Tables\Columns\IconColumn::make('is_anonymous_claim')
                    ->boolean(),
                Tables\Columns\TextColumn::make('status')
                    ->searchable(),
                Tables\Columns\TextColumn::make('resolved_by')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('resolved_at')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
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
            'index' => Pages\ListIntegrityReports::route('/'),
            'create' => Pages\CreateIntegrityReport::route('/create'),
            'edit' => Pages\EditIntegrityReport::route('/{record}/edit'),
        ];
    }
}
