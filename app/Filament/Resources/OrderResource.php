<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderResource\Pages;
use App\Filament\Resources\OrderResource\RelationManagers;
use App\Models\Order;
use App\Models\Store;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\QueryBuilder\Constraints\DateConstraint;
use Filament\Tables\Filters\QueryBuilder;
use Filament\Tables\Filters\QueryBuilder\Constraints\SelectConstraint;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Columns\SelectColumn;
use Filament\Tables\Actions\BulkAction;
use Illuminate\Database\Eloquent\Collection;

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
                Forms\Components\TextInput::make('order_number')
                    ->label('Номер замовлення')
                    ->required()
                    ->maxLength(191),
                Forms\Components\TextInput::make('product_sku')
                    ->label('SKU')
                    ->required()
                    ->maxLength(191),
                Forms\Components\TextInput::make('quantity')
                    ->label('Кількість')
                    ->required()
                    ->numeric(),
                 Forms\Components\TextInput::make('stock_quantity')
                    ->label('На складі')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('name')
                    ->label('Назва'),
                Forms\Components\TextInput::make('image')
                    ->label('Посилання на фото'),
                Forms\Components\DateTimePicker::make('order_date')
                    ->label('Дата створення'),
                Forms\Components\Select::make('store_id')
                    ->label('Магазин')
                    ->options(Store::pluck('name', 'id')->toArray())
                    ->required(),
                Forms\Components\Select::make('status')
                    ->label('Статус')
                    ->options([
                        'pending' => 'В ожидании',
                        'new' => 'Новый'
                    ])
                    ->default('pending'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('order_date')
                    ->label('Дата')
                    ->date('d.m.y H:i')
                    ->searchable(),
                // TextColumn::make('order_number')
                //     ->label('Номер')
                //     ->searchable(),
                TextColumn::make('product_sku')
                    ->label('SKU')
                    ->searchable(),
                TextColumn::make('quantity')
                    ->label('Кількість')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('stock_quantity')
                    ->label('На складі')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('name')
                    ->label('Назва')
                    ->searchable(),
                Tables\Columns\ImageColumn::make('image')
                    ->height(50),

                TextColumn::make('store.name')
                    ->label('Магазин')
                    ->sortable(),
                TextColumn::make('status')
                    ->badge()
                    // ->color(fn (string $state): string => match ($state) {
                    //     'pending' => 'success',
                    //     'new' => 'warning',
                    // })
                    ->label('Статус')
                    ->searchable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultGroup('order_number')
             ->groups([
                    Group::make('order_number')
                        ->label('Номер замовлення'),
                ])

            ->filters([
                Filter::make('order_date_range')
                    ->label('Дата замовлення')
                    ->form([
                        DatePicker::make('order_date_from')
                            ->label('Від'),
                        DatePicker::make('order_date_until')
                            ->label('До'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when(
                                $data['order_date_from'],
                                fn($q) =>
                                $q->whereDate('order_date', '>=', $data['order_date_from'])
                            )
                            ->when(
                                $data['order_date_until'],
                                fn($q) =>
                                $q->whereDate('order_date', '<=', $data['order_date_until'])
                            );
                    })->columnSpan('full')->columns(2)
            ], layout: FiltersLayout::AboveContent)
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make(),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
                BulkAction::make('createFactoryOrder')
                ->label('Создать заказ на производство')
                ->action(function (Collection $records, array $data) {
                    // Сохраняем ID заказов во временное хранилище (например, сессия)
                    session(['selected_order_ids' => $records->pluck('id')->toArray()]);

                    // редирект на кастомную страницу формы
                    return redirect()->route('filament.admin.pages.create-factory-order');
                })
                ->requiresConfirmation()
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
            'index' => Pages\ListOrders::route('/'),
            'create' => Pages\CreateOrder::route('/create'),
            'edit' => Pages\EditOrder::route('/{record}/edit'),
        ];
    }
}
