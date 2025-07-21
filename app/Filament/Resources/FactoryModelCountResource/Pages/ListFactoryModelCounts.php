<?php

namespace App\Filament\Resources\FactoryModelCountResource\Pages;

use App\Filament\Resources\FactoryModelCountResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListFactoryModelCounts extends ListRecords
{
    protected static string $resource = FactoryModelCountResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
