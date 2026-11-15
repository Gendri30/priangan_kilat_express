<?php

namespace App\Filament\Resources\Pakets\Pages;

use App\Filament\Resources\Pakets\PaketResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPaket extends EditRecord
{
    protected static string $resource = PaketResource::class;

    public function mount(int|string $record): void{
        abort(403, 'data transaksi tidak dapat di ubah');
    }

}
