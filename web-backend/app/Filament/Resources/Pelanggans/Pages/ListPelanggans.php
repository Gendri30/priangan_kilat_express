<?php

namespace App\Filament\Resources\Pelanggans\Pages;

use App\Filament\Resources\Pelanggans\PelangganResource;
use Filament\Actions\CreateAction;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Auth;
use App\Services\Csv\ImportPelangganCsv;

class ListPelanggans extends ListRecords
{
    protected static string $resource = PelangganResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Tombol hijau untuk upload dan impor pelanggan.csv
            Action::make('importPelangganCsv')
                ->label('Impor CSV Pelanggan')
                ->icon('heroicon-o-arrow-up-tray')
                ->color('success')
                // Hanya Admin yang bisa melihat tombol ini
                ->visible(fn (): bool => Auth::user()?->role === 'admin')
                ->modalHeading('Impor Data Pelanggan (pelanggan.csv)')
                ->form([
                    FileUpload::make('file_csv')
                        ->label('Pilih file pelanggan.csv')
                        ->disk('local')
                        ->directory('temp-csv')
                        ->required(),
                ])
                // Memanggil logika pembersihan nama & nomor HP di ImportPelangganCsv
                ->action(function (array $data): void {
                    $pesan = ImportPelangganCsv::jalankan($data['file_csv']);

                    Notification::make()
                        ->title('Impor Pelanggan Selesai')
                        ->body($pesan)
                        ->success()
                        ->send();
                }),

            // Tombol tambah pelanggan satuan (+ New pelanggan), khusus Admin
            CreateAction::make()
                ->visible(fn (): bool => Auth::user()?->role === 'admin'),
        ];
    }
}
