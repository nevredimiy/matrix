<?php

namespace App\Filament\Resources\WarehouseReceiptResource\Pages;

use App\Filament\Resources\WarehouseReceiptResource;
use Filament\Resources\Pages\CreateRecord;

class CreateWarehouseReceipt extends CreateRecord
{
    protected static string $resource = WarehouseReceiptResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Прийомка товару створена';
    }
}
