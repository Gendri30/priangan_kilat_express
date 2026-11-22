<?php

namespace App\Filament\Resources\Kurirs\Pages;

use App\Filament\Resources\Kurirs\KurirResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;


class CreateKurir extends CreateRecord
{
    protected static string $resource = KurirResource::class;

    public function mount(): void
    {
        abort_unless(Auth::user()?->role === 'admin', 403);

        parent::mount();
    }
}

