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

                    // Берём **НЕактивные** статусы OpenCart, сохранённые в нашей БД
                    $inactiveStatuses = OrderStatus::where('is_active', 0)->pluck('identifier')->toArray();                    
        
                    // Все номера заказов, уже присутствующие в таблице orders (магазин = ocStore)
                    $existingOrderIds = Order::where('store_id', 1) // 1 - ocstore
                        ->pluck('order_number')
                        ->all();

                    $archiveRows = $this->updateOrderStatuses($inactiveStatuses, $existingOrderIds);
                    $this->updateOrders($inactiveStatuses, $existingOrderIds);


                    // Сообщение об успехе
                    Notification::make()
                        ->title('Оновлення завершено')
                        // ->body("Додано: {$ordersForSaveCount},\nоновлено: {$ordersForUpdateCount},\nвидалено: {$archiveRows}")
                        ->body("Додано: {видалено: {$archiveRows}")
                        ->success()
                        ->send();
                }),

            Actions\CreateAction::make(),
        ];
    }

    public function updateOrderStatuses($inactiveStatuses, $existingOrderIds): array
    {
       
        // На стороне OpenCart ищем те же заказы, у которых статус не включен
        $ocOrdersToArchive = OcOrder::whereIn('order_id', $existingOrderIds)
            ->whereIn('order_status_id',  $inactiveStatuses)
            ->get();
        
        if ($ocOrdersToArchive->isEmpty()) {
            return array($ocOrdersToArchive); // Архивировать нечего
        }

        // Забираем сразу все нужные заказы с их товарами
        $orders = Order::with(['orderProducts.product', 'orderStatus'])   // product → relation, чтобы достать sku
            ->where('store_id', 1)
            ->whereIn('order_number', $ocOrdersToArchive->pluck('order_id'))
            ->get();

        // Готовим bulk‑insert в archived_orders
        $archiveRows = $orders->map(function (Order $order) {
            // Все SKU через запятую
            $skus = $order->orderProducts
                ->map(fn ($op) => $op->product->sku ?? null)
                ->filter()
                ->implode(',');

            return [
                'order_number'  => $order->order_number,
                'store_id'      => $order->store_id,
                'status'        => $order->orderStatus?->name,          // или передавай реальный текст статуса
                'product_skus'  => $skus,
                'created_at'    => now(),
                'updated_at'    => now(),
            ];
        })->all();

        // Сохраняем и удаляем заказы «одним махом»
        DB::table('archived_orders')->insert($archiveRows);

        Order::where('store_id', 1)
            ->whereIn('order_number', $ocOrdersToArchive->pluck('order_id'))
            ->delete();

        return $archiveRows;

    }

    // Добавление и обновление заказов
    public function updateOrders($inactiveStatuses, $existingOrderIds)
    {

        $activeProducts = Product::where('is_active', 1)->pluck('sku')->toArray();

        $ocOrders = OcOrder::whereHas('products.product', function ($query) use ($activeProducts) {
            $query->whereIn('model', $activeProducts);
        })
            ->with(['products'])
            ->whereNotIn('order_status_id', $inactiveStatuses)
            ->get();
        
        $ordersForSave = [];
        $ordersForUpdate = [];

        $dataOrder= [];
        $dataOrderProduct = [];
        
        
        
        foreach ($ocOrders as $order) {
            
            $dataOrder = [
                'order_number' => $order->order_id,
                'store_id' =>  1, // OcStore
                'status'  =>  'відкритий',
                'order_status_identifier' => $order->order_status_id,
                // 'image' => 'https://dinara.david-freedman.com.ua/image/' . $product->product->image,
                // 'name' => $product->name,
                // 'stock_quantity' => $product->product->quantity,
                'order_date' => $order->date_added,
                'created_at' => now(),
                'updated_at' => now()
            ];

            foreach ($order->products as $product) {
                $dataOrderProduct = [
                    'sku' => $product->model,
                    'quantity' => $product->quantity,
                    'product_id_oc' => $product->id,
                    
                    // 'order_id' => '',
                    // 'product_id' => ,
                    // 'quantity' => 
                ];

            }
            
            // if (in_array($order['order_id'], $existingOrderIds)) {
            //     $ordersForUpdate[] = $data;
            // } else {
            //     $ordersForSave[] = $data;
            // }
        }

        dump($ocOrders);
        dump($dataOrder);
        dump($dataOrderProduct);
        // dd($ordersForUpdate);

        // if (!empty($ordersForSave)) {
        //     DB::table('orders')->insert($ordersForSave);
        // }

        // foreach ($ordersForUpdate as $updateData) {
        //     DB::table('orders')
        //         ->where('order_number', $updateData['order_number'])
        //         ->where('product_sku', $updateData['product_sku']) // уточнение
        //         ->update([
        //             'quantity' => $updateData['quantity'],
        //             'store_id' => $updateData['store_id'],
        //             'status' => $updateData['status'],
        //             'image' => $updateData['image'],
        //             'name' => $updateData['name'],
        //             'stock_quantity' => $updateData['stock_quantity'],
        //             'order_date' => $updateData['order_date'],
        //             'updated_at' => now(),
        //         ]);
        // }

        // [$createdProducts, $updatedProducts] = $this->syncProductsFromOc();
        // $ordersForSaveCount = count($ordersForSave);
        // $ordersForUpdateCount = count($ordersForUpdate);
        // $archivedOrdersCount = count($archivedOrders);
    }
}
