<?php

namespace App\Filament\Resources\Pelanggans\Pages;

use App\Filament\Resources\Pelanggans\PelangganResource;
use App\Models\Pelanggan;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Auth;

class EditPelanggan extends EditRecord
{
    protected static string $resource = PelangganResource::class;

    public function mount(int|string $record): void
    {
        abort_unless(Auth::user()?->role === 'admin', 403, 'manajer tidak punya akses');
        parent::mount($record);
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->visible(fn (): bool => Auth::user()?->role === 'admin')
                ->before(function (DeleteAction $action, Pelanggan $record): void {
                    if ($record->pakets()->exists()) {
                        Notification::make()
                            ->title('Pelanggan tidak dapat dihapus')
                            ->body('Pelanggan ini sudah memiliki riwayat transaksi paket.')
                            ->danger()
                            ->send();

                        $action->halt();
                    }
                }),
        ];
    }
}
