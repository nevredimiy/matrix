<?php

namespace App\Filament\Resources\FactoryModelCountResource\Pages;

use App\Filament\Resources\FactoryModelCountResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditFactoryModelCount extends EditRecord
{
    protected static string $resource = FactoryModelCountResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
