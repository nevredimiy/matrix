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
use App\Models\OrderStatus;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

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
                    $inactiveStatuses = OrderStatus::where('is_active', 0)
                        ->pluck('identifier')
                        ->toArray();

                    // Номери вже існуючих замовлень
                    $existingOrderNumbers = Order::where('store_id', 1)
                        ->pluck('order_number')
                        ->toArray();

                    // Архівування/видалення
                    $archivedRows = $this->updateOrderStatuses($inactiveStatuses, $existingOrderNumbers);

                    // Синхронізація замовлень
                    [$ordersForInsert, $ordersForUpdate] = $this->updateOrders($inactiveStatuses, $existingOrderNumbers);

                    // Підрахунок
                    $archivedCount      = is_countable($archivedRows)      ? count($archivedRows)      : 0;
                    $insertedCount      = is_countable($ordersForInsert)   ? count($ordersForInsert)   : 0;
                    $updatedCount       = is_countable($ordersForUpdate)   ? count($ordersForUpdate)   : 0;

                    // Повідомлення
                    Notification::make()
                        ->title('Оновлення завершено')
                        ->body("Додано: {$insertedCount},\nоновлено: {$updatedCount},\nвидалено: {$archivedCount}")
                        ->success();
                }),

            Actions\CreateAction::make(),
        ];
    }

    public function updateOrderStatuses(array $inactiveStatuses, array $existingOrderNumbers): array
    {
        // Заказы из OpenCart, у которых статус в списке неактивных
        $ocOrdersToArchive = OcOrder::whereIn('order_id', $existingOrderNumbers)
            ->whereIn('order_status_id', $inactiveStatuses) 
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
            $skus = $order->orderProducts
                ->map(fn ($op) => $op->product->sku)
                ->filter()
                ->implode(',');

            return [
                'order_number' => $order->order_number,
                'store_id'     => $order->store_id,
                'status'       => $order->orderStatus?->name ?? 'архів',
                'product_skus' => $skus,           // убедись, что колонка TEXT
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
        // 1. OC‑ID всех активных продуктов
        $activeOcProductIds = Product::where('is_active', 1)
            ->pluck('product_id_oc')
            ->toArray();

        // 2. Заказы OC, где есть активные товары и статус ≠ архив
        $ocOrders = OcOrder::with('products')
            ->whereHas('products', fn ($q) => $q->whereIn('product_id', $activeOcProductIds))
            ->whereNotIn('order_status_id', $inactiveStatuses)
            ->get();

        // Буферы bulk‑операций
        $ordersForInsert  = [];
        $ordersForUpdate  = [];
        $orderProductsInsert = [];   // для новых заказов
        $orderProductsUpsert = [];   // для существующих заказов

        foreach ($ocOrders as $ocOrder) {

            // Данные заказа
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
                // Обновим сам заказ
                $ordersForUpdate[] = $orderPayload + ['created_at' => now()]; // created_at не изменится, но upsert требует
            } else {
                // Сохраним новый
                $ordersForInsert[] = $orderPayload + ['created_at' => now()];
            }

            // --- Синхронизация товаров заказа ---
            $currentProductIds = [];

            foreach ($ocOrder->products as $ocProduct) {
                // локальный ID продукта
                $localProductId = Product::where('product_id_oc', $ocProduct->product_id)->value('id');
                if (!$localProductId) {
                    continue; // продукт ещё не завели
                }

                $itemPayload = [
                    'order_number' => $ocOrder->order_id, 
                    'product_id'   => $localProductId,
                    'quantity'     => $ocProduct->quantity,
                    'updated_at'   => now(),
                    'created_at'   => now(),
                ];

                $currentProductIds[] = $localProductId;

                // если заказ новый → просто insert
                if ($isExisting) {
                    $orderProductsUpsert[] = $itemPayload;
                } else {
                    $orderProductsInsert[] = $itemPayload;
                }
            }

            // Если заказ существующий — удалим из order_products лишние позиции
            if ($isExisting && $currentProductIds) {
                DB::table('order_products')
                    ->where('order_number', $ocOrder->order_id)
                    ->whereNotIn('product_id', $currentProductIds)
                    ->delete();
            }
        }

        // --- Выполняем всё одной транзакцией ---
        DB::transaction(function () use (
            $ordersForInsert,
            $ordersForUpdate,
            $orderProductsInsert,
            $orderProductsUpsert
        ) {
            // Новые заказы
            if ($ordersForInsert) {
                DB::table('orders')->insert($ordersForInsert);
            }

            // Обновление заказов
            if ($ordersForUpdate) {
                DB::table('orders')->upsert(
                    $ordersForUpdate,
                    ['order_number', 'store_id'],            
                    ['status', 'order_status_identifier', 'order_date', 'updated_at']
                );
            }

            // Товары новых заказов
            if ($orderProductsInsert) {
                DB::table('order_products')->insert($orderProductsInsert);
            }

            // Товары существующих заказов (upsert по составному ключу)
            if ($orderProductsUpsert) {
                DB::table('order_products')->upsert(
                    $orderProductsUpsert,
                    ['order_number', 'product_id'],          // уникальный ключ
                    ['quantity', 'updated_at']
                );
            }
        });

        return [$ordersForInsert, $ordersForUpdate];
    }
}
