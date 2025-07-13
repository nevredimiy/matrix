<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderResource\Pages;
use App\Filament\Resources\OrderResource\RelationManagers;
use App\Filament\Resources\ProductsRelationManagerResource\RelationManagers\ProductsRelationManager;
use App\Models\Order;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Tables\Columns\TextColumn;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationLabel = 'Замовлення';

    protected static ?string $navigationGroup = 'Головна';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('order_number')
                    ->label('Номер заказа')
                    ->required()
                    ->unique(ignoreRecord: true),

                Select::make('store_id')
                    ->label('Магазин')
                    ->relationship('store', 'name')
                    ->searchable()
                    ->required(),

                DatePicker::make('order_date')
                    ->label('Дата заказа')
                    ->default(now())
                    ->required(),

                Repeater::make('products')
                    ->relationship() // указывает что это hasMany по products
                    ->label('Товары')
                    ->schema([
                        Select::make('product_id')
                            ->label('Товар')
                            ->options(Product::query()->pluck('name', 'id'))
                            ->searchable()
                            ->required(),
                        Select::make('product_id')
                            ->label('SKU')
                            ->options(Product::query()->pluck('sku', 'id'))
                            ->searchable()
                            ->required(),
                        TextInput::make('quantity')
                            ->label('Количество')
                            ->numeric()
                            ->minValue(1)
                            ->required(),
                    ])->columns(3)
                    ->required()
                    ->columnSpanFull()
                    ->addActionLabel('Додати товар'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('order_number')->label('Номер заказа')->sortable()->searchable(),
                TextColumn::make('store.name')->label('Магазин')->sortable()->searchable(),
                TextColumn::make('status')->label('Статус')->sortable(),
                TextColumn::make('order_date')->label('Дата заказа')->date()->sortable(),
                TextColumn::make('products_count')->counts('products')->label('Кол-во тов.'),
                TextColumn::make('products_skus')
                    ->label('Артикулы')
                    ->getStateUsing(function ($record) {
                        return $record->pivotProducts->pluck('sku')->implode(', ');
                    })
                    
                    // ->toggleable()
                    ->limit(50) // если хочешь ограничить длину вывода
                    ->tooltip(fn ($record) => $record->pivotProducts->pluck('name')->implode(', ')), // полный список в tooltip
                    
            ])
            ->defaultSort('order_date', 'desc')
            ->filters([
                //
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
            ProductsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrders::route('/'),
            'create' => Pages\CreateOrder::route('/create'),
            'edit' => Pages\EditOrder::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()
            ->with('pivotProducts');
    }

}
