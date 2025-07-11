<?php

namespace App\Filament\Resources\FactoryOrderItemResource\Pages;

use App\Filament\Resources\FactoryOrderItemResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditFactoryOrderItem extends EditRecord
{
    protected static string $resource = FactoryOrderItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
