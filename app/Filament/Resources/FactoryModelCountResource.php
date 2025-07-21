<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FactoryModelCountResource\Pages;
use App\Filament\Resources\FactoryModelCountResource\RelationManagers;
use App\Models\FactoryModelCount;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class FactoryModelCountResource extends Resource
{
    protected static ?string $model = FactoryModelCount::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationLabel = 'Кіл-ть форм';
    protected static ?string $modelLabel = 'Кількість форм на виробництві';
    protected static ?string $pluralModelLabel = 'Кількість форм на виробництві';
    protected static ?string $navigationGroup = 'Налаштування';
    protected static bool $hasTitleCaseModelLabel = false;


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('product_id')
                    ->label('Товар')
                    ->options(function (callable $get) {
                        $usedProductIds = FactoryModelCount::pluck('product_id')->toArray();
                        $currentProductId = $get('product_id');

                        // Удаляем текущий product_id из массива "запрещенных"
                        if ($currentProductId) {
                            $usedProductIds = array_diff($usedProductIds, [$currentProductId]);
                        }

                        return Product::whereNotIn('id', $usedProductIds)
                            ->pluck('sku', 'id');
                    })
                    ->searchable()
                    ->required(),

                Forms\Components\TextInput::make('factory1_model_count')
                    ->label('Кіл-ть на В.№1')
                    ->numeric(),
                Forms\Components\TextInput::make('factory2_model_count')
                    ->label('Кіл-ть на В.№2')
                    ->numeric(),
            ])->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('product.sku')
                    ->label('Арт.')
                    ->sortable(),
                Tables\Columns\TextColumn::make('factory1_model_count')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('factory2_model_count')
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
            'index' => Pages\ListFactoryModelCounts::route('/'),
            'create' => Pages\CreateFactoryModelCount::route('/create'),
            'edit' => Pages\EditFactoryModelCount::route('/{record}/edit'),
        ];
    }
}
