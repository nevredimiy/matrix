<?php

namespace App\Filament\Pages;

use App\Models\FactoryOrderItem;
use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class FactoryMissingPage extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.factory-missing-page';

    protected static bool $shouldRegisterNavigation = false;

    public ?int $factory_id = null;

    public function mount(?int $factory_id = null): void
    {
        $this->factory_id = $factory_id;
    }

    public static function getSlug(): string
    {
        return 'factory-missing-page/{factory_id?}'; // параметр маршрута
    }

     
   
    public function table(Table $table): Table
    {
        $factory_id = request()->get('factory_id');

         return $table
            ->query(
                FactoryOrderItem::query()
                    ->selectRaw('
                        MIN(id) as id,
                        product_id,
                        SUM(quantity_ordered) as total_quantity,
                        SUM(quantity_delivered) as total_delivered,
                        SUM(quantity_ordered) - SUM(quantity_delivered) as missing_quantity
                    ')
                    ->whereHas('factoryOrder', function ($q) {
                        $q->where('factory_id', $this->factory_id);
                    })
                    ->groupBy('product_id')
                    ->with('product')
                    ->orderByDesc('missing_quantity')
            )
            ->columns([
                TextColumn::make('product.name')
                    ->label('Назва')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('product.sku')
                    ->label('Арт.')
                    ->sortable()
                    ->numeric(),                
                TextColumn::make('missing_quantity')
                    ->label('На изготовление, шт')
                    ->sortable()
                    ->numeric(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
           
            ]);
    }


    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }
}
