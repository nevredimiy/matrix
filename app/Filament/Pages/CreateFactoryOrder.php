<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Models\Order;
use App\Models\Product;

class CreateFactoryOrder extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.create-factory-order';

    public $items = [];

    public function mount()
    {
        $orderIds = session('selected_order_ids', []);
        $orders = Order::whereIn('id', $orderIds)->get();

        // dd($orders);

        // Предзаполняем список товаров на производство
        $this->items = $orders->map(fn ($order) => [
            'order_id' => $order->order_number,
            'product_sku' => $order->product_sku,
            'product_name' => $order?->name ?? 'Неизвестный товар',
            'image' => $order?->image ?? null,
            'stock_quantity' => $order?->stock_quantity ?? 0,
            'quantity' => $order->quantity,
        ])->toArray();
        // dd($this->items);
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
        // $factoryOrder = \App\Models\FactoryOrder::create([
        //     'factory_id' => 'FO-' . now()->format('YmdHi'),
        //     'status' => 'в процессе',
        // ]);

        dd($this->items);
        foreach ($this->items as $item) {
            $product = Product::where('sku', $item['product_sku'])->first();
            

            if ($product) {
                $factoryOrder->items()->create([
                    'product_id' => $product->id,
                    'quantity_ordered' => $item['quantity'],
                ]);
            }
        }

        session()->flash('success', 'Заказ на производство создан!');
        return redirect()->route('filament.admin.resources.factory-orders.index');
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

}
