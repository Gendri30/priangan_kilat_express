<?php

namespace App\Filament\Resources\Kurirs\Pages;

use App\Filament\Resources\Kurirs\KurirResource;
use Filament\Actions\CreateAction;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Auth;
use App\Services\Csv\ImportKurirCsv;


class ListKurirs extends ListRecords
{
    protected static string $resource = KurirResource::class;
    protected function getHeaderActions(): array
    {
        return [
            // Tombol hijau untuk upload dan impor kurir.csv
            Action::make('importKurirCsv')
                ->label('Impor CSV Kurir')
                ->icon('heroicon-o-arrow-up-tray')
                ->color('success')
                // Hanya Admin yang bisa melihat tombol ini (Manajer tidak bisa)
                ->visible(fn (): bool => Auth::user()?->role === 'admin')
                ->modalHeading('Impor Data Kurir (kurir.csv)')
                // Form kotak upload di dalam pop-up
                ->form([
                    FileUpload::make('file_csv')
                        ->label('Pilih file kurir.csv')
                        ->disk('local')
                        ->directory('temp-csv')
                        ->required(),
                ])
                // Saat tombol Submit di pop-up diklik, panggil file service ImportKurirCsv
                ->action(function (array $data): void {
                    $pesan = ImportKurirCsv::jalankan($data['file_csv']);

                    // Tampilkan notifikasi hijau hasil impor
                    Notification::make()
                        ->title('Impor Kurir Selesai')
                        ->body($pesan)
                        ->success()
                        ->send();
                }),

            // Tombol tambah kurir satuan (+ New kurir), khusus Admin
            CreateAction::make()
                ->visible(fn (): bool => Auth::user()?->role === 'admin'),
        ];
    }
}
