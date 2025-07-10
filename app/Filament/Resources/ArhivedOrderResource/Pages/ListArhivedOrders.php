<?php

namespace App\Filament\Resources\ArhivedOrderResource\Pages;

use App\Filament\Resources\ArhivedOrderResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListArhivedOrders extends ListRecords
{
    protected static string $resource = ArhivedOrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
