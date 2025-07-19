<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Models\Order;
use App\Models\Product;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;
use App\Models\FactoryOrder;

class CreateFactoryOrder extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.create-factory-order';

    protected static ?string $navigationLabel = 'Створити замовлення на виробницство';

    protected static ?int $navigationSort = 10;

    public $items = [];
    public $factoryId = null;
    public $factories = [];
    public $products = [];


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
                        'factory_id'       => 1
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

                $requiredQuantity = $row['quantity'] - $row['stock_quantity'] > 0 ? $row['quantity'] - $row['stock_quantity'] : 0;
                $row['required_quantity'] = $requiredQuantity;

                return $row;
            })
            ->values()
            ->toArray();

        $this->factories = \App\Models\Factory::pluck('name', 'id')->toArray();
        $this->products = \App\Models\Product::pluck('sku')->toArray();
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

        // 1. Группируем товары по фабрикам
        $groups = collect($this->items)->groupBy('factory_id');

        try {
            // 2. Гарантируем целостность данных
            DB::transaction(function () use ($groups) {

                foreach ($groups as $factoryId => $items) {

                    /** @var FactoryOrder $factoryOrder */
                    $factoryOrder = FactoryOrder::create([
                        'factory_id' => $factoryId,
                        'status'     => 'в процессе',
                    ]);

                    // 3. Заполняем строки заказа
                    foreach ($items as $item) {
                        // вытаскиваем id товара по SKU (value() быстрее first()->id)
                        $productId = Product::where('sku', $item['product_sku'])->value('id');

                        if ($productId) {
                            $factoryOrder->items()->create([
                                'product_id'        => $productId,
                                'quantity_ordered'  => $item['quantity'],
                                // если есть, можете сразу добавить required_quantity, quantity_delivered и т.д.
                            ]);
                        }
                    }
                }
            });

            Notification::make()
                ->title('Успех')
                ->body('Заказы на производство созданы.')
                ->success()
                ->send();

        } catch (\Throwable $e) {

            Notification::make()
                ->title('Ошибка')
                ->body('Не удалось создать заказы: '.$e->getMessage())
                ->danger()
                ->send();

                return back();   // оставляем пользователя на форме
        }

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
            'factory_id' => 1
        ];
    }

    public function removeItem($index)
    {
        unset($this->items[$index]);

        // Сброс ключей массива (чтобы избежать пропуска индексов)
        $this->items = array_values($this->items);
    }


}
