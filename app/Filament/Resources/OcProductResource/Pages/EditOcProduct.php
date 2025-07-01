<?php

namespace App\Filament\Resources\OcProductResource\Pages;

use App\Filament\Resources\OcProductResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditOcProduct extends EditRecord
{
    protected static string $resource = OcProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
