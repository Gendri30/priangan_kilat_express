<?php

namespace App\Filament\Resources\Kurirs\Pages;

use App\Filament\Resources\Kurirs\KurirResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Auth;
use Override;

class EditKurir extends EditRecord
{
    protected static string $resource = KurirResource::class;

    public function mount(int|string $record): void{
        parent::mount($record);
    }
    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()->visible(false),
        ];
    }
}
