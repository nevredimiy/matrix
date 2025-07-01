<?php

namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Resources\ProductResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions\Action;
use App\Models\Product;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Http;

class ListProducts extends ListRecords
{
    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('import_from_opencart')
                ->label('Импорт из OpenCart')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('success')
                ->requiresConfirmation()
                ->action(function () {

                     // 1. Авторизация и получение токена
                    $loginUrl = 'https://dinara.david-freedman.com.ua/index.php?route=api/login';
                    $apiKey = env('OC_DINARA_API');

                    $loginResponse = Http::asForm()->post($loginUrl, [
                        'key' => $apiKey,
                    ]);

                    if (!$loginResponse->ok()) {
                        Notification::make()
                            ->title('Ошибка авторизации')
                            ->danger()
                            ->send();
                        return;
                    }

                    $apiToken = $loginResponse->json('api_token');

                    // 2. Получение продуктов
                    $productsUrl = 'https://dinara.david-freedman.com.ua/index.php?route=api/product/getProducts';

                    $response = Http::get($productsUrl, [
                        'api_token' => $apiToken,
                    ]);

                    if (!$response->ok()) {
                        Notification::make()
                            ->title('Ошибка при получении товаров: ' . $response->status())
                            ->danger()
                            ->send();
                        return;
                    }

                    $products = $response->json('products');
                    

                    // Твой код импорта продуктов в базу
                    foreach ($products as $product) {
                        // например, по полю model ищем и добавляем
                    }

                    Notification::make()
                        ->title('Импорт завершен успешно')
                        ->success()
                        ->send();
                }),

                 Actions\CreateAction::make(),
            ];

    }
}
