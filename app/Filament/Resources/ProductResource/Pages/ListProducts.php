<?php

namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Resources\ProductResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions\Action;
use App\Models\Product;
use Filament\Notifications\Notification;
use App\Models\OcProduct;
use Illuminate\Support\Facades\DB;

class ListProducts extends ListRecords
{
    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('update_products_oc')
                ->label('Оновити товари OpenCart')
                ->color('success')
                ->requiresConfirmation()
                ->icon('heroicon-o-arrow-down-tray')
                ->action(function () {
                    $this->updateOcProducts();
                }),
            Action::make('update_products_hor')
                ->label('Оновити товари Horoshop')
                ->color('info')
                ->requiresConfirmation()
                ->icon('heroicon-o-arrow-down-tray')
                ->action(function () {
                    $this->updateHorProducts();
                }),

            Actions\CreateAction::make(),
        ];
    }

    public function rawurlencode_path($path)
    {
        return implode('/', array_map('rawurlencode', explode('/', $path)));
    }

    public function updateOcProducts()
    {
        $existingProductIds = Product::pluck('product_id_oc')
            ->toArray();

        $ocProducts = OcProduct::with(['description' => fn($q) => $q->where('language_id', 1)])
            ->where('status', 1)
            ->get()
            ->toArray();

        $productsForSave = [];
        $productsForUpdate = [];

        foreach ($ocProducts as $product) {

            $imagePath = $product['image'];
            $image = 'https://dinara.david-freedman.com.ua/image/' . $this->rawurlencode_path($imagePath);
            $data = [
                'name' => $product['description']['name'],
                'sku' => $product['model'],
                'stock_quantity' => $product['quantity'],
                'image' => $image,
                'product_id_oc' => $product['product_id'],
                'is_active' => true
            ];

            if (in_array($product['product_id'], $existingProductIds)) {
                $productsForUpdate[] = $data;
            } else {
                $productsForSave[] = $data;
            }
        }

        if (!empty($productsForSave)) {
            DB::table('products')->insert($productsForSave);
        }

        foreach ($productsForUpdate as $updateData) {
            DB::table('products')
                ->where('product_id_oc', $updateData['product_id'])
                ->update([
                    'name' => $updateData['name'],
                    'stock_quantity' => $updateData['stock_quantity'],
                    'image' => $updateData['image'],
                ]);
        }

        // Уведомление прямо здесь:
        Notification::make()
            ->title('Оновлення завершено')
            ->body('Додано: ' . count($productsForSave) . ', оновлено: ' . count($productsForUpdate))
            ->success()
            ->send();
    }

    // пока не берем. Неясно по какому критерию
    public function updateHorProducts()
    {

        $existingProductIds = Product::pluck('sku')
            ->toArray();

        $response = app(\App\Services\HoroshopApiService::class)->call('catalog/export', [
            'expr' => [
                'display_in_showcase' => 1,
            ]
        ]);
        dd($response);

        $products = $response['response']['products'] ?? [];

        $productsForSave = [];
        $productsForUpdate = [];

        foreach($products as $product){

            $image = empty($product['images']) ? ($product['gallery_common'][0] ?? '') : $product['images'][0];

            $data = [
                'name' => $product['title']['ua'],
                'sku' => $product['article'],
                'stock_quantity' => 0,
                'image' => $image,
                'product_id_hor' => $product['product_id'],
                'is_active' => true
            ];
        }
    }
}
