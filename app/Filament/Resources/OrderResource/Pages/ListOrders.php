<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Resources\Pages\ListRecords;
use Filament\Notifications\Notification;
use App\Models\OcOrder;
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
                ->action(function () {

                    // dd(OcOrder::limit(10)->orderBy('order_id', 'desc')->get());
                    // DB::table('orders')->truncate();
                    // dd();

                    // Удаление заказов если статус Закрыт
                    // $inactiveStatuses = [3, 5, 7, 8, 9, 10, 11, 13, 14];
                    $inactiveStatuses = [5, 14, 0];

                    $existingOrderNumbers = DB::table('orders')
                        ->where('store_id', 1) // 1 - ocstore
                        ->pluck('order_number')
                        ->all();
                    $archivedOrders = OcOrder::whereIn('order_id', $existingOrderNumbers)
                        ->whereIn('order_status_id',  $inactiveStatuses)
                        ->get();

                    if ($archivedOrders->isNotEmpty()) {
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
                                ]);
                            }

                            DB::table('orders')
                                ->where('order_number', $archived->order_id)
                                ->where('store_id', 1)
                                ->delete();
                        }
                    }

                    // Добавление и обновление заказов
                    $ocOrders = OcOrder::whereHas('products.product', function ($query) {
                        $query->whereNotNull('ean')->where('ean', '!=', '');
                    })
                        ->with(['products'])
                        ->whereNotIn('order_status_id', $inactiveStatuses) // исключаем Сделка завершена, Отправлено
                        ->get();

                    // Получаем ID уже существующих заказов из локальной таблицы
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
                                'product_sku' =>  $product->model,
                                'quantity'  =>  $product->quantity,
                                'store_id' =>  1, // OcStore
                                'status'  =>  'відкритий',
                            ];

                            if (in_array($order['order_id'], $existingOrderIds)) {
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
                            ->update([
                                'product_sku' => $updateData['product_sku'],
                                'quantity' => $updateData['quantity'],
                                'store_id' => $updateData['store_id'],
                                'status' => $updateData['status'],
                            ]);
                    }

                    // Сообщение об успехе
                    Notification::make()
                        ->title('Оновлення завершено')
                        ->body('Додано: ' . count($ordersForSave) . ', оновлено: ' . count($ordersForUpdate) . ', видалено: ' . count($archivedOrders))
                        ->success()
                        ->send();
                }),


            Action::make('update_orders_hor')
                ->label('Оновити замовлення з Хорошоп')
                ->color('info')
                ->requiresConfirmation()
                ->icon('heroicon-o-arrow-down-tray')
                ->action(function () {

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
                            ]);
                        }

                        DB::table('orders')
                            ->where('order_number', $archived['order_id'])
                            ->where('store_id', 2)
                            ->delete();
                    }

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

                            $data = [
                                'order_number' => $order['order_id'],
                                'product_sku' => $product['article'],
                                'quantity' => $product['quantity'],
                                'store_id' => 2, // Хорошоп
                                'status' => 'відкритий',
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
}
