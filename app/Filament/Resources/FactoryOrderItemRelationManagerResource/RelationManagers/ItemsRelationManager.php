<?php

namespace App\Filament\Resources\FactoryOrderItemRelationManagerResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'items';

    protected static ?string $recordTitleAttribute = 'id';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                // Forms\Components\TextInput::make('id')
                //     ->required()
                //     ->maxLength(255),
                Forms\Components\TextInput::make('product_id')
                    ->required(),
                Forms\Components\TextInput::make('quantity_ordered')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('quantity_delivered')
                    ->numeric(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                // Tables\Columns\TextColumn::make('id'),
                Tables\Columns\TextColumn::make('product.sku')
                    ->label('Артикул'),
                Tables\Columns\TextColumn::make('product.name')
                    ->label('Наименование'),
                Tables\Columns\TextColumn::make('quantity_ordered'),
                Tables\Columns\TextColumn::make('quantity_delivered'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
