<?php

namespace App\Filament\Resources\FactoryOrderDeliveryResource\Pages;

use App\Filament\Resources\FactoryOrderDeliveryResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditFactoryOrderDelivery extends EditRecord
{
    protected static string $resource = FactoryOrderDeliveryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
