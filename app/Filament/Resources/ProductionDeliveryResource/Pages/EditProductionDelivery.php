<?php

namespace App\Filament\Resources\ProductionDeliveryResource\Pages;

use App\Filament\Resources\ProductionDeliveryResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditProductionDelivery extends EditRecord
{
    protected static string $resource = ProductionDeliveryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
