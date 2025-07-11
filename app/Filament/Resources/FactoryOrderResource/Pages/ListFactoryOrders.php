<?php

namespace App\Filament\Resources\FactoryOrderResource\Pages;

use App\Filament\Resources\FactoryOrderResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListFactoryOrders extends ListRecords
{
    protected static string $resource = FactoryOrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
