<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderStatusResource\Pages;
use App\Filament\Resources\OrderStatusResource\RelationManagers;
use App\Models\OrderStatus;
use App\Models\Store;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Forms\Components\Toggle;
use Illuminate\Database\Eloquent\Collection;

class OrderStatusResource extends Resource
{
    protected static ?string $model = OrderStatus::class;

    protected static ?string $navigationIcon = 'heroicon-o-check-badge';

    protected static ?string $navigationLabel = 'Статуси замовлення';

    protected static ?string $navigationGroup = 'Налаштування';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(191),
                Forms\Components\Select::make('store_id')
                    ->options(Store::all()->pluck('name', 'id'))
                    ->required(),
                Forms\Components\TextInput::make('identifier')
                    ->required()
                    ->numeric(),
                Toggle::make('is_active')
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->description('ВИКЛЮЧЕННЯ СТАТУСУ означає, що замовлення з таким статусом НЕ РОЗГЛЯДАЄТЬСЯ')
            ->columns([
                 Tables\Columns\TextColumn::make('identifier')
                    ->numeric()
                    ->sortable()
                    ->label('ID статусу'),
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->label('Назва'),
                Tables\Columns\TextColumn::make('store.name')
                    ->sortable()
                    ->searchable()
                    ->label('Магазин'),
               
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

                 Tables\Actions\BulkAction::make('disable')
                        ->label('Вимкнути обрані')
                        ->icon('heroicon-o-eye-slash')
                        ->color('warning')
                        ->action(function (Collection $records) {
                            $records->each(function ($record) {
                                $record->update(['is_active' => false]);
                            });
                        })
                        ->requiresConfirmation()
                        ->deselectRecordsAfterCompletion(),

                Tables\Actions\BulkAction::make('enable')
                    ->label('Увімкнути обрані')
                    ->icon('heroicon-o-eye')
                    ->color('success')
                    ->action(function (Collection $records) {
                        $records->each(function ($record) {
                            $record->update(['is_active' => true]);
                        });
                    })
                    ->requiresConfirmation()
                    ->deselectRecordsAfterCompletion(),
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
            'index' => Pages\ListOrderStatuses::route('/'),
            'create' => Pages\CreateOrderStatus::route('/create'),
            'edit' => Pages\EditOrderStatus::route('/{record}/edit'),
        ];
    }
}
