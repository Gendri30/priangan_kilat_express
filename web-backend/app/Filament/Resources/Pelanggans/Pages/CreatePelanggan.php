<?php

namespace App\Filament\Resources\Pelanggans\Pages;

use App\Filament\Resources\Pelanggans\PelangganResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreatePelanggan extends CreateRecord
{
    protected static string $resource = PelangganResource::class;

    public function mount():void{
        abort_unless(Auth::user()?->role === 'admin', 403, 'manajer tidak punya akses');
        parent::mount();
    }
}
