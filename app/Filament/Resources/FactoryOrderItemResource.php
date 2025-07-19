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
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\Section;

class FactoryOrderItemResource extends Resource
{
    protected static ?string $model = FactoryOrderItem::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationLabel = 'Замовлені товари';

    protected static ?int $navigationSort = 9;

    protected static ?string $navigationGroup = 'Виробництво';

    protected static ?string $pluralModelLabel = 'Товари';

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
            ->columns([
                Tables\Columns\TextColumn::make('factory_order_id')
                    ->label('№ Зам.')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('product.name')
                    ->label('Назва')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('product.sku')
                    ->label('Арт.')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('quantity_ordered')
                    ->label('Кіл-ть')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('quantity_delivered')
                    ->label('Відвантажено')
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

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make()
                    ->columnSpan([
                        'sm' => 2,
                        'xl' => 3,
                        '2xl' => 4,
                    ])
                    ->schema([
                        TextEntry::make('factory_order_id')
                            ->label('Номер замовлення')->columnSpan(2),
                        TextEntry::make('order.factory.name')
                            ->label('Виробництво')->columnSpan(2),
                    ]),
                 Section::make()
                    ->columns([
                        'sm' => 3,
                        'xl' => 6,
                        '2xl' => 8,
                    ])
                    ->schema([
                       
                        TextEntry::make('product.name')
                            ->label('Назва продукту'),
                        TextEntry::make('product.sku')
                            ->label('Артикул'),
                        ImageEntry::make('product.image')
                            ->label('Фото'),

                    ]),
                TextEntry::make('product.name')
                    ->label('Назва продукту'),
                 TextEntry::make('product.stock_quantity')
                    ->label('Кількість на складі'),
                 TextEntry::make('product.desired_stock_quantity')
                    ->label('Бажана кількість на складі'),
                
            ]);
    }
}
