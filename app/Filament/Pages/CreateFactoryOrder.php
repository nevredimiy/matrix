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

        // Предзаполняем список товаров на производство
        $this->items = $orders->map(fn ($order) => [
            'order_id' => $order->order_number,
            'product_sku' => $order->product_sku,
            'product_name' => $order?->name ?? 'Неизвестный товар',
            'image' => $order?->image ?? null,
            'stock_quantity' => $order?->stock_quantity ?? 0,
            'quantity' => $order->quantity,
        ])->toArray();
        
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
