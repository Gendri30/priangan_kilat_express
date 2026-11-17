<?php

namespace App\Filament\Resources\Pakets\Pages;

use App\Filament\Resources\Pakets\PaketResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Auth;

class ListPakets extends ListRecords
{
    protected static string $resource = PaketResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->visible(fn (): bool => Auth::user()?->role === 'admin'),
        ];
    }
}
