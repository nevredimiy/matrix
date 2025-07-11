<?php

namespace App\Filament\Resources\OrderStatusResource\Pages;

use App\Filament\Resources\OrderStatusResource;
use App\Models\OcOrderStatus;
use App\Models\OrderStatus;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions\Action;
use Filament\Notifications\Notification;

class ListOrderStatuses extends ListRecords
{
    protected static string $resource = OrderStatusResource::class;

    protected function getHeaderActions(): array
    {
        return [

            Action::make('update_order_statuses_oc')
                ->label('Оновити Статуси замовлень OpenCart')
                ->color('success')
                // ->requiresConfirmation()
                ->icon('heroicon-o-arrow-down-tray')
                ->action(function () {
                    $ocStatuses = OcOrderStatus::all();
                    $statuses = OrderStatus::pluck('Identifier')->toArray();
                    $forUpdate = [];
                    $forSave = [];
                    dump($ocStatuses);
                    foreach($ocStatuses as $ocStatus){

                        $data = [
                            'name' => $ocStatus->name ?? '',
                            'store_id' => 1, // ocStore
                            'identifier' => $ocStatus->order_status_id,
                            'is_active' => 0
                        ];
                        if(in_array($ocStatus->order_status_id, $statuses)){
                            $forUpdate[] = $data;
                        }else {
                            $forSave[] = $data;
                        }
                    }

                    // Создаем новые статусы
                    if(!empty($forSave)){
                        foreach($forSave as $data){
                            OrderStatus::create($data);
                        }
                    }

                    // Обновляем существующие
                    foreach ($forUpdate as $dataUpdate){
                        OrderStatus::updateOrCreate(
                            ['identifier' => $dataUpdate['identifier']],
                            [
                                'name' => $dataUpdate['name'],
                                'store_id' => $dataUpdate['store_id'],
                            ]
                        );
                    }

                    $forSaveCount = count($forSave);
                    $forUpdateCount = count($forUpdate);

                    Notification::make()
                        ->title('Оновлення завершено')
                        ->body("Додано: {$forSaveCount},\nоновлено: {$forUpdateCount}")
                        ->success()
                        ->send();

                 }),    

                 

            Actions\CreateAction::make(),
        ];
    }
}
