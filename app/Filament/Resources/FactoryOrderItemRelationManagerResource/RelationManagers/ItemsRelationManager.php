<?php

namespace App\Filament\Resources\FactoryOrderItemRelationManagerResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Notifications\Notification;
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
                    ->label('Кількість замовлено')
                    ->helperText('Кількість одиниць, які потрібно виготовити')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('quantity_delivered')
                    ->label('Кількість відвантажено')
                    ->helperText('Кількість одиниць, які вже відвантажено')
                    ->numeric()
                    ->default(0),
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
                
                Tables\Columns\TextColumn::make('quantity_ordered')
                    ->label('Кіл-сть замовлено')
                    ->tooltip('Кількість одиниць, які замовлені для виробництва')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('quantity_delivered')
                    ->label('Кіл-сть відвантажено')
                    ->tooltip('Кількість одиниць, які вже відвантажено з виробництва')
                    ->numeric()
                    ->sortable()
                    ->badge()
                    ->color(function ($record) {
                        if ($record->quantity_delivered == 0) return 'gray';
                        if ($record->quantity_delivered >= $record->quantity_ordered) return 'success';
                        return 'warning';
                    }),
            ])
            ->filters([
                //
            ])
            // ->headerActions([
            //     Tables\Actions\CreateAction::make(),
            // ])
            ->actions([
                Tables\Actions\Action::make('view_deliveries')
                    ->label('Історія відвантажень')
                    ->icon('heroicon-o-clock')
                    ->color('info')
                    ->url(fn ($record) => "/admin/factory-order-deliveries?tableFilters[factory_order_item_id][value]={$record->id}")
                    ->openUrlInNewTab()
                    ->visible(fn ($record) => $record->quantity_delivered > 0),

                Tables\Actions\Action::make('deliver')
                    ->label('Відвантажити')
                    ->icon('heroicon-o-truck')
                    ->color('success')
                    ->form([
                        Forms\Components\DateTimePicker::make('delivered_at')
                            ->label('Дата та час відвантаження')
                            ->default(now())
                            ->required(),
                        Forms\Components\TextInput::make('quantity')
                            ->label('Кількість для відвантаження')
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(fn ($record) => $record->quantity_ordered - $record->quantity_delivered)
                            ->default(fn ($record) => $record->quantity_ordered - $record->quantity_delivered)
                            ->required()
                            ->suffix('шт.'),
                        Forms\Components\Textarea::make('notes')
                            ->label('Примітки')
                            ->placeholder('Додаткові примітки...')
                            ->rows(2),
                    ])
                    ->action(function (array $data, $record) {
                        // Создаем запись об отгрузке
                        $record->factoryOrderDelivery()->create([
                            'user_id' => auth()->id(),
                            'delivered_at' => $data['delivered_at'],
                            'quantity' => $data['quantity'],
                            'notes' => $data['notes'] ?? null,
                        ]);

                        // Обновляем quantity_delivered
                        \App\Filament\Resources\FactoryOrderDeliveryResource::recalculateDeliveredQuantity($record->id);

                        // Уведомление об успехе
                        Notification::make()
                            ->title('Успіх')
                            ->body("Відвантажено {$data['quantity']} шт. товару {$record->product->name}")
                            ->success()
                            ->send();
                    })
                    ->successNotificationTitle('Товар успішно відвантажено')
                    ->visible(fn ($record) => $record->quantity_ordered > $record->quantity_delivered),
                // Tables\Actions\EditAction::make(),
                // Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    // Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);


    }
}
