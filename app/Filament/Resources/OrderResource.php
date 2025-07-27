<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderResource\Pages;
use App\Filament\Resources\OrderResource\RelationManagers;
use App\Filament\Resources\ProductsRelationManagerResource\RelationManagers\ProductsRelationManager;
use App\Models\Order;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Enums\FiltersLayout;


class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-inbox-arrow-down';

    protected static ?string $navigationLabel = 'Замовлення';

    protected static ?string $pluralModelLabel = 'Замовлення';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('order_number')
                    ->label('Номер заказа')
                    ->required()
                    ->unique(ignoreRecord: true),

                Select::make('store_id')
                    ->label('Магазин')
                    ->relationship('store', 'name')
                    ->searchable()
                    ->required()
                    ->preload(),

                

                DatePicker::make('order_date')
                    ->label('Дата заказа')
                    ->default(now())
                    ->required(),

                Select::make('status')
                    ->label('Статус')
                    ->options([
                        'new' => 'Новий',
                        'in_proogress' => 'В процесі',
                        'ready' => 'Готовий',
                    ]),

                Repeater::make('products')
                    ->relationship() // указывает что это hasMany по products
                    ->label('Товары')
                    ->schema([
                        Select::make('product_id')
                            ->label('Товар')
                            ->options(Product::query()->pluck('name', 'id'))
                            ->searchable()
                            ->required(),
                        Select::make('product_id')
                            ->label('SKU')
                            ->options(Product::query()->pluck('sku', 'id'))
                            ->searchable()
                            ->required(),
                        TextInput::make('quantity')
                            ->label('Количество')
                            ->numeric()
                            ->minValue(1)
                            ->required(),
                    ])->columns(3)
                    ->required()
                    ->columnSpanFull()
                    ->addActionLabel('Додати товар'),
            ]);
    }

   

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('order_number', 'desc')
            ->columns([
                TextColumn::make('order_number')
                    ->label('Номер заказа')
                    ->sortable()
                    // ->description(function ($record) {
                    //     return $record->pivotProducts->pluck('sku')->implode(', ');
                    // })
                    ->tooltip(fn ($record) => $record->pivotProducts->pluck('sku')->implode(', '))
                    ->searchable()
                    ->description(function ($record) {
                        return $record->pivotProducts
                            ->map(fn ($product) => $product->sku . ' - ' . $product->pivot->quantity . 'шт')
                            ->implode(', ');
                    }),
                TextColumn::make('store.name')->label('Магазин')->sortable()->searchable(),
                TextColumn::make('status')
                    ->icon(fn (string $state): string => match ($state) {
                        'ready' => 'heroicon-o-check-circle',
                        'in_progress' => 'heroicon-o-clock',
                        'new' => 'heroicon-o-fire',
                        default => 'heroicon-o-exclamation-circle'
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'ready' => 'success',
                        'in_progress' => 'warning',
                        'new' => 'danger',
                        default => 'info',
                    })
                    ->label('Статус') // Заголовок колонки
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'ready' => 'Готово',
                        'in_progress' => 'В процессе',
                        'new' => 'Новый',
                        default => 'Неизвестно',
                    }),
                TextColumn::make('order_date')->label('Дата заказа')->date()->sortable(),
                TextColumn::make('products_count')->counts('products')->label('Кол-во тов.'),                    
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Статус')
                    ->options([
                        'ready' => 'Готові',
                        'in_progress' => 'В процесі',
                        'new' => 'Нові',
                    ])
            ], layout: FiltersLayout::AboveContent)
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
                BulkAction::make('createFactoryOrder')
                    ->label('Сформувати замовлення на виробництво')
                    ->icon('heroicon-o-truck')
                    ->requiresConfirmation()
                    ->action(function ($records) {
                        $ids = $records->pluck('id')->toArray();
                        session(['selected_order_ids' => $ids]);

                        return redirect()->route('filament.admin.pages.create-factory-order');
                    }),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            ProductsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrders::route('/'),
            'create' => Pages\CreateOrder::route('/create'),
            'edit' => Pages\EditOrder::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()
            ->with('pivotProducts');
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->hasRole('admin'); // только для админа
    }



}
