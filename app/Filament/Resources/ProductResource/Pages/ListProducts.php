<?php

namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Resources\ProductResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions\Action;
use App\Models\Product;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use App\Models\OcProduct;

class ListProducts extends ListRecords
{
    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('update_products')
                ->label('Оновити товари')
                ->color('success')
                ->requiresConfirmation()
                ->icon('heroicon-o-arrow-down-tray')
                ->action(function () {
                    $products = OcProduct::whereNotNull('ean')
                        ->where('ean', '!=', '')
                        ->get()
                        ->toArray;
                    dd($products);
                }),

            Actions\CreateAction::make(),
            
            // Action::make('import_from_opencart')
            //     ->label('Импорт из OpenCart по Api')
            //     ->icon('heroicon-o-arrow-down-tray')
            //     ->color('success')
            //     ->requiresConfirmation()
            //     ->action(function () {

            //         $baseUrl = 'https://dinara.david-freedman.com.ua/';
            //         $apiKey = env('OC_DINARA_API');
            //                             $cookieJar = new \GuzzleHttp\Cookie\CookieJar();

            //         $client = new Client([
            //             'base_uri' => $baseUrl,
            //             'cookies' => $cookieJar,
            //         ]);

            //         $loginResponse = $client->post('index.php?route=api/login', [
            //             'form_params' => ['key' => $apiKey],
            //         ]);

            //         $body = json_decode((string) $loginResponse->getBody(), true);
            //         $apiToken = $body['api_token'] ?? null;

            //         if (!$apiToken) {
            //             dd('Ошибка логина', $body);
            //         }

            //         // ⬇️ вот тут — проверим, какие cookies сохранены
            //         foreach ($cookieJar->toArray() as $cookie) {
            //             echo $cookie['Name'] . ': ' . $cookie['Value'] . "\n";
            //         }

            //         // Тот же клиент, тот же cookieJar
            //         $response = $client->get('index.php?route=api/product/getProducts', [
            //             'query' => ['api_token' => $apiToken],
            //             'headers' => [
            //                 'Accept'     => 'application/json',
            //             ]
            //     ]);


            //         $body = (string) $response->getBody();

            //         // 👇 Добавим на этом этапе dd, чтобы увидеть реальный ответ

            //         $data = json_decode($body, true);

            //         if (!isset($data['products']) || !is_array($data['products'])) {
            //             Notification::make()
            //                 ->title('Нет данных о продуктах')
            //                 ->warning()
            //                 ->send();
            //             return;
            //         }

            //         // 5. Сохраняем продукты
            //         foreach ($data['products'] as $product) {
            //             Product::updateOrCreate(
            //                 ['model' => $product['model']],
            //                 [
            //                     'name' => $product['name'] ?? 'Без названия',
            //                     'ean' => $product['ean'] ?? null,
            //                     'price' => $product['price'] ?? 0,
            //                     'quantity' => $product['quantity'] ?? 0,
            //                     'status' => $product['status'] ?? 0,
            //                 ]
            //             );
            //         }

            //         Notification::make()
            //             ->title('Импорт завершен: ' . count($data['products']) . ' товаров')
            //             ->success()
            //             ->send();

            //     }),




                 
            ];

    }
}
