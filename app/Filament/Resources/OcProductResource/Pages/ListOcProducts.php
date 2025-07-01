<?php

namespace App\Filament\Resources\OcProductResource\Pages;

use App\Filament\Resources\OcProductResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListOcProducts extends ListRecords
{
    protected static string $resource = OcProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
