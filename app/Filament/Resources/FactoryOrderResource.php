<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FactoryOrderItemRelationManagerResource\RelationManagers\ItemsRelationManager;
use App\Filament\Resources\FactoryOrderResource\Pages;
use App\Filament\Resources\FactoryOrderResource\RelationManagers;
use App\Models\FactoryOrder;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class FactoryOrderResource extends Resource
{
    protected static ?string $model = FactoryOrder::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationLabel = 'Список замовлень';

    protected static ?int $navigationSort = 10;

    protected static ?string $navigationGroup = 'Виробництво';

    // protected static ?string $navigationParentItem = 'Налаштування';


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('factory_id')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('status')
                    ->required()
                    ->maxLength(191)
                    ->default('в процессе'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('№ замовлення')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('factory.name')
                    ->label('Виробництво')
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Статус замовлення')
                    ->searchable(),
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
            // \App\Filament\Resources\FactoryOrderResource\RelationManagers\FactoryOrderItemRelationManager::class,
            ItemsRelationManager::class
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListFactoryOrders::route('/'),
            'create' => Pages\CreateFactoryOrder::route('/create'),
            'edit' => Pages\EditFactoryOrder::route('/{record}/edit'),
        ];
    }
}
