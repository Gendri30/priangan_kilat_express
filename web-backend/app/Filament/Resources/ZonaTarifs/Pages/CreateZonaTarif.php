<?php

namespace App\Filament\Resources\ZonaTarifs\Pages;

use App\Filament\Resources\ZonaTarifs\ZonaTarifResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;
use Override;

class CreateZonaTarif extends CreateRecord
{
    protected static string $resource = ZonaTarifResource::class;

    public function mount(): void
    {
        abort_unless(Auth::user()?->role === 'manajer', 403);

        parent::mount();
    }
}
