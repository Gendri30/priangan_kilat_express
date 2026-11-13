<?php

namespace App\Filament\Resources\ZonaTarifs\Pages;

use App\Filament\Resources\ZonaTarifs\ZonaTarifResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditZonaTarif extends EditRecord
{
    protected static string $resource = ZonaTarifResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
