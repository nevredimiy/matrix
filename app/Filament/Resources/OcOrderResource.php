<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OcOrderResource\Pages;
use App\Filament\Resources\OcOrderResource\RelationManagers;
use App\Models\OcOrder;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Tables\Columns\TextColumn;

class OcOrderResource extends Resource
{
    protected static ?string $model = OcOrder::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Общие настройки';

    protected static bool $shouldRegisterNavigation = false;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        // dd($table);
        return $table
            ->query(
                OcOrder::whereIn('order_id', function ($subQuery) {
                    $subQuery->select('op.order_id')
                        ->from('order_product as op')
                        ->join('product as p', 'op.product_id', '=', 'p.product_id')
                        ->whereNotNull('p.ean')
                        ->where('p.ean', '!=', '');
                })
            )
            ->columns([
                TextColumn::make('order_id')->label('ID'),
                TextColumn::make('firstname')->label('Имя'),
                TextColumn::make('lastname')->label('Фамилия'),
                TextColumn::make('telephone')->label('Телефон'),
                TextColumn::make('email')->label('Email'),
                TextColumn::make('total')->label('Сумма'),
                TextColumn::make('date_added')->label('Дата')->dateTime(),
            ])
            ->defaultSort('order_id', 'desc')
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
            'index' => Pages\ListOcOrders::route('/'),
            // 'create' => Pages\CreateOcOrder::route('/create'),
            // 'edit' => Pages\EditOcOrder::route('/{record}/edit'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }
    
}
