<?php

namespace App\Filament\Resources\Kurirs\Pages;

use App\Filament\Resources\Kurirs\KurirResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Auth;

class ListKurirs extends ListRecords
{
    protected static string $resource = KurirResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->visible(fn (): bool => Auth::user()?->role === 'admin'),
        ];
    }
}
