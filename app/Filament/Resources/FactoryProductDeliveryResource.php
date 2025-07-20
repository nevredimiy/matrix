<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FactoryProductDeliveryResource\Pages;
use App\Filament\Resources\FactoryProductDeliveryResource\RelationManagers;
use App\Models\FactoryProductDelivery;
use Filament\Forms;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class FactoryProductDeliveryResource extends Resource
{
    protected static ?string $model = FactoryProductDelivery::class;
    
    protected static ?string $pluralModel = 'Отгрузка товара';
    protected static ?string $navigationGroup = 'Производство';
    protected static ?string $navigationLabel = 'Отгрузка';
    protected static ?int $navigationSort = 18;
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $pluralModelLabel = 'Відгрузка';
    protected static ?string $modelLabel = 'Відгрузку';



    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Repeater::make('Товар')
                    ->schema([
                        Forms\Components\Select::make('product_id')
                            ->relationship(name: 'product', titleAttribute: 'sku')
                            ->searchable(['name', 'sku'])
                            ->preload()
                            ->label('SKU'),
                        Forms\Components\TextInput::make('quantity')
                            ->required()
                            ->numeric()
                            ->label('Кол-во'),
                        Forms\Components\DateTimePicker::make('delivered_at')
                            ->label('Дата отгрузки')
                            ->displayFormat('d F Y')
                            ->default(now())
                            ->locale('uk'),
                        Forms\Components\Select::make('delivered_by')
                            ->relationship(name: 'user', titleAttribute: 'name')
                             ->label('Кем отгружено')
                    ])
                    ->columnSpan('full')
                    ->columns(4)
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('product.sku')
                    ->label('SKU')
                    ->sortable(),
                Tables\Columns\TextColumn::make('quantity')
                    ->label('Кіл-ть')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('delivered_at')
                    ->label('Дата відгрузки')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Відгружено')
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
                // Tables\Actions\EditAction::make(),
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
            'index' => Pages\ListFactoryProductDeliveries::route('/'),
            'create' => Pages\CreateFactoryProductDelivery::route('/create'),
            // 'edit' => Pages\EditFactoryProductDelivery::route('/{record}/edit'),
        ];
    }

   
}
