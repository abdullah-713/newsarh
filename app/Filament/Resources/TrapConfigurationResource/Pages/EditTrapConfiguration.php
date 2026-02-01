<?php

namespace App\Filament\Resources\TrapConfigurationResource\Pages;

use App\Filament\Resources\TrapConfigurationResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTrapConfiguration extends EditRecord
{
    protected static string $resource = TrapConfigurationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
