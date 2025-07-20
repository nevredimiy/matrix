<?php

namespace App\Filament\Resources\FactoryProductDeliveryResource\Pages;

use App\Filament\Resources\FactoryProductDeliveryResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditFactoryProductDelivery extends EditRecord
{
    protected static string $resource = FactoryProductDeliveryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    // protected function beforeFill(): void
    // {
       
        
    //     // Можно сделать редирект на список:
    //     $this->redirect(FactoryProductDeliveryResource::getUrl('index'));
    // }
}
