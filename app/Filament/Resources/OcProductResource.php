<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OcProductResource\Pages;
use App\Filament\Resources\OcProductResource\RelationManagers;
use App\Models\OcProduct;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

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
                OcProduct::whereNotNull('ean')
                    ->where('ean', '!=', '')
                    ->whereIn('product_id', function ($sub) {
                        $sub->select('product_id')->from('oc_order_product');
                    })
            )
            ->columns([
                TextColumn::make('product_id')->label('ID'),
                TextColumn::make('model')->label('Артикул'),
                TextColumn::make('ean')->label('EAN'),
                TextColumn::make('price')->label('Цена'),
                TextColumn::make('quantity')->label('Остаток'),
                TextColumn::make('date_available')->label('Дата доступности')->date(),
                TextColumn::make('status')->label('Статус')->formatStateUsing(fn ($state) => $state ? 'Активен' : 'Отключен'),
                TextColumn::make('description.name')->label('Название'),
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
            'index' => Pages\ListOcProducts::route('/'),
            // 'create' => Pages\CreateOcProduct::route('/create'),
            // 'edit' => Pages\EditOcProduct::route('/{record}/edit'),
        ];
    }
}
