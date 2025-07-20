<?php

namespace App\Filament\Resources\FactoryOrderDeliveryResource\Pages;

use App\Filament\Resources\FactoryOrderDeliveryResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListFactoryOrderDeliveries extends ListRecords
{
    protected static string $resource = FactoryOrderDeliveryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
