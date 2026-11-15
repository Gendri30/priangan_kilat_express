<?php

namespace App\Filament\Resources\Pelanggans\Pages;

use App\Filament\Resources\Pelanggans\PelangganResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Auth;

class EditPelanggan extends EditRecord
{
    protected static string $resource = PelangganResource::class;

    public function mount(int|string $record): void{
        abort_unless(Auth::user()?->role === 'admin', 403, 'manajer tidak punya akses');
        parent::mount($record);
    }
    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()->visible(false),
        ];
    }
}
