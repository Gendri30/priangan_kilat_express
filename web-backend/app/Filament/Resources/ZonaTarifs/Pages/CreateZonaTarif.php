<?php

namespace App\Filament\Resources\ZonaTarifs\Pages;

use App\Filament\Resources\ZonaTarifs\ZonaTarifResource;
use Filament\Resources\Pages\CreateRecord;
use Override;

class CreateZonaTarif extends CreateRecord
{
    protected static string $resource = ZonaTarifResource::class;

    public function mount(): void{
        abort(403, 'tidak mendapat hak akses');
    }
}
