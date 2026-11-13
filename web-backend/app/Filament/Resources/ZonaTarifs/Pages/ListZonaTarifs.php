<?php

namespace App\Filament\Resources\ZonaTarifs\Pages;

use App\Filament\Resources\ZonaTarifs\ZonaTarifResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListZonaTarifs extends ListRecords
{
    protected static string $resource = ZonaTarifResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
