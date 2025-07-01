<?php

namespace App\Filament\Resources\OcOrderResource\Pages;

use App\Filament\Resources\OcOrderResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditOcOrder extends EditRecord
{
    protected static string $resource = OcOrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
