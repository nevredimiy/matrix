<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OcProductResource\Pages;
use App\Filament\Resources\OcProductResource\RelationManagers;
use App\Models\OcProduct;
use App\Models\OcOrderProduct;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Tables\Columns\TextColumn;

class OcProductResource extends Resource
{
    protected static ?string $model = OcProduct::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->query(
                // OcProduct::whereNotNull('ean')
                //     ->where('ean', '!=', '')
                //     ->whereIn('product_id', function ($sub) {
                //         $sub->select('product_id')->from('order_product');
                //     })
                OcProduct::with(['orderProducts'])
                    ->withSum('orderProducts as ordered_quantity', 'quantity')
                    ->whereNotNull('ean')
                    ->where('ean', '!=', '')
                    ->whereIn('product_id', function ($sub) {
                        $sub->select('product_id')->from('order_product');
                    })
                    

            )
            ->columns([
                TextColumn::make('product_id')->sortable()->label('ID'),
                TextColumn::make('description.name')->sortable()->label('Название'),
                TextColumn::make('model')->sortable()->label('Модель'),
                TextColumn::make('sku')->sortable()->label('Артикул'),
                TextColumn::make('ean')->sortable()->label('EAN'),
                TextColumn::make('quantity')->sortable()->label('Остаток'),
                TextColumn::make('ordered_quantity')
                    ->label('Заказано')
                    ->sortable()
                    ->numeric(),
                TextColumn::make('status')
                    ->sortable()
                    ->label('Статус')
                    ->formatStateUsing(fn($state) => $state ? 'Активен' : 'Отключен'),
                TextColumn::make('orderProducts')  // псевдоколонка
                    ->label('Номера заказов')
                    ->formatStateUsing(fn($state, $record) => 
                        $record->orderProducts->pluck('order_id')->unique()->implode(', ')
                    )
                    ->wrap()
                    ->limit(50),
            ])
            ->filters([
                //
            ])
            ->actions([
                // Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                // Tables\Actions\BulkActionGroup::make([
                //     Tables\Actions\DeleteBulkAction::make(),
                // ]),
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
            'index' => Pages\ListOcProducts::route('/'),
            // 'create' => Pages\CreateOcProduct::route('/create'),
            // 'edit' => Pages\EditOcProduct::route('/{record}/edit'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }
}
