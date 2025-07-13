<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use App\Models\OcOrder;
use App\Models\OcProduct;
use App\Models\Order;
use App\Models\OrderProduct;
use App\Models\OrderStatus;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Arr;


class ListOrders extends ListRecords
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('update_orders_oc')
                ->label('Оновити замовлення з OC')
                ->color('success')
                ->requiresConfirmation()
                ->icon('heroicon-o-arrow-down-tray')
                ->action(function () {

                    // Неактивні статуси
                    $inactiveStatuses = OrderStatus::where('store_id', 1) // 1 - ocstore
                        ->where('is_active', 0)
                        ->pluck('identifier')                        
                        ->toArray();

                    // Добавить статус 0  - это незавершенные заказы, их мы не учитываем
                    if(!in_array(0, $inactiveStatuses)){
                        array_push($inactiveStatuses, 0);
                    }

                    // Номери вже існуючих замовлень
                    $existingOrderNumbers = Order::where('store_id', 1)
                        ->pluck('order_number')
                        ->toArray();

                    // Архівування/видалення
                    $archivedRows = $this->updateOrderStatuses($inactiveStatuses, $existingOrderNumbers);

                    // Синхронізація замовлень
                    ['new_numbers' => $newNumbers, 'all_numbers' => $allNumbers] =
                        $this->updateOrders($inactiveStatuses, $existingOrderNumbers);

                    $insertedCount = count($newNumbers);
                    $updatedCount = count($allNumbers) - $insertedCount;

                    // Підрахунок
                    $archivedCount      = is_countable($archivedRows)      ? count($archivedRows)      : 0;
                   

                    // Повідомлення
                    Notification::make()
                        ->title('Оновлення завершено')
                        ->body("Додано: {$insertedCount},\nоновлено: {$updatedCount},\nвидалено: {$archivedCount}")
                        ->success()
                        ->send();
                }),
                Action::make('update_orders_hor')
                    ->label('Оновити замовлення з Hor')
                    ->color('info')
                    ->requiresConfirmation()
                    ->icon('heroicon-o-arrow-down-tray')
                    ->action(function () {
                        $result = $this->updateOrdersFromHoroshop();
                        
                        Notification::make()
                            ->title('Оновлення завершено')
                            ->body("Додано {$result['created']} замовлень, оновлено {$result['updated']} замовлень.")
                            ->success()
                            ->send();
                    }),

                Actions\CreateAction::make(),
        ];
    }

    public function updateOrderStatuses(array $inactiveStatuses, array $existingOrderNumbers): array
    {

        // Заказы из OpenCart, у которых статус в списке неактивных
        $ocOrdersToArchive = OcOrder::whereIn('order_id', $existingOrderNumbers)
            ->whereIn('order_status_id', $inactiveStatuses)
            ->where('language_id', 5)
            ->get();

        if ($ocOrdersToArchive->isEmpty()) {
            return []; // нечего архивировать
        }

        // Наши заказы + товары
        $orders = Order::with(['orderProducts.product', 'orderStatus'])
            ->where('store_id', 1)
            ->whereIn('order_number', $ocOrdersToArchive->pluck('order_id'))
            ->get();

        // Данные для arhived_orders
        $archiveRows = $orders->map(function (Order $order) {
            $skues = $order->orderProducts
                ->map(fn($op) => $op->product->sku)
                ->filter()
                ->implode(',');

            return [
                'order_number' => $order->order_number,
                'store_id'     => $order->store_id,
                'status_order'       => $order->orderStatus?->name ?? 'архів',
                'product_skues' => $skues,           // убедись, что колонка TEXT
                'created_at'   => now(),
                'updated_at'   => now(),
            ];
        })->all();

        // Сохраняем и удаляем в одной транзакции
        DB::transaction(function () use ($archiveRows, $ocOrdersToArchive) {

            // insertOrIgnore → если метод вызовется повторно, дубликаты игнорируются
            DB::table('arhived_orders')->insertOrIgnore($archiveRows);

            Order::where('store_id', 1)
                ->whereIn('order_number', $ocOrdersToArchive->pluck('order_id'))
                ->delete();
        });

        return $archiveRows;
    }

    // Добавление и обновление заказов


    public function updateOrders(array $inactiveStatuses, array $existingOrderNumbers): array
    {

        $existingSet = collect($existingOrderNumbers)->flip(); // ['29101' => 0, …]
        $newNumbers = [];  // список order_number, которых не было ранее

        // Все OC‑ID активных продуктов
        $activeOcProductIds = Product::where('is_active', 1)
            ->pluck('product_id_oc')
            ->toArray();

        // Заказы OpenCart, где есть активные товары и статус ≠ архив
        $ocOrders = OcOrder::with('products')
            ->whereHas('products', fn($q) => $q->whereIn('product_id', $activeOcProductIds))
            ->whereNotIn('order_status_id', $inactiveStatuses)
            ->get();

        // Буферы
        $ordersForInsert  = [];
        $ordersForUpdate  = [];
        $rawOrderItems    = []; // временно храним товары по order_number

        foreach ($ocOrders as $ocOrder) {

            $orderNumber = $ocOrder->order_id;
            $isExisting = $existingSet->has($orderNumber);

            $orderPayload = [
                'order_number'            => $ocOrder->order_id,
                'store_id'                => 1,
                'status'                  => 'відкритий',
                'order_status_identifier' => $ocOrder->order_status_id,
                'order_date'              => $ocOrder->date_added,
                'updated_at'              => now(),
            ];

            $isExisting = in_array($ocOrder->order_id, $existingOrderNumbers, true);

            if ($isExisting) {
                $ordersForUpdate[] = $orderPayload + ['created_at' => now()];
            } else {
                $ordersForInsert[] = $orderPayload + ['created_at' => now()];
            }

            // --- товары этого заказа ---
            foreach ($ocOrder->products as $ocProduct) {

                $localProductId = Product::where('product_id_oc', $ocProduct->product_id)->value('id');
                if (!$localProductId) {
                    continue; // продукт ещё не завели
                }

                $rawOrderItems[$ocOrder->order_id][] = [
                    'product_id' => $localProductId,
                    'quantity'   => $ocProduct->quantity,
                ];
            }
        }

        // --- Транзакция ---
        DB::transaction(function () use (&$ordersForInsert, &$ordersForUpdate, &$rawOrderItems) {

            // Валидация статусов
            $validStatusIds = OrderStatus::pluck('identifier')->toArray();

            $ordersForInsert = collect($ordersForInsert)
                ->filter(fn($o) => in_array($o['order_status_identifier'], $validStatusIds))
                ->values()
                ->all();

            // Вставка / обновление заказов
            if ($ordersForInsert) {
                DB::table('orders')->upsert(
                    $ordersForInsert,
                    ['order_number', 'store_id'],
                    ['order_status_identifier', 'order_date', 'updated_at']
                );
            }


            if ($ordersForUpdate) {
                DB::table('orders')->upsert(
                    $ordersForUpdate,
                    ['order_number', 'store_id'],
                    ['order_status_identifier', 'order_date', 'updated_at']
                );
            }

            // Получаем map order_number → id (уже после вставки!)
            $orderIdMap = Order::where('store_id', 1)
                ->whereIn('order_number', array_keys($rawOrderItems))
                ->pluck('id', 'order_number')
                ->toArray();

            // Готовим массивы insert/upsert для товаров
            $orderProductsInsert = [];
            $orderProductsUpsert = [];

            foreach ($rawOrderItems as $orderNumber => $products) {

                $orderId = $orderIdMap[$orderNumber] ?? null;
                if (!$orderId) {
                    continue; // на всякий случай
                }

                $currentProductIds = [];

                foreach ($products as $item) {
                    $payload = [
                        'order_id'   => $orderId,
                        'product_id' => $item['product_id'],
                        'quantity'   => $item['quantity'],
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];

                    $currentProductIds[] = $item['product_id'];

                    // определяем новый/старый заказ через existing map
                    if (in_array($orderNumber, $ordersForUpdate ? collect($ordersForUpdate)->pluck('order_number')->all() : [], true)) {
                        $orderProductsUpsert[] = $payload;
                    } else {
                        $orderProductsInsert[] = $payload;
                    }
                }

                // Удаляем «лишние» товары у существующих заказов
                if (in_array($orderNumber, $ordersForUpdate ? collect($ordersForUpdate)->pluck('order_number')->all() : [], true)) {
                    DB::table('order_products')
                        ->where('order_id', $orderId)
                        ->whereNotIn('product_id', $currentProductIds)
                        ->delete();
                }
            }

            // Сохраняем товары
            if ($orderProductsInsert) {
                DB::table('order_products')->insert($orderProductsInsert);
            }

            if ($orderProductsUpsert) {
                DB::table('order_products')->upsert(
                    $orderProductsUpsert,
                    ['order_id', 'product_id'],
                    ['quantity', 'updated_at']
                );
            }
        });


        return [
            'new_numbers'  => $newNumbers,
            'all_numbers'  => collect($ocOrders)->pluck('order_id')->all()
        ];
    }

    public function updateOrdersFromHoroshop(): array
    {
        return DB::transaction(function () {

            $createdCount = 0;
            $updatedCount = 0;

            // 1. Получаем справочники
            $activeStatuses = OrderStatus::where('store_id', 2)
                ->where('is_active', 1)
                ->pluck('identifier')
                ->all();

            $activeSkus = Product::where('is_active', 1)
                ->pluck('sku')
                ->all();

            $productsMap = Product::whereIn('sku', $activeSkus)
                ->get(['id', 'sku'])
                ->keyBy('sku');

            $existingOrders = Order::where('store_id', 2)
                ->pluck('id', 'order_number'); // ['HS123' => 1]

            // 2. Получаем заказы
            $response = app(\App\Services\HoroshopApiService::class)->call(
                'orders/get',
                ['status' => $activeStatuses]
            );
            $remoteOrders = $response['response']['orders'] ?? [];

            if (empty($remoteOrders)) {
                return ['created' => 0, 'updated' => 0];
            }

            $ordersToUpsert = [];
            $pivotToUpsert = [];

            foreach ($remoteOrders as $order) {
                $allowedProducts = collect($order['products'] ?? [])
                    ->filter(fn ($p) => in_array($p['article'], $activeSkus))
                    ->all();

                if (empty($allowedProducts)) {
                    continue;
                }

                $isNew = !isset($existingOrders[$order['order_id']]);

                $ordersToUpsert[] = [
                    'order_number'            => $order['order_id'],
                    'store_id'                => 2,
                    'status'                  => 'відкритий',
                    'order_status_identifier' => $order['stat_status'],
                    'order_date'              => $order['stat_created'],
                    'updated_at'              => now(),
                    'created_at'              => now(),
                ];

                if ($isNew) {
                    $createdCount++;
                } else {
                    $updatedCount++;
                }

                foreach ($allowedProducts as $product) {
                    $pivotToUpsert[] = [
                        'order_number' => $order['order_id'],
                        'product_id'   => $productsMap[$product['article']]->id ?? null,
                        'quantity'     => $product['quantity'],
                        'updated_at'   => now(),
                        'created_at'   => now(),
                    ];
                }
            }

            if (!empty($ordersToUpsert)) {
                Order::upsert(
                    $ordersToUpsert,
                    ['order_number'],
                    ['status', 'order_status_identifier', 'order_date', 'updated_at']
                );

                $orderIds = Order::where('store_id', 2)
                    ->whereIn('order_number', Arr::pluck($ordersToUpsert, 'order_number'))
                    ->pluck('id', 'order_number');

                foreach ($pivotToUpsert as &$row) {
                    $row['order_id'] = $orderIds[$row['order_number']] ?? null;
                    unset($row['order_number']);
                }

                OrderProduct::upsert(
                    $pivotToUpsert,
                    ['order_id', 'product_id'],
                    ['quantity', 'updated_at']
                );
            }

            return [
                'created' => $createdCount,
                'updated' => $updatedCount,
            ];
        });
    }

}
