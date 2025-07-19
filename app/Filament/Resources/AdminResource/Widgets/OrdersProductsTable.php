<?php

namespace App\Filament\Resources\AdminResource\Widgets;

use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;
use App\Models\OrderProduct;
use Filament\Tables\Grouping\Group;

class OrdersProductsTable extends BaseWidget implements Tables\Contracts\HasTable
{
    protected int | string | array $columnSpan = 'full';  

    protected static ?string $pluralModelLabel = 'Заказанные продукты';

    public function getTitle(): string
    {
        return 'Последние товары в заказах';
    }

    
    public function table(Table $table): Table
    {
        return $table
            ->heading('Заказа с товарами')
            ->description('Общая картина заказанных товаров')
            ->groups([
                Group::make('order.id')
                    ->label('Заказ'),
            ])
            ->defaultGroup('order.id')
            ->query(
                 OrderProduct::query()
                    ->with(['order', 'product', 'factoryOrderItems']) 
                    ->latest()
            )
            ->columns([
                
                TextColumn::make('product.sku')->label('SKU'),
                TextColumn::make('quantity')->label('Заказано'),
                TextColumn::make('product.stock_quantity')->label('На складе'),
                TextColumn::make('product.desired_stock_quantity')->label('Желаем'),
                TextColumn::make('factory_order_items_count')
                    ->label('На производстве')
                    ->getStateUsing(function ($record) {
                        return $record->factoryOrderItems()->count();
                    }),
            ]);
    }
}
