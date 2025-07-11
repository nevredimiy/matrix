<?php

namespace App\Filament\Resources\FactoryOrderResource\Pages;

use App\Filament\Resources\FactoryOrderResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditFactoryOrder extends EditRecord
{
    protected static string $resource = FactoryOrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
