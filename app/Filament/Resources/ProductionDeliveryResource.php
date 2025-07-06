<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductionDeliveryResource\Pages;
use App\Filament\Resources\ProductionDeliveryResource\RelationManagers;
use App\Models\ProductionDelivery;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ProductionDeliveryResource extends Resource
{
    protected static ?string $model = ProductionDelivery::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationLabel = 'Поставки продукции';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('production_order_id')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('production_facility_id')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('delivered_quantity')
                    ->required()
                    ->numeric(),
                Forms\Components\DateTimePicker::make('delivered_at'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('production_order_id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('production_facility_id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('delivered_quantity')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('delivered_at')
                    ->dateTime()
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
            'index' => Pages\ListProductionDeliveries::route('/'),
            'create' => Pages\CreateProductionDelivery::route('/create'),
            'edit' => Pages\EditProductionDelivery::route('/{record}/edit'),
        ];
    }
}
