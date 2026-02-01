<?php

namespace App\Filament\Resources\UserShiftAssignmentResource\Pages;

use App\Filament\Resources\UserShiftAssignmentResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditUserShiftAssignment extends EditRecord
{
    protected static string $resource = UserShiftAssignmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
