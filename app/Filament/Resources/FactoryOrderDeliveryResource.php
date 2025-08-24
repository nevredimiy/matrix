<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FactoryOrderDeliveryResource\Pages;
use App\Filament\Resources\FactoryOrderDeliveryResource\RelationManagers;
use App\Models\FactoryOrderDelivery;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class FactoryOrderDeliveryResource extends Resource
{
    protected static ?string $model = FactoryOrderDelivery::class;

    protected static ?string $navigationIcon = 'heroicon-o-truck';

    protected static ?string $navigationLabel = 'Відвантаження з виробництва';

    protected static ?string $navigationGroup = 'Виробництво';

    protected static ?int $navigationSort = 20;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('factory_order_item_id')
                    ->label('Товар для відвантаження')
                    ->relationship('factoryOrderItem', 'id')
                    ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->product->name} (Артикул: {$record->product->sku}) - Замовлено: {$record->quantity_ordered} шт.")
                    ->required()
                    ->searchable()
                    ->preload(),

                Forms\Components\DateTimePicker::make('delivered_at')
                    ->label('Дата та час відвантаження')
                    ->default(now())
                    ->required(),

                Forms\Components\TextInput::make('quantity')
                    ->label('Кількість відвантажено')
                    ->required()
                    ->numeric()
                    ->minValue(1)
                    ->suffix('шт.'),

                Forms\Components\Textarea::make('notes')
                    ->label('Примітки')
                    ->placeholder('Додаткові примітки до відвантаження...')
                    ->rows(3)
                    ->columnSpanFull(),

                Forms\Components\Hidden::make('user_id')
                    ->default(auth()->id()),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('delivered_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('factoryOrderItem.factoryOrder.order_number')
                    ->label('№ замовлення')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('factoryOrderItem.product.sku')
                    ->label('Артикул')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('factoryOrderItem.product.name')
                    ->label('Товар')
                    ->searchable()
                    ->sortable()
                    ->limit(30),

                Tables\Columns\TextColumn::make('quantity')
                    ->label('Кіл-сть відвантажено')
                    ->numeric()
                    ->sortable()
                    ->badge()
                    ->color('success'),

                Tables\Columns\TextColumn::make('delivered_at')
                    ->label('Дата відвантаження')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('Хто відвантажив')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('receipt_status')
                    ->label('Статус прийомки')
                    ->getStateUsing(function ($record) {
                        $latestReceipt = $record->latestWarehouseReceipt;
                        return $latestReceipt ? $latestReceipt->status : 'pending';
                    })
                    ->badge()
                    ->color(function ($state) {
                        return match ($state) {
                            'received' => 'success',
                            'pending' => 'warning',
                            'damaged' => 'danger',
                            'partial' => 'info',
                            default => 'gray',
                        };
                    }),

                Tables\Columns\TextColumn::make('notes')
                    ->label('Примітки')
                    ->limit(50)
                    ->tooltip(function ($record) {
                        return $record->notes;
                    })
                    ->placeholder('—'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Створено')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('factory_order_item_id')
                    ->label('Товар')
                    ->relationship('factoryOrderItem.product', 'name')
                    ->searchable()
                    ->preload()
                    ->multiple(),

                Tables\Filters\SelectFilter::make('user_id')
                    ->label('Хто відвантажив')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload(),

                Tables\Filters\SelectFilter::make('receipt_status')
                    ->label('Статус прийомки')
                    ->options([
                        'pending' => 'Очікує прийому',
                        'received' => 'Прийнято',
                        'damaged' => 'Пошкоджено',
                        'partial' => 'Частково прийнято',
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        if (empty($data['value'])) {
                            return $query;
                        }

                        return $query->whereHas('warehouseReceipts', function (Builder $subQuery) use ($data) {
                            $subQuery->where('status', $data['value']);
                        });
                    }),

                Tables\Filters\Filter::make('delivered_at')
                    ->form([
                        Forms\Components\DatePicker::make('delivered_from')
                            ->label('З'),
                        Forms\Components\DatePicker::make('delivered_until')
                            ->label('По'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['delivered_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('delivered_at', '>=', $date),
                            )
                            ->when(
                                $data['delivered_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('delivered_at', '<=', $date),
                            );
                    })
                    ->label('Фільтр за датою'),
            ])
            ->actions([
                Tables\Actions\Action::make('create_receipt')
                    ->label('Прийняти на склад')
                    ->icon('heroicon-o-archive-box')
                    ->color('success')
                    ->url(fn ($record) => "/admin/warehouse-receipts/create?factory_order_delivery_id={$record->id}")
                    ->openUrlInNewTab()
                    ->visible(fn ($record) => $record->warehouseReceipts->isEmpty() || $record->warehouseReceipts->where('status', 'received')->isEmpty()),

                Tables\Actions\Action::make('view_receipts')
                    ->label('Переглянути прийомки')
                    ->icon('heroicon-o-eye')
                    ->color('info')
                    ->url(fn ($record) => "/admin/warehouse-receipts?tableFilters[factory_order_delivery_id][value]={$record->id}")
                    ->openUrlInNewTab()
                    ->visible(fn ($record) => $record->warehouseReceipts->isNotEmpty()),

                Tables\Actions\EditAction::make()
                    ->label('Редагувати'),
                Tables\Actions\DeleteAction::make()
                    ->label('Видалити')
                    ->requiresConfirmation(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('Видалити вибрані')
                        ->requiresConfirmation(),
                ]),
            ])
            ->emptyStateHeading('Немає відвантажень')
            ->emptyStateDescription('Створіть перше відвантаження, натиснувши кнопку "Створити".')
            ->emptyStateIcon('heroicon-o-truck');
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
            'index' => Pages\ListFactoryOrderDeliveries::route('/'),
            'create' => Pages\CreateFactoryOrderDelivery::route('/create'),
            'edit' => Pages\EditFactoryOrderDelivery::route('/{record}/edit'),
        ];
    }

    protected static function afterCreate($record): void
    {
        // Создаем запись о приемке со статусом "pending"
        \App\Models\WarehouseReceipt::create([
            'factory_order_delivery_id' => $record->id,
            'user_id' => auth()->id(),
            'quantity_received' => 0, // Пока не принято
            'status' => 'pending',
            'notes' => 'Автоматично створено при відвантаженні з виробництва',
        ]);

        // Обновляем quantity_delivered в FactoryOrderItem
        static::recalculateDeliveredQuantity($record->factory_order_item_id);
    }

    protected static function afterUpdate($record): void
    {
        // Пересчитываем quantity_delivered после обновления
        static::recalculateDeliveredQuantity($record->factory_order_item_id);
    }

    protected static function afterDelete($record): void
    {
        // Пересчитываем quantity_delivered после удаления
        static::recalculateDeliveredQuantity($record->factory_order_item_id);
    }

    /**
     * Пересчитывает общее отгруженное количество для указанного элемента заказа
     */
    public static function recalculateDeliveredQuantity(int $factoryOrderItemId): void
    {
        $item = \App\Models\FactoryOrderItem::find($factoryOrderItemId);

        if ($item) {
            $totalDelivered = $item->factoryOrderDelivery()->sum('quantity');
            $item->update(['quantity_delivered' => $totalDelivered]);

            // После пересчета проверяем статус всего заказа
            static::checkAndUpdateOrderStatus($item->factory_order_id);

            // Логируем изменение для отладки
            \Illuminate\Support\Facades\Log::info("Пересчитано quantity_delivered для FactoryOrderItem ID {$factoryOrderItemId}: {$totalDelivered}");
        }
    }

    /**
     * Проверяет и обновляет статус заказа на производство
     */
    public static function checkAndUpdateOrderStatus(int $factoryOrderId): void
    {
        $factoryOrder = \App\Models\FactoryOrder::with('items')->find($factoryOrderId);

        if ($factoryOrder) {
            // Проверяем, все ли товары полностью отгружены
            $allItemsCompleted = $factoryOrder->items->every(function ($item) {
                return $item->quantity_delivered >= $item->quantity_ordered;
            });

            // Проверяем, есть ли хоть один товар в заказе
            $hasItems = $factoryOrder->items->isNotEmpty();

            if ($allItemsCompleted && $hasItems && $factoryOrder->status !== 'Завершен') {
                $factoryOrder->update(['status' => 'Завершен']);
                \Illuminate\Support\Facades\Log::info("Заказ на производство ID {$factoryOrderId} отмечен как завершенный");
            } elseif (!$allItemsCompleted && $factoryOrder->status === 'Завершен') {
                // Если заказ был завершен, но теперь не все товары отгружены - возвращаем в процесс
                $factoryOrder->update(['status' => 'в процессе']);
                \Illuminate\Support\Facades\Log::info("Заказ на производство ID {$factoryOrderId} возвращен в статус 'в процессе'");
            }
        }
    }

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->hasRole('admin'); // только для админа
    }
}
