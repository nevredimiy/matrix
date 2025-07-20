<?php

namespace App\Filament\Resources\FactoryProductDeliveryResource\Pages;

use App\Filament\Resources\FactoryProductDeliveryResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListFactoryProductDeliveries extends ListRecords
{
    protected static string $resource = FactoryProductDeliveryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
