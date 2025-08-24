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
use Filament\Tables\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;

class FactoryOrderResource extends Resource
{
    protected static ?string $model = FactoryOrder::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationLabel = 'Замовлення';

    protected static ?int $navigationSort = 20;

    protected static ?string $navigationGroup = 'Виробництво';

    // protected static bool $shouldRegisterNavigation = false;  // Скрываем из меню

    

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
                Tables\Columns\TextColumn::make('order_number')
                    ->label('№ замовлення')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('factory.name')
                    ->label('Виробництво')
                    ->sortable(),

                Tables\Columns\TextColumn::make('completion_percentage')
                    ->label('Прогрес')
                    ->getStateUsing(function ($record) {
                        $totalOrdered = $record->items->sum('quantity_ordered');
                        $totalDelivered = $record->items->sum('quantity_delivered');

                        if ($totalOrdered == 0) return '0%';

                        $percentage = round(($totalDelivered / $totalOrdered) * 100);
                        return $percentage . '%';
                    })
                    ->badge()
                    ->color(function ($state) {
                        $percentage = (int) str_replace('%', '', $state);
                        if ($percentage == 100) return 'success';
                        if ($percentage >= 50) return 'warning';
                        return 'gray';
                    })
                    ->sortable(false),

                Tables\Columns\TextColumn::make('status')
                    ->label('Статус замовлення')
                    ->badge()
                    ->color(function (string $state): string {
                        return match ($state) {
                            'в процессе' => 'warning',
                            'Завершен' => 'success',
                            default => 'gray',
                        };
                    })
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
                Tables\Filters\SelectFilter::make('status')
                    ->label('Статус замовлення')
                    ->options([
                        'в процессе' => 'В процесі',
                        'Завершен' => 'Завершен',
                    ])
                    ->default()
                    ->multiple(),

                Tables\Filters\SelectFilter::make('factory_id')
                    ->label('Виробництво')
                    ->relationship('factory', 'name')
                    ->searchable()
                    ->preload(),

                Tables\Filters\Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('created_from')
                            ->label('З'),
                        Forms\Components\DatePicker::make('created_until')
                            ->label('По'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    })
                    ->label('Фільтр за датою створення'),
            ])
            ->actions([
                Tables\Actions\Action::make('complete_order')
                    ->label('Завершити замовлення')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Завершити замовлення?')
                    ->modalDescription('Це позначить замовлення як завершене, незалежно від кількості відвантажених товарів.')
                    ->modalSubmitActionLabel('Так, завершити')
                    ->action(function ($record) {
                        $record->update(['status' => 'Завершен']);

                        Notification::make()
                            ->title('Успіх')
                            ->body("Замовлення №{$record->order_number} завершено")
                            ->success()
                            ->send();
                    })
                    ->visible(fn ($record) => $record->status !== 'Завершен'),

                Tables\Actions\Action::make('view_items')
                    ->label('Переглянути товари')
                    ->icon('heroicon-o-eye')
                    ->color('info')
                    ->url(fn ($record) => "/admin/factory-orders/{$record->id}")
                    ->openUrlInNewTab(),
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

    public static function shouldRegisterNavigation(): bool
    {
        return Auth::user()?->hasRole('admin'); // только для админа
     
    }
}
