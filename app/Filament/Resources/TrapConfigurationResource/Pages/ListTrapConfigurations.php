<?php

namespace App\Filament\Resources\TrapConfigurationResource\Pages;

use App\Filament\Resources\TrapConfigurationResource;
use Filament\Resources\Pages\ListRecords;

class ListTrapConfigurations extends ListRecords
{
    protected static string $resource = TrapConfigurationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\CreateAction::make(),
        ];
    }
}
