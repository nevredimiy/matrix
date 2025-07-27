<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FactoryOrderItemResource\Pages;
use App\Filament\Resources\FactoryOrderItemResource\RelationManagers;
use App\Models\FactoryOrderItem;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;

class FactoryOrderItemResource extends Resource
{
    protected static ?string $model = FactoryOrderItem::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'temp';

     protected static ?int $navigationSort = 99;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('factory_order_id')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('product_id')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('quantity_ordered')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('quantity_delivered')
                    ->required()
                    ->numeric()
                    ->default(0),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('id', 'desc')
            ->columns([
                TextColumn::make('factory_order_id')
                    ->numeric()
                    ->label('№')
                    ->sortable(),
                TextColumn::make('product.sku')
                    ->label('Арт.')
                    ->tooltip(fn ($record) => $record->product->name ?? null)
                    ->sortable(),
                TextColumn::make('quantity_ordered')
                    ->label('Кіл-ть')
                    ->tooltip('Кількість штук, які замовлені в магазині')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('quantity_delivered')
                    ->label('Від-но')
                    ->tooltip('Відвантажено з виробництва, шт.')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('factoryOrder.factory.name')
                    ->label('Куди')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
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
            'index' => Pages\ListFactoryOrderItems::route('/'),
            'create' => Pages\CreateFactoryOrderItem::route('/create'),
            'edit' => Pages\EditFactoryOrderItem::route('/{record}/edit'),
        ];
    }

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->hasRole('admin'); // только для админа
    }
}
