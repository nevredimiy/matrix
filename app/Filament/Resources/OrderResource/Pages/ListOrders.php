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
            Action::make('update_orders')
                ->label('Оновити замовлення з OC')
                ->color('success')
                ->requiresConfirmation()
                ->icon('heroicon-o-arrow-down-tray')
                ->action(function () {
                    $ocOrders = OcOrder::whereHas('products.product', function ($query) {
                        $query->whereNotNull('ean')->where('ean', '!=', '');
                    })
                        ->with(['products'])
                        ->whereNotIn('order_status_id', [5, 14]) // исключаем Сделка завершена, Отправлено
                        ->get();

                    // Получаем ID уже существующих заказов из локальной таблицы
                    $existingOrderIds = DB::table('orders')->pluck('order_number')->all();

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
                        ->body('Додано: ' . count($ordersForSave) . ', оновлено: ' . count($ordersForUpdate))
                        ->success()
                        ->send();
                }),
            // Actions\CreateAction::make(),
        ];
    }
}
