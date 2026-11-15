<?php

namespace App\Filament\Resources\Kurirs\Pages;

use App\Filament\Resources\Kurirs\KurirResource;
use App\Models\Kurir;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Auth;

class EditKurir extends EditRecord
{
    protected static string $resource = KurirResource::class;

    public function mount(int|string $record): void
    {
        abort_unless(Auth::user()?->role === 'admin', 403, 'Hanya Admin yang dapat mengubah data kurir.');

        parent::mount($record);
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->visible(fn (): bool => Auth::user()?->role === 'admin')
                ->before(function (DeleteAction $action, Kurir $record): void {
                    if ($record->pakets()->exists()) {
                        Notification::make()
                            ->title('Kurir tidak dapat dihapus')
                            ->body('Kurir sudah punya riwayat paket/COD. Matikan saja saklar Is active.')
                            ->danger()
                            ->send();

                        $action->halt();
                    }
                }),
        ];
    }
}
