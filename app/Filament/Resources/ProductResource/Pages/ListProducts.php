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
                    $productsOc = OcProduct::query()
                        ->whereNotNull('ean')
                        ->with('description')
                        ->where('ean', '!=', '')
                        ->get()
                        ->toArray();

                    $products = Product::all()->toArray();
                    $existingSkus = array_column($products, 'sku');

                    $productsForSave = [];
                    $productsForUpdate = [];

                    foreach ($productsOc as $product) {

                        $imagePath = $product['image'];
                        $image = 'https://dinara.david-freedman.com.ua/image/' . $this->rawurlencode_path($imagePath);
                        $data = [
                            'name' => $product['description']['name'],
                            'sku' => $product['model'],
                            'stock_quantity' => $product['quantity'],
                            'image' => $image,
                            'product_id_oc' => $product['id']
                        ];

                        if (in_array($product['model'], $existingSkus)) {
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
                            ->where('sku', $updateData['sku'])
                            ->update([
                                'name' => $updateData['name'],
                                'stock_quantity' => $updateData['stock_quantity'],
                                'image' => $updateData['image'],
                                'product_id_oc' => $product['id']
                            ]);
                    }

                    // Уведомление прямо здесь:
                    Notification::make()
                        ->title('Оновлення завершено')
                        ->body('Додано: ' . count($productsForSave) . ', оновлено: ' . count($productsForUpdate))
                        ->success()
                        ->send();
                }),
            Action::make('update_products')
                ->label('Оновити товари Horoshop')
                ->color('info')
                ->requiresConfirmation()
                ->icon('heroicon-o-arrow-down-tray')
                ->action(function () {}),

            Actions\CreateAction::make(),
        ];
    }

    public function rawurlencode_path($path)
    {
        return implode('/', array_map('rawurlencode', explode('/', $path)));
    }
}
