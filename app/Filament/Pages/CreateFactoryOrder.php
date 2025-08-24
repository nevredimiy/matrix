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

    // Свойства для управления диалогом подтверждения
    public $showOverwriteDialog = false;
    public $conflictingOrders = [];
    public $pendingSave = false;

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
                    'product_sku' => $op->product->sku,
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
        $this->products = \App\Models\Product::pluck('sku')->toArray();
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

    // Обработчик для обновления данных товара в itemsByOrderId
    public function updatedItemsByOrderId($value, $name)
    {
        // $name приходит как "order_id.index.product_sku" или "order_id.index.required_quantity" или "order_id.index.factory_id"
        $parts = explode('.', $name);
        if (count($parts) === 3) {
            $orderId = $parts[0];
            $index = $parts[1];
            $field = $parts[2];

            if ($field === 'product_sku') {
                $sku = $value;

                // Ищем товар по артикулу
                $product = \App\Models\Product::where('sku', $sku)->first();

                if ($product) {
                    // Обновляем данные товара в массиве
                    $this->itemsByOrderId[$orderId][$index]['product'] = $product;
                    $this->itemsByOrderId[$orderId][$index]['product_id'] = $product->id;
                    $this->itemsByOrderId[$orderId][$index]['product_sku'] = $sku;

                    // Убеждаемся, что required_quantity установлено по умолчанию для новых товаров
                    if (!isset($this->itemsByOrderId[$orderId][$index]['required_quantity']) ||
                        $this->itemsByOrderId[$orderId][$index]['required_quantity'] <= 0) {
                        $this->itemsByOrderId[$orderId][$index]['required_quantity'] = 1;
                    }
                } else {
                    // Если товар не найден — очищаем поля
                    $this->itemsByOrderId[$orderId][$index]['product'] = null;
                    $this->itemsByOrderId[$orderId][$index]['product_id'] = null;
                    $this->itemsByOrderId[$orderId][$index]['product_sku'] = '';
                }
            } elseif ($field === 'required_quantity') {
                // Убеждаемся, что required_quantity не может быть меньше 0
                $this->itemsByOrderId[$orderId][$index]['required_quantity'] = max(0, (int)$value);
            } elseif ($field === 'factory_id') {
                // Сохраняем выбранную фабрику
                $this->itemsByOrderId[$orderId][$index]['factory_id'] = $value;
            }
        }
    }

    public function save()
    {
        // Проверяем конфликтующие заказы
        $this->checkConflictingOrders();

        if (!empty($this->conflictingOrders)) {
            // Показываем диалог подтверждения
            $this->showOverwriteDialog = true;
            $this->pendingSave = true;
            return;
        }

        // Если нет конфликтов, выполняем сохранение
        $this->performSave();
    }

    public function checkConflictingOrders()
    {
        $this->conflictingOrders = [];

        foreach ($this->orders as $order) {
            // Проверяем, есть ли уже заказы на производство с таким номером
            $existingOrders = FactoryOrder::where('order_number', $order->order_number)->get();

            if ($existingOrders->isNotEmpty()) {
                $this->conflictingOrders[] = [
                    'order_number' => $order->order_number,
                    'existing_count' => $existingOrders->count(),
                    'factories' => $existingOrders->pluck('factory.name')->join(', ')
                ];
            }
        }
    }

    public function confirmOverwrite()
    {
        $this->showOverwriteDialog = false;
        $this->performSave();
    }

    public function cancelOverwrite()
    {
        $this->showOverwriteDialog = false;
        $this->pendingSave = false;
        $this->conflictingOrders = [];
    }

    public function performSave()
    {
        try {
            // 1. Гарантируем целостность данных
            DB::transaction(function () {
                $processedOrderIds = [];

                // 2. Для каждого исходного заказа создаем заказ на производство
                foreach ($this->orders as $order) {
                    // 3. Группируем товары по фабрикам для текущего заказа
                    // Фильтруем только товары с product_id, factory_id и required_quantity > 0
                    $factoryGroups = collect($this->itemsByOrderId[$order->id] ?? [])
                        ->filter(function ($item) {
                            return $item['product_id'] &&
                                   $item['factory_id'] &&
                                   ($item['required_quantity'] ?? 0) > 0;
                        })
                        ->groupBy('factory_id');

                    // 4. Пропускаем заказ, если нет товаров для производства
                    if ($factoryGroups->isEmpty()) {
                        continue;
                    }

                    // 5. Удаляем существующие заказы на производство для этого номера заказа
                    FactoryOrder::where('order_number', $order->order_number)->delete();

                    // 6. Для каждой фабрики создаем отдельный заказ на производство
                    foreach ($factoryGroups as $factoryId => $items) {
                        /** @var FactoryOrder $factoryOrder */
                        $factoryOrder = FactoryOrder::create([
                            'factory_id' => $factoryId,
                            'order_id' => $order->id,
                            'order_number' => $order->order_number,
                            'status' => 'в процессе',
                        ]);

                        // 7. Заполняем строки заказа товарами для этой фабрики
                        foreach ($items as $item) {
                            $factoryOrder->items()->create([
                                'product_id' => $item['product_id'],
                                'quantity_ordered' => $item['required_quantity'],
                            ]);
                        }
                    }

                    // 8. Отмечаем заказ как обработанный только если были созданы заказы на производство
                    $processedOrderIds[] = $order->id;
                }

                // 9. Обновляем статусы заказов
                if (!empty($processedOrderIds)) {
                    Order::whereIn('id', $processedOrderIds)->update(['status' => 'in_progress']);
                }
            });

            $message = !empty($this->conflictingOrders)
                ? 'Заказы на производство перезаписаны.'
                : 'Заказы на производство созданы.';

            Notification::make()
                ->title('Успех')
                ->body($message)
                ->success()
                ->send();

            // Сбрасываем состояние
            $this->conflictingOrders = [];
            $this->pendingSave = false;

        } catch (\Throwable $e) {
            Notification::make()
                ->title('Ошибка')
                ->body('Не удалось создать заказы: ' . $e->getMessage())
                ->danger()
                ->send();

            return back(); // оставляем пользователя на форме
        }

        return redirect()->route('filament.admin.resources.factory-orders.index');
    }

    public function addEmptyItem($orderId)
    {
        $this->itemsByOrderId[$orderId][] = [
            'product_id' => null,
            'product' => null,
            'product_sku' => '',
            'factory_id' => null,
            'required_quantity' => 1,
            'quantity' => 0,
        ];

        // Запускаем процесс перераспределения фабрик после добавления товара
        $this->destributionItems();
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

        // dd($this->itemsByOrderId);

        foreach ($this->itemsByOrderId as $orderId => $order) {
            foreach($order as $index => $productData) {
                $product = $productData['product'];

                // Проверяем, что товар существует
                if ($product) {
                    $f1_model_count = $product->factoryModelCount?->factory1_model_count;
                    $manufactureDays = $f1_model_count ? $productData['required_quantity'] / $f1_model_count : 0;

                    if ($manufactureDays <= $maxDays) {
                        $this->itemsByOrderId[$orderId][$index]['factory_id'] = 1;
                    } else {
                        $this->itemsByOrderId[$orderId][$index]['factory_id'] = 2;
                    }
                } else {
                    // Если товара нет, устанавливаем фабрику по умолчанию
                    $this->itemsByOrderId[$orderId][$index]['factory_id'] = 1;
                }
            }
        }
    }

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->hasRole('admin'); // только для админа
    }
   


}
