<?php

namespace App\Filament\Resources\IntegrityReportResource\Pages;

use App\Filament\Resources\IntegrityReportResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditIntegrityReport extends EditRecord
{
    protected static string $resource = IntegrityReportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
