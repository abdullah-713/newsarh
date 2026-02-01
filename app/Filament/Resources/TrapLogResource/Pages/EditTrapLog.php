<?php

namespace App\Filament\Resources\TrapLogResource\Pages;

use App\Filament\Resources\TrapLogResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTrapLog extends EditRecord
{
    protected static string $resource = TrapLogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
