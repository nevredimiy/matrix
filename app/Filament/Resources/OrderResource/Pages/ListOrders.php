<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Resources\Pages\ListRecords;
use Filament\Notifications\Notification;
use App\Models\OcOrder;
use App\Models\OcProduct;
use App\Models\Order;
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
                ->action(fn () => $this->updateOrdersFromOc()),


            Action::make('update_orders_hor')
                ->label('Оновити замовлення з Хорошоп')
                ->color('info')
                ->requiresConfirmation()
                ->icon('heroicon-o-arrow-down-tray')
                ->action(function () {

                    // $response = app(\App\Services\HoroshopApiService::class)->call('catalog/export', [
                    //     'expr' => [
                    //         'article' => ['HM-071'], // или 'article' => 'HM-155'
                    //     ],
                    //     'limit' => 5,
                    // ]);

                    // $filtered = collect($response['response']['products'] ?? [])
                    //     ->filter(fn($product) => trim($product['article']) === 'HM-155');

                    // dd($filtered->values()->all());

                // Проверка на актуальность заказов.
                    $inactiveStatuses = [3, 4, 5, 6, 7, 8];

                    $existingOrderNumbers = DB::table('orders')
                        ->where('store_id', 2)
                        ->pluck('order_number')
                        ->all();

                    $response = app(\App\Services\HoroshopApiService::class)->call('order/get', [
                        'ids' => $existingOrderNumbers,
                        'status' => $inactiveStatuses,
                    ]);

                    $arhivedOrders = $response['response']['orders'] ?? [];

                    foreach ($arhivedOrders as $archived) {
                        $orderItems = DB::table('orders')
                            ->where('order_number', $archived['order_id'])
                            ->where('store_id', 2)
                            ->get();

                        foreach ($orderItems as $item) {
                            DB::table('arhived_orders')->insert([
                                'order_number' => $item->order_number,
                                'product_sku' => $item->product_sku,
                                'quantity' => $item->quantity,
                                'store_id' => $item->store_id,
                                'status' => 'архів',
                                'created_at' => now(),
                                'updated_at' => now()
                            ]);
                        }

                        DB::table('orders')
                            ->where('order_number', $archived['order_id'])
                            ->where('store_id', 2)
                            ->delete();
                    }
                // Конец проверки на актуальность

                // Обновляем и заполняем заказами
                    $allowedSkus = ['HM-155', 'HM-102', 'HM-070', 'HM-071'];

                    $response = app(\App\Services\HoroshopApiService::class)->getOrders([
                        'status' => [1, 2], // 1 - Новый; 2 - В обработке
                        'limit' => 10
                    ]); // - 1 — новый; 2 — в обработке

                    $orders = $response['response']['orders'] ?? [];

                    // dd($orders);
                    $filteredOrders = array_filter($orders, function ($order) use ($allowedSkus) {
                        foreach ($order['products'] as $product) {
                            if (in_array($product['article'], $allowedSkus)) {
                                return true;
                            }
                        }
                        return false;
                    });

                    $ordersForSave = [];
                    $ordersForUpdate = [];

                    foreach ($filteredOrders as $order) {
                        foreach ($order['products'] as $product) {
                            if (!in_array($product['article'], $allowedSkus)) {
                                continue;
                            }

                            $ocProduct = OcProduct::where('model',  $product['article'])->first();                            
                            
                            if (empty($ocProduct)) {
                                $response = app(\App\Services\HoroshopApiService::class)->call('catalog/export', [
                                    'expr' => [
                                        'article' => $product['article'], // или 'article' => 'HM-155'
                                    ],
                                    'limit' => 1,
                                ]);
                                $horProduct = $response['response']['products'][0];

                                $stock_quantity = $horProduct['quantity'] ?? 0;
                                $image = $horProduct['gallery_common'][0] ?? '';
                            } else {
                                $stock_quantity = $ocProduct->quantity ?? 0;
                                $image = isset($ocProduct->image) ? 'https://dinara.david-freedman.com.ua/image/' . $ocProduct->image : '';
                            }


                            $data = [
                                'order_number' => $order['order_id'],
                                'product_sku' => $product['article'],
                                'quantity' => $product['quantity'],
                                'store_id' => 2, // Хорошоп
                                'status' => 'відкритий',
                                'image' =>  $image,
                                'name' => $product['title'],
                                'stock_quantity' => $stock_quantity,
                                'order_date' => $order['stat_created'],
                                'created_at' => now(),
                                'updated_at' => now()
                            ];

                            if (in_array($order['order_id'], $existingOrderNumbers)) {
                                $ordersForUpdate[] = $data;
                            } else {
                                $ordersForSave[] = $data;
                            }
                        }
                    }

                    if (!empty($ordersForSave)) {
                        DB::table('orders')->insert($ordersForSave);
                    }

                    foreach ($ordersForUpdate as $updateData) {
                        DB::table('orders')
                            ->where('order_number', $updateData['order_number'])
                            ->where('product_sku', $updateData['product_sku'])
                            ->update([
                                'quantity' => $updateData['quantity'],
                                'store_id' => $updateData['store_id'],
                                'status' => $updateData['status'],
                                'image' => $updateData['image'],
                                'name' => $updateData['name'],
                                'stock_quantity' => $updateData['stock_quantity'],
                                'order_date' => $updateData['order_date'],
                                'updated_at' => now()
                            ]);
                    }

                    Notification::make()
                        ->title('Оновлення з Хорошоп завершено')
                        ->body('Додано: ' . count($ordersForSave) . ', оновлено: ' . count($ordersForUpdate) . ', видалено ' . count($arhivedOrders))
                        ->success()
                        ->send();
                })



            // Actions\CreateAction::make(),
        ];
    }

    protected function updateOrdersFromOc()
    {
        $inactiveStatuses = [5, 14, 0];

        $archivedCount = $this->archiveClosedOrders($inactiveStatuses);
        [$createdCount, $updatedCount] = $this->syncOpenOrders($inactiveStatuses);

        Notification::make()
            ->title("Оновлення завершено")
            ->body("➕ Додано: {$createdCount}\n✏️ Оновлено: {$updatedCount}\n🗃️ Архівовано: {$archivedCount}")
            ->success()
            ->send();
    }

    protected function archiveClosedOrders(array $inactiveStatuses): void
    {
        $existingOrderNumbers = DB::table('orders')
            ->where('store_id', 1)
            ->pluck('order_number')
            ->all();

        $archivedOrders = OcOrder::whereIn('order_id', $existingOrderNumbers)
            ->whereIn('order_status_id', $inactiveStatuses)
            ->get();

        foreach ($archivedOrders as $archived) {
            $orderItems = DB::table('orders')
                ->where('order_number', $archived->order_id)
                ->where('store_id', 1)
                ->get();

            foreach ($orderItems as $item) {
                DB::table('arhived_orders')->insert([
                    'order_number' => $item->order_number,
                    'product_sku' => $item->product_sku,
                    'quantity' => $item->quantity,
                    'store_id' => $item->store_id,
                    'status' => 'архів',
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }

            DB::table('orders')
                ->where('order_number', $archived->order_id)
                ->where('store_id', 1)
                ->delete();
        }
    }

    protected function syncOpenOrders(array $inactiveStatuses): void
    {
        $ocOrders = OcOrder::whereHas('products.product', fn ($query) =>
            $query->whereNotNull('ean')->where('ean', '!=', '')
        )
            ->with(['products'])
            ->whereNotIn('order_status_id', $inactiveStatuses)
            ->get();

        $existingOrderIds = DB::table('orders')
            ->where('store_id', 1)
            ->pluck('order_number')
            ->all();

        $ordersForSave = [];
        $ordersForUpdate = [];

        foreach ($ocOrders as $order) {
            foreach ($order->products as $product) {
                $data = [
                    'order_number' => $order->order_id,
                    'product_sku' => $product->model,
                    'quantity' => $product->quantity,
                    'store_id' => 1,
                    'status' => 'відкритий',
                    'image' => 'https://dinara.david-freedman.com.ua/image/' . $product->product->image,
                    'name' => $product->name,
                    'stock_quantity' => $product->product->quantity,
                    'order_date' => $order->date_added,
                    'created_at' => now(),
                    'updated_at' => now()
                ];

                if (in_array($order->order_id, $existingOrderIds)) {
                    $ordersForUpdate[] = $data;
                } else {
                    $ordersForSave[] = $data;
                }
            }
        }

        if (!empty($ordersForSave)) {
            DB::table('orders')->insert($ordersForSave);
        }

        foreach ($ordersForUpdate as $updateData) {
            DB::table('orders')
                ->where('order_number', $updateData['order_number'])
                ->where('product_sku', $updateData['product_sku'])
                ->update([
                    'quantity' => $updateData['quantity'],
                    'store_id' => $updateData['store_id'],
                    'status' => $updateData['status'],
                    'image' => $updateData['image'],
                    'name' => $updateData['name'],
                    'stock_quantity' => $updateData['stock_quantity'],
                    'order_date' => $updateData['order_date'],
                    'updated_at' => now(),
                ]);
        }
    }

}
