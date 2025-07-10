<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderGroupResource\Pages;
use App\Filament\Resources\OrderGroupResource\RelationManagers;
use App\Models\OrderGroup;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Tables\Columns\TextColumn;

class OrderGroupResource extends Resource
{
    protected static ?string $model = OrderGroup::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('order_number')->label('Номер замовлення')->searchable(),
                TextColumn::make('store_id')->label('Магазин'),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
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
            \App\Filament\Resources\OrderGroupResource\RelationManagers\OrderProductRelation::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrderGroups::route('/'),
            'view' => Pages\ViewOrderGroup::route('{record}'),
        ];
    }
}
