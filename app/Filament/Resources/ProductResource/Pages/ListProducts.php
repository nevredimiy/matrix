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
                    $url = 'https://dinara.david-freedman.com.ua/index.php?route=api/product/getProducts';
                    $apiKey = env('OC_DINARA_API');

                    $response = Http::asForm()->post($url, [
                        'key' => $apiKey,
                    ]);

                    if (!$response->ok()) {
                        Notification::make()
                            ->title('Ошибка подключения к OpenCart')
                            ->danger()
                            ->send();
                        return;
                    }

                    $products = $response->json()['products'] ?? [];
                    $addedCount = 0;

                    foreach ($products as $item) {
                        $ean = $item['ean'] ?? null;
                        $sku = $item['model'] ?? null;

                        if (!$ean || !$sku) continue;

                        $exists = Product::where('sku', $sku)->exists();

                        if (!$exists) {
                            Product::create([
                                'name' => $item['name'] ?? 'Без названия',
                                'sku' => $sku,
                                'stock_quantity' => $item['quantity'] ?? 0,
                                'desired_stock_quantity' => 0,
                                'ordered_for_production' => 0,
                            ]);

                            $addedCount++;
                        }
                    }

                    Notification::make()
                        ->title('Импорт завершен')
                        ->body("Добавлено новых товаров: {$addedCount}")
                        ->success()
                        ->send();
                }),

                 Actions\CreateAction::make(),
            ];

    }
}
