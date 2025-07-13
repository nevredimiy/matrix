<?php

namespace App\Filament\Resources\OrderStatusResource\Pages;

use App\Filament\Resources\OrderStatusResource;
use App\Models\OcOrderStatus;
use App\Models\OrderStatus;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;

class ListOrderStatuses extends ListRecords
{
    protected static string $resource = OrderStatusResource::class;

    protected function getHeaderActions(): array
    {
        return [

            Action::make('update_order_statuses_oc')
                ->label('Оновити Статуси замовлень OpenCart')
                ->color('success')
                ->icon('heroicon-o-arrow-down-tray')
                ->action(function () {
                    $ocStatuses = OcOrderStatus::where('language_id', 5)->get(); // 5 - укр. яз.
                    $existingIdentifiers = OrderStatus::where('store_id', 1)->pluck('identifier')->toArray();

                    $forSave = [];
                    $forUpdate = [];

                    foreach ($ocStatuses as $ocStatus) {
                        $data = [
                            'name' => $ocStatus->name ?? '',
                            'store_id' => 1, // ocStore
                            'identifier' => $ocStatus->order_status_id,
                            'is_active' => 0,
                        ];

                        if (in_array($ocStatus->order_status_id, $existingIdentifiers)) {
                            $forUpdate[] = $data;
                        } else {
                            $forSave[] = $data;
                        }
                    }

                    DB::transaction(function () use ($forSave, $forUpdate) {
                        foreach ($forSave as $data) {
                            OrderStatus::create($data);
                        }

                        foreach ($forUpdate as $dataUpdate) {
                            OrderStatus::updateOrCreate(
                                ['identifier' => $dataUpdate['identifier']],
                                [
                                    'name' => $dataUpdate['name'],
                                    'identifier' => $dataUpdate['identifier'],
                                ]
                            );
                        }
                    });

                    Notification::make()
                        ->title('Оновлення завершено')
                        ->body("Додано: " . count($forSave) . ", оновлено: " . count($forUpdate))
                        ->success()
                        ->send();
                }), 

              Action::make('update_order_statuses_hor')
                ->label('Оновити Статуси замовлень Horoshop')
                ->color('info')
                ->icon('heroicon-o-arrow-down-tray')
                ->action(function () {

                    $response = app(\App\Services\HoroshopApiService::class)->call('orders/get_available_statuses');
                    $horStatuses = $response['response']['statuses'] ?? [];

                    $existingIdentifiers = OrderStatus::where('store_id', 2)->pluck('identifier')->toArray();

                    $forSave = [];
                    $forUpdate = [];


                    foreach($horStatuses as $status){
                        $data = [
                            'name'=> $status['title']['ua'] ?? '',
                            'store_id' => 2, // horoshop
                            'identifier' => $status['id'],
                            'is_active' => 0
                        ];

                        if (in_array($status['id'], $existingIdentifiers)) {
                            $forUpdate[] = $data;
                        } else {
                            $forSave[] = $data;
                        }
                    }

                    DB::transaction(function () use ($forSave, $forUpdate) {
                        foreach ($forSave as $data) {
                            OrderStatus::create($data);
                        }

                        foreach ($forUpdate as $dataUpdate) {
                            OrderStatus::updateOrCreate(
                                ['identifier' => $dataUpdate['identifier']],
                                [
                                    'name' => $dataUpdate['name'],
                                    'identifier' => $dataUpdate['id'],
                                ]
                            );
                        }
                    });

                    Notification::make()
                        ->title('Оновлення завершено')
                        ->body("Додано: " . count($forSave) . ", оновлено: " . count($forUpdate))
                        ->success()
                        ->send();
                }),

                 

            Actions\CreateAction::make(),
        ];
    }
}
