<?php

namespace App\Filament\Resources\Pakets\Pages;

use App\Filament\Resources\Pakets\PaketResource;
use Filament\Actions\CreateAction;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Auth;
use App\Services\Csv\ImportPaketCsv;

class ListPakets extends ListRecords
{
    protected static string $resource = PaketResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Tombol hijau untuk upload dan impor paket_historis.csv
            Action::make('importPaketCsv')
                ->label('Impor CSV Paket')
                ->icon('heroicon-o-arrow-up-tray')
                ->color('success')
                // Hanya Admin yang bisa melihat tombol ini
                ->visible(fn (): bool => Auth::user()?->role === 'admin')
                ->modalHeading('Impor Paket Historis (paket_historis.csv)')
                ->form([
                    FileUpload::make('file_csv')
                        ->label('Pilih file paket_historis.csv')
                        ->disk('local')
                        ->directory('temp-csv')
                        ->required(),
                ])
                // Memanggil logika pembersihan paket lama di ImportPaketCsv
                ->action(function (array $data): void {
                    $pesan = ImportPaketCsv::jalankan($data['file_csv']);

                    Notification::make()
                        ->title('Impor Paket Selesai')
                        ->body($pesan)
                        ->success()
                        ->send();
                }),

            // Tombol input transaksi paket baru (+ New paket), khusus Admin
            CreateAction::make()
                ->visible(fn (): bool => Auth::user()?->role === 'admin'),
        ];
    }
}
