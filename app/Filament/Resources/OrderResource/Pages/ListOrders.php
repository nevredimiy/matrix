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
                    $inactiveStatuses = [5, 14, 0, 7];

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
                                'image' => 'https://dinara.david-freedman.com.ua/image/' . $product->product->image,
                                'name' => $product->name,
                                'stock_quantity' => $product->product->quantity,
                                'order_date' => $order->date_added,
                                'created_at' => now(),
                                'updated_at' => now()
                            ];

                            if (in_array($order['order_id'], $existingOrderIds)) {
                                $ordersForUpdate[] = $data;
                            } else {
                                $ordersForSave[] = $data;
                            }
                        }
                    }

                    // dd($ordersForUpdate);

                    if (!empty($ordersForSave)) {
                        DB::table('orders')->insert($ordersForSave);
                    }

                    foreach ($ordersForUpdate as $updateData) {
                        DB::table('orders')
                            ->where('order_number', $updateData['order_number'])
                            ->where('product_sku', $updateData['product_sku']) // уточнение
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

                    [$createdProducts, $updatedProducts] = $this->syncProductsFromOc();
                    $ordersForSaveCount = count($ordersForSave);
                    $ordersForUpdateCount = count($ordersForUpdate);
                    $archivedOrdersCount = count($archivedOrders);

                    // Сообщение об успехе
                    Notification::make()
                        ->title('Оновлення завершено')
                        ->body("Додано: {$ordersForSaveCount},\nоновлено: {$ordersForUpdateCount},\nвидалено: {$archivedOrdersCount},\nДодано продуктів: {$createdProducts},\nОновлено продуктів: {$updatedProducts}")
                        ->success()
                        ->send();
                }),

            Actions\CreateAction::make(),
        ];
    }
}
