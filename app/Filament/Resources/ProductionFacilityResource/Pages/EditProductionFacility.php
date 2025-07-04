<?php

namespace App\Filament\Resources\ProductionFacilityResource\Pages;

use App\Filament\Resources\ProductionFacilityResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditProductionFacility extends EditRecord
{
    protected static string $resource = ProductionFacilityResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
