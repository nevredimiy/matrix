<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WarehouseReceiptResource\Pages;
use App\Models\WarehouseReceipt;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Actions\Action;
use Filament\Notifications\Notification;

class WarehouseReceiptResource extends Resource
{
    protected static ?string $model = WarehouseReceipt::class;

    protected static ?string $navigationIcon = 'heroicon-o-archive-box';

    protected static ?string $navigationLabel = 'Прийомка товарів';

    protected static ?string $navigationGroup = 'Склад';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('factory_order_delivery_id')
                    ->label('Відвантаження з виробництва')
                    ->relationship('factoryOrderDelivery', 'id')
                    ->getOptionLabelFromRecordUsing(fn ($record) => "Заказ №{$record->factoryOrderItem->factoryOrder->order_number} - {$record->factoryOrderItem->product->name} ({$record->quantity} шт.)")
                    ->required()
                    ->searchable()
                    ->preload()
                    ->live(),

                Forms\Components\DateTimePicker::make('received_at')
                    ->label('Дата та час прийому')
                    ->default(now())
                    ->required(),

                Forms\Components\TextInput::make('quantity_received')
                    ->label('Кількість прийнято')
                    ->numeric()
                    ->minValue(0)
                    ->maxValue(fn ($get) => $get('factory_order_delivery_id') ? \App\Models\FactoryOrderDelivery::find($get('factory_order_delivery_id'))?->quantity : 0)
                    ->required()
                    ->suffix('шт.'),

                Forms\Components\Select::make('status')
                    ->label('Статус')
                    ->options([
                        'pending' => 'Очікує прийому',
                        'received' => 'Прийнято',
                        'damaged' => 'Пошкоджено',
                        'partial' => 'Частково прийнято',
                    ])
                    ->default('received')
                    ->required(),

                Forms\Components\TextInput::make('warehouse_location')
                    ->label('Місце на складі')
                    ->placeholder('Наприклад: Полка A-1, Сектор 5')
                    ->maxLength(255),

                Forms\Components\Textarea::make('notes')
                    ->label('Примітки')
                    ->placeholder('Додаткові примітки до прийому...')
                    ->rows(3)
                    ->columnSpanFull(),

                Forms\Components\Hidden::make('user_id')
                    ->default(auth()->id()),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('received_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('factoryOrderDelivery.factoryOrderItem.factoryOrder.order_number')
                    ->label('№ замовлення')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('factoryOrderDelivery.factoryOrderItem.product.sku')
                    ->label('Артикул')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('factoryOrderDelivery.factoryOrderItem.product.name')
                    ->label('Товар')
                    ->searchable()
                    ->sortable()
                    ->limit(30),

                Tables\Columns\TextColumn::make('quantity_received')
                    ->label('Кіл-сть прийнято')
                    ->numeric()
                    ->sortable()
                    ->badge()
                    ->color('success'),

                Tables\Columns\TextColumn::make('status')
                    ->label('Статус')
                    ->badge()
                    ->color(function (string $state): string {
                        return match ($state) {
                            'pending' => 'warning',
                            'received' => 'success',
                            'damaged' => 'danger',
                            'partial' => 'info',
                            default => 'gray',
                        };
                    }),

                Tables\Columns\TextColumn::make('warehouse_location')
                    ->label('Місце')
                    ->placeholder('—')
                    ->searchable(),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('Прийняв')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('received_at')
                    ->label('Дата прийому')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Створено')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Статус')
                    ->options([
                        'pending' => 'Очікує прийому',
                        'received' => 'Прийнято',
                        'damaged' => 'Пошкоджено',
                        'partial' => 'Частково прийнято',
                    ]),

                Tables\Filters\SelectFilter::make('user_id')
                    ->label('Прийняв')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload(),

                Tables\Filters\Filter::make('received_at')
                    ->form([
                        Forms\Components\DatePicker::make('received_from')
                            ->label('З'),
                        Forms\Components\DatePicker::make('received_until')
                            ->label('По'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['received_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('received_at', '>=', $date),
                            )
                            ->when(
                                $data['received_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('received_at', '<=', $date),
                            );
                    })
                    ->label('Фільтр за датою'),
            ])
            ->actions([
                Tables\Actions\Action::make('quick_receive')
                    ->label('Прийняти швидко')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Прийняти товар?')
                    ->modalDescription('Це автоматично встановить статус "Прийнято" для цього товару.')
                    ->modalSubmitActionLabel('Так, прийняти')
                    ->action(function ($record) {
                        $record->update([
                            'status' => 'received',
                            'received_at' => now(),
                            'quantity_received' => $record->factoryOrderDelivery->quantity,
                        ]);

                        Notification::make()
                            ->title('Успіх')
                            ->body("Товар {$record->factoryOrderDelivery->factoryOrderItem->product->name} прийнято на склад")
                            ->success()
                            ->send();
                    })
                    ->visible(fn ($record) => $record->status === 'pending'),

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
            ->emptyStateHeading('Немає прийомок')
            ->emptyStateDescription('Створіть першу прийомку товару на склад.')
            ->emptyStateIcon('heroicon-o-archive-box');
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
            'index' => Pages\ListWarehouseReceipts::route('/'),
            'create' => Pages\CreateWarehouseReceipt::route('/create'),
            'edit' => Pages\EditWarehouseReceipt::route('/{record}/edit'),
        ];
    }

    // public static function shouldRegisterNavigation(): bool
    // {
    //     return auth()->user()?->hasRole('admin'); // только для админа
    // }
}
