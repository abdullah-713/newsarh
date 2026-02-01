<?php

namespace App\Filament\Resources\TrapLogResource\Pages;

use App\Filament\Resources\TrapLogResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTrapLogs extends ListRecords
{
    protected static string $resource = TrapLogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
