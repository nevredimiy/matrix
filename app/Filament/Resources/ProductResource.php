<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Filament\Resources\ProductResource\RelationManagers;
use App\Models\Product;
use Filament\Actions\Action as ActionsAction;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Actions\Action;
use Illuminate\Support\Facades\Http;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Forms\Components\Toggle;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationLabel = 'Товари';

    protected static ?string $navigationGroup = 'Налаштування';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Назва')
                    ->maxLength(191),
                Forms\Components\TextInput::make('sku')
                    ->label('SKU')
                    ->unique(ignoreRecord: true)
                    ->required()
                    ->maxLength(191),
                Forms\Components\TextInput::make('image')
                    ->label('Посилання на фото')
                    ->maxLength(191),
                Forms\Components\TextInput::make('stock_quantity')
                    ->label('Кіль-сть')
                    ->numeric()
                     ->minLength(0)
                    ->default(0),
                Forms\Components\TextInput::make('desired_stock_quantity')
                    ->label('Бажана кіль-сть')
                    ->numeric()
                    ->default(0)
                    ->minLength(0),
                Forms\Components\TextInput::make('product_id_oc')
                    ->label('ID товару на OC')
                    ->numeric(),
                Forms\Components\TextInput::make('product_id_hor')
                    ->label('ID товару на Hor')
                    ->numeric(),
                Toggle::make('is_active')
                        ->label('Вкл/Викл'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Назва')
                    ->searchable(),
                Tables\Columns\TextColumn::make('sku')
                    ->label('SKU')
                    ->searchable(),
                 Tables\Columns\ImageColumn::make('image')
                    ->height(50),
                Tables\Columns\TextColumn::make('stock_quantity')
                    ->label('Кількість')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('desired_stock_quantity')
                    ->label('Бажана кіль-сть')
                    ->numeric()
                    ->sortable(),
                ToggleColumn::make('is_active')
                    ->label('Вкл/Викл'),
                
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
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }




}
