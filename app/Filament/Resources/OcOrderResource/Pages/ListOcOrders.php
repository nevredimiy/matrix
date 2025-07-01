<?php

namespace App\Filament\Resources\OcOrderResource\Pages;

use App\Filament\Resources\OcOrderResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListOcOrders extends ListRecords
{
    protected static string $resource = OcOrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
