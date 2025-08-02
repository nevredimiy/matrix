<?php

namespace App\Filament\Pages;

use App\Models\Factory;
use Filament\Pages\Page;
use App\Models\Order;
use App\Models\Product;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;
use App\Models\FactoryOrder;
use App\Models\Setting;

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

    public $orders = null;

    public $itemsByOrderId = [];

    public function mount()
    {
        $orderIds = session('selected_order_ids', []);

        $this->orders = Order::whereIn('id', $orderIds)
            ->with(['orderProducts.product'])
            ->get();

        foreach ($this->orders as $order) {
            foreach ($order->orderProducts as $op) {
                $this->itemsByOrderId[$order->id][] = [
                    'product_id' => $op->product->id,
                    'product' => $op->product,
                    'factory_id' => null,
                    'quantity' => $op->quantity,
                    'required_quantity' => $op->product->desired_stock_quantity + $op->quantity - $op->product->stock_quantity < 0 ? 0 : $op->product->desired_stock_quantity + $op->quantity - $op->product->stock_quantity,
                    // 'factory_id' => $this->destributionItems();
                ];
            }
        }

        $this->destributionItems();

        // dump($this->orders);
        // dd($this->itemsByOrderId);

        $this->factories = Factory::pluck('name', 'id')->toArray();
    }
    
    //  Это нужно для нового товара, что бы подятгивались данные
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

        dd($this->items);
        // 1. Группируем товары по фабрикам
        $groups = collect($this->items)->groupBy('factory_id');
  
        try {
            // 2. Гарантируем целостность данных
            DB::transaction(function () use ($groups) {
                $allOrdersText = '';
                foreach ($groups as $factoryId => $items) {

                    /** @var FactoryOrder $factoryOrder */
                    $factoryOrder = FactoryOrder::create([
                        'factory_id' => $factoryId,
                        'status'     => 'в процессе',
                    ]);

                    // 3. Заполняем строки заказа
                    foreach ($items as $item) {

                        $allOrdersText .= $item['text_order_ids'];
                        // вытаскиваем id товара по SKU (value() быстрее first()->id)
                        $productId = Product::where('sku', $item['product_sku'])->value('id');

                        if ($productId) {
                            $factoryOrder->items()->create([
                                'product_id'        => $productId,
                                'quantity_ordered'  => $item['required_quantity'],
                                // если есть, можете сразу добавить required_quantity, quantity_delivered и т.д.
                            ]);
                        }
                    }
                }

                $allOrders = array_filter(array_map('trim', explode(',', $allOrdersText)));

                Order::whereIn('order_number', $allOrders)->update(['status' => 'in_progress']);
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

    public function addEmptyItem($orderId)
    {
        $this->itemsByOrderId[$orderId][] = [
            'product_id' => null,
            'product' => null,
            'factory_id' => null,
            'required_quantity' => 1,
        ];
    }



    public function removeItem($orderId, $index)
    {
        unset($this->itemsByOrderId[$orderId][$index]);
        $this->itemsByOrderId[$orderId] = array_values($this->itemsByOrderId[$orderId]); // пересобрать индексы
    }



    public function getItems($orders)
    {
        $itemsGrouped = []; 

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
                        'desired_stock_quantity' => $product->desired_stock_quantity ?? 0,
                        'order_ids'      => [],
                        'factory_id'       => 1
                    ];
                }

                // накапливаем
                $itemsGrouped[$sku]['quantity']     += $op->quantity;
                $itemsGrouped[$sku]['order_ids'][]   = $order->order_number;
            }
        }

        $items = collect($itemsGrouped)
            ->map(function ($row) {
                $row['text_order_ids'] = implode(',', $row['order_ids']);
                unset($row['order_ids']);

                $requiredQuantity = $row['desired_stock_quantity'] + $row['quantity'] - $row['stock_quantity'] > 0 ? $row['desired_stock_quantity'] + $row['quantity'] - $row['stock_quantity'] : 0;
                $row['required_quantity'] = $requiredQuantity;

                return $row;
            })
            ->values()
            ->toArray();

        return $items;
    }

    public function destributionItems()
    {
        $maxDays = (int) Setting::get('max_days_per_form', 7); // по умолчанию 7, если не задано

        dd($this->itemsByOrderId);

        foreach ($this->itemsByOrderId as $order) {
            foreach($order['products'] as $product)
                $product = Product::where('sku', $item['product_sku'])->with(['factoryModelCount', 'factoryOrderItem'])->first();

                $f1_model_count = $product->factoryModelCount?->factory1_model_count;
                $manufactureDays = $f1_model_count ? $item['required_quantity'] / $f1_model_count : 0;

            if ($manufactureDays <= $maxDays) {
                $item['factory_id'] = 1;
            } else {
                $item['factory_id'] = 2;
            }
        }
    }

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->hasRole('admin'); // только для админа
    }
   


}
