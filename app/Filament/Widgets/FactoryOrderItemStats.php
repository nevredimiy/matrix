<?php

namespace App\Filament\Widgets;

use App\Models\FactoryOrderItem;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class FactoryOrderItemStats extends BaseWidget
{
    protected function getStats(): array
    {
        $totalOrdered = FactoryOrderItem::sum('quantity_ordered');
        $totalDelivered = FactoryOrderItem::sum('quantity_delivered');

        $percentDelivered = $totalOrdered > 0
            ? round(($totalDelivered / $totalOrdered) * 100, 1)
            : 0;

        return [
            Stat::make('Всего заказано', number_format($totalOrdered))
                ->description('шт.'),
            Stat::make('Всего отгружено', number_format($totalDelivered))
                ->description('шт.'),
            Stat::make('Процент выполнения', $percentDelivered . '%')
                ->description('Отгружено от общего')
                ->color($percentDelivered >= 100 ? 'success' : 'warning'),
        ];
    }
}
