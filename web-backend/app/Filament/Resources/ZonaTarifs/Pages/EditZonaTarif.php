<?php

namespace App\Filament\Resources\ZonaTarifs\Pages;

use App\Filament\Resources\ZonaTarifs\ZonaTarifResource;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Auth;

class EditZonaTarif extends EditRecord
{
    protected static string $resource = ZonaTarifResource::class;

    public function mount(string|int $record): void
    {
        abort_unless(Auth::user()?->role === 'manajer', 403);

        parent::mount($record);
    }

}
