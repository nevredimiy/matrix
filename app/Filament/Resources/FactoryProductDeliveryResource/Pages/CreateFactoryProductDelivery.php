<?php

namespace App\Filament\Resources\FactoryProductDeliveryResource\Pages;

use App\Filament\Resources\FactoryProductDeliveryResource;
use App\Models\FactoryOrder;
use App\Models\FactoryOrderItem;
use App\Models\FactoryProductDelivery;
use Filament\Actions;
use Filament\Forms\Form;
use Filament\Resources\Pages\CreateRecord;

class CreateFactoryProductDelivery extends CreateRecord
{
    protected static string $resource = FactoryProductDeliveryResource::class;

    protected function beforeCreate(): void
    {

        foreach ($this->data['Товар'] as $item) {
            FactoryProductDelivery::create($item);
            
            // Обновляем таблицу  factory_ order_items
            $this->updateFactoryOrderItem($item);
        }

        
        // Можно сделать редирект на список:
        $this->redirect(FactoryProductDeliveryResource::getUrl('index'));
        
        // Предотвращаем стандартное сохранение одной записи
        $this->halt();
    }

    protected function updateFactoryOrderItem($item)
    {
        $countProductDelivery = $item['quantity'];

        $factoryOrderIteams = FactoryOrderItem::all();

        $factoryOrders = FactoryOrder::all();
    }

  
}
