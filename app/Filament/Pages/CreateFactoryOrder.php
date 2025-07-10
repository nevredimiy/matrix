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

    public function save()
    {
        // $factoryOrder = \App\Models\FactoryOrder::create([
        //     'factory_id' => 'FO-' . now()->format('YmdHi'),
        //     'status' => 'в процессе',
        // ]);

        foreach ($this->items as $item) {
            $product = Product::where('sku', $item['product_sku'])->first();
            dd($product);

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
}
