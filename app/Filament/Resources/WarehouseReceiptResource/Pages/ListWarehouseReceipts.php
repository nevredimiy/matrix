<?php

namespace App\Filament\Resources\WarehouseReceiptResource\Pages;

use App\Filament\Resources\WarehouseReceiptResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListWarehouseReceipts extends ListRecords
{
    protected static string $resource = WarehouseReceiptResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
