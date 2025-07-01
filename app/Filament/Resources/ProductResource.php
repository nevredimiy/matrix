<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Filament\Resources\ProductResource\RelationManagers;
use App\Models\Product;
use Filament\Actions\Action as ActionsAction;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Actions\Action;
use Illuminate\Support\Facades\Http;
use Filament\Notifications\Notification;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(191),
                Forms\Components\TextInput::make('sku')
                    ->label('SKU')
                    ->required()
                    ->maxLength(191),
                Forms\Components\TextInput::make('stock_quantity')
                    ->required()
                    ->numeric()
                    ->default(0),
                Forms\Components\TextInput::make('desired_stock_quantity')
                    ->required()
                    ->numeric()
                    ->default(0),
                Forms\Components\TextInput::make('ordered_for_production')
                    ->required()
                    ->numeric()
                    ->default(0),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('sku')
                    ->label('SKU')
                    ->searchable(),
                Tables\Columns\TextColumn::make('stock_quantity')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('desired_stock_quantity')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('ordered_for_production')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }



 

    public static function getGlobalActions(): array
    {
        return [
            Action::make('import_from_opencart')
                ->label('Импорт из OpenCart')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('success')
                ->requiresConfirmation()
                ->action(function () {
                    self::importFromOpenCart();
                }),
        ];
    }

    public static function importFromOpenCart(): void
    {
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
            ->title("Импорт завершен")
            ->body("Добавлено новых товаров: {$addedCount}")
            ->success()
            ->send();
    }

}
