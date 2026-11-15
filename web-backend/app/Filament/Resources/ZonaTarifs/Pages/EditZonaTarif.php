<?php

namespace App\Filament\Resources\ZonaTarifs\Pages;

use App\Filament\Resources\ZonaTarifs\ZonaTarifResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;

class EditZonaTarif extends EditRecord
{
    protected static string $resource = ZonaTarifResource::class;

    public function mount(int|string $record): void{
        abort_unless(Auth::user()?->role === 'manajer', 403, 'Hanya Manajer yang dapat mengubah tarif zona.');
        parent::mount($record);
    }

    protected function getHeaderActions(): array{
        return [
            DeleteAction::make()->visible(false),
        ];
    }
}
