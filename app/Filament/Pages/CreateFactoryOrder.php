<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Models\Order;
use App\Models\Product;
use Filament\Notifications\Notification;

class CreateFactoryOrder extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.create-factory-order';

    public $items = [];
    public $factoryId = null;
    public $factories = [];


    public function mount()
    {
        $orderIds = session('selected_order_ids', []);

        $orders = Order::whereIn('id', $orderIds)->get();

        $itemsGrouped = [];   // ← массив вместо collect()

        foreach ($orders as $order) {
            foreach ($order->orderProducts as $op) {
                $product = $op->product;
                $sku     = $product->sku ?? null;

                if (!$sku) {
                    continue;
                }

                // инициализируем строку, если её ещё нет
                if (!isset($itemsGrouped[$sku])) {
                    $itemsGrouped[$sku] = [
                        'product_sku'    => $sku,
                        'product_name'   => $product->name ?? 'Неизвестно',
                        'image'          => $product->image ?? null,
                        'stock_quantity' => $product->stock_quantity ?? 0,
                        'quantity'       => 0,
                        'order_ids'      => [],
                    ];
                }

                // накапливаем
                $itemsGrouped[$sku]['quantity']     += $op->quantity;
                $itemsGrouped[$sku]['order_ids'][]   = $order->order_number;
            }
        }

        

        $this->items = collect($itemsGrouped)
            ->map(function ($row) {
                $row['order_id'] = implode(',', $row['order_ids']);
                unset($row['order_ids']);
                return $row;
            })
            ->values()
            ->toArray();

               // Список производств
        $this->factories = \App\Models\Factory::pluck('name', 'id')->toArray();
    }
    

    public function updatedItems($value, $name)
    {
        // $name приходит как "0.product_sku" или "1.product_sku" и т.п.
        if (str_ends_with($name, 'product_sku')) {
            // Получаем индекс записи (например, 0)
            $index = intval(explode('.', $name)[0]);

            $sku = $value;

            // Ищем товар по артикулу
            $product = \App\Models\Product::where('sku', $sku)->first();

            if ($product) {
                // Обновляем поля в $items для этой строки
                $this->items[$index]['product_name'] = $product->name;
                $this->items[$index]['image'] = $product->image;
                $this->items[$index]['stock_quantity'] = $product->stock_quantity;
            } else {
                // Если товар не найден — очищаем поля
                $this->items[$index]['product_name'] = '';
                $this->items[$index]['image'] = null;
                $this->items[$index]['stock_quantity'] = 0;
            }
        }
    }

    public function save()
    {
        if (!$this->factoryId) {
            $this->addError('factoryId', 'Выберите производство.');
            Notification::make()
                ->title('Ошибка')
                ->body('Не удалось создать заказ: Виберіть виробництво' )
                ->danger()
                ->send();
            return;
        }

        try {
            $factoryOrder = \App\Models\FactoryOrder::create([
                'factory_id' => $this->factoryId,
                'status' => 'в процессе',
            ]);

            foreach ($this->items as $item) {
                $product = Product::where('sku', $item['product_sku'])->first();
                

                if ($product) {
                    $factoryOrder->items()->create([
                        'product_id' => $product->id,
                        'quantity_ordered' => $item['quantity'],
                    ]);
                }
            }    
        } catch (\Throwable $th) {
            // Показываем ошибку во flash-сообщении
            session()->flash('error', 'Ошибка при создании заказа: ' . $th->getMessage());

            // Или через Filament Notification (если подключено):
            Notification::make()
                ->title('Ошибка')
                ->body('Не удалось создать заказ: ' . $th->getMessage())
                ->danger()
                ->send();
        }       

        session()->flash('success', 'Заказ на производство создан!');
        return redirect()->route('filament.admin.resources.factory-order-items.index');
    }

    public function addEmptyItem()
    {
        $this->items[] = [
            'order_id' => null,
            'product_sku' => '',
            'product_name' => '',
            'image' => null,
            'stock_quantity' => 0,
            'quantity' => 1,
        ];
    }

    public function removeItem($index)
    {
        unset($this->items[$index]);

        // Сброс ключей массива (чтобы избежать пропуска индексов)
        $this->items = array_values($this->items);
    }


}
