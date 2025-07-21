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

        foreach ($this->data['product'] as $item) {
            FactoryProductDelivery::create($item);
            
            // Есле проиводство незадано, то ставим 1 - первое
            if(!$item['factory_id']){
                $item['factory_id'] = 1; 
            }
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
        $factoryOrderItem = FactoryOrderItem::where('product_id', $item['product_id'])
            ->whereHas('factoryOrder', function ($q) use($item) {
                $q->where('factory_id', $item['factory_id']);
            })
            ->first();
            
        if ($factoryOrderItem) {
            $factoryOrderItem->quantity_delivered += $item['quantity'];
            $factoryOrderItem->save();
        }
    }

  
}
