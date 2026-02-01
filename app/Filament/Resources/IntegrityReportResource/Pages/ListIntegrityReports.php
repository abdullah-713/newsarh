<?php

namespace App\Filament\Resources\IntegrityReportResource\Pages;

use App\Filament\Resources\IntegrityReportResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListIntegrityReports extends ListRecords
{
    protected static string $resource = IntegrityReportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
