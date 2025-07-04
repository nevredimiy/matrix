<?php

namespace App\Filament\Resources\ProductionFacilityResource\Pages;

use App\Filament\Resources\ProductionFacilityResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListProductionFacilities extends ListRecords
{
    protected static string $resource = ProductionFacilityResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
