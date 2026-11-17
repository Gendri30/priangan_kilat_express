<?php

namespace App\Filament\Resources\Pakets\Tables;

use App\Models\Kurir;
use App\Models\Paket;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class PaketsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('no_resi')
                    ->label('No. Resi')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Diterima' => 'gray',
                        'Transit' => 'info',
                        'Dikirim' => 'warning',
                        'Terkirim' => 'success',
                        'Gagal Kirim' => 'danger',
                        'Retur' => 'danger',
                        default => 'gray',
                    }),

                TextColumn::make('pelanggan.nama')
                    ->label('Pengirim')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('nama_penerima')
                    ->label('Penerima')
                    ->searchable(),

                TextColumn::make('kota_tujuan')
                    ->label('Tujuan')
                    ->description(fn (Paket $record): string => 'Zona ' . $record->zona . ($record->is_zona_manual ? ' (Manual)' : ''))
                    ->searchable(),

                TextColumn::make('layanan')
                    ->label('Layanan')
                    ->badge()
                    ->color(fn (string $state): string => $state === 'Express' ? 'warning' : 'primary'),

                TextColumn::make('berat_tagih')
                    ->label('Berat (kg)')
                    ->suffix(' kg')
                    ->sortable(),

                IconColumn::make('perlu_verifikasi')
                    ->label('Perlu Verifikasi')
                    ->boolean(),

                TextColumn::make('ongkos_kirim')
                    ->label('Ongkir')
                    ->money('IDR', locale: 'id')
                    ->sortable(),

                IconColumn::make('is_cod')
                    ->label('COD')
                    ->boolean(),

                TextColumn::make('nilai_cod')
                    ->label('Nilai COD')
                    ->money('IDR', locale: 'id')
                    ->sortable(),

                TextColumn::make('status_setoran_cod')
                    ->label('Setoran COD')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'belum_disetor' => 'Belum Disetor',
                        'sudah_disetor' => 'Sudah Disetor',
                        default => 'Non-COD',
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'belum_disetor' => 'warning',
                        'sudah_disetor' => 'success',
                        default => 'gray',
                    }),

                TextColumn::make('kurir.kode_kurir')
                    ->label('Kurir')
                    ->placeholder('-')
                    ->description(fn (Paket $record): ?string => $record->kurir?->nama),

                TextColumn::make('jumlah_gagal')
                    ->label('Gagal')
                    ->suffix('x')
                    ->sortable(),

                TextColumn::make('tanggal_masuk')
                    ->label('Tanggal Masuk')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Status Paket')
                    ->options([
                        'Diterima' => 'Diterima',
                        'Transit' => 'Transit',
                        'Dikirim' => 'Dikirim',
                        'Terkirim' => 'Terkirim',
                        'Gagal Kirim' => 'Gagal Kirim',
                        'Retur' => 'Retur',
                    ]),

                SelectFilter::make('status_setoran_cod')
                    ->label('Status Setoran COD')
                    ->options([
                        'belum_disetor' => 'Belum Disetor',
                        'sudah_disetor' => 'Sudah Disetor',
                        'tidak_cod' => 'Non-COD',
                    ]),

                TernaryFilter::make('perlu_verifikasi')
                    ->label('Perlu Verifikasi Berat'),
            ])
            ->recordActions([
                // 1. Diterima -> Transit (Khusus Admin)
                Action::make('ubah_transit')
                    ->label('Ubah ke Transit')
                    ->icon('heroicon-o-arrow-right-circle')
                    ->color('info')
                    ->requiresConfirmation()
                    ->visible(fn (Paket $record): bool => Auth::user()?->role === 'admin' && $record->status === 'Diterima')
                    ->action(function (Paket $record): void {
                        $record->update(['status' => 'Transit']);

                        $record->catatRiwayat(
                            status: 'Transit',
                            pelakuTipe: 'Admin',
                            pelakuNama: Auth::user()?->name ?? 'Admin Loket',
                            catatan: 'Paket masuk ke proses sortir / transit'
                        );

                        Notification::make()
                            ->title('Status paket diubah ke Transit')
                            ->success()
                            ->send();
                    }),

                // 2. Transit / Gagal Kirim (< 2x) -> Tugaskan Kurir -> Dikirim (Maks. 20 Paket)
                Action::make('tugaskan_kurir')
                    ->label(fn (Paket $record): string => $record->status === 'Gagal Kirim' ? 'Kirim Ulang' : 'Tugaskan Kurir')
                    ->icon('heroicon-o-truck')
                    ->color('primary')
                    ->visible(fn (Paket $record): bool => Auth::user()?->role === 'admin'
                        && ($record->status === 'Transit' || ($record->status === 'Gagal Kirim' && $record->jumlah_gagal < 2)))
                    ->form([
                        Select::make('kurir_id')
                            ->label('Pilih Kurir Aktif (Maks. 20 Paket Dikirim)')
                            ->options(fn () => Kurir::where('is_active', true)
                                ->get()
                                ->mapWithKeys(fn (Kurir $kurir) => [
                                    $kurir->id => "{$kurir->kode_kurir} - {$kurir->nama} (Bawaan: {$kurir->countPaketDikirim()}/20)",
                                ]))
                            ->searchable()
                            ->required(),
                    ])
                    ->action(function (Paket $record, array $data): void {
                        $kurir = Kurir::find($data['kurir_id']);

                        if (! $kurir || ! $kurir->is_active) {
                            Notification::make()
                                ->title('Kurir tidak aktif atau tidak ditemukan')
                                ->danger()
                                ->send();

                            return;
                        }

                        if (! $kurir->bisaDitugaskan()) {
                            Notification::make()
                                ->title('Penugasan Ditolak')
                                ->body("Kurir {$kurir->kode_kurir} ({$kurir->nama}) sudah membawa maksimal 20 paket berstatus Dikirim.")
                                ->danger()
                                ->send();

                            return;
                        }

                        $record->update([
                            'kurir_id' => $kurir->id,
                            'status' => 'Dikirim',
                        ]);

                        $record->catatRiwayat(
                            status: 'Dikirim',
                            pelakuTipe: 'Admin',
                            pelakuNama: Auth::user()?->name ?? 'Admin Loket',
                            catatan: "Paket ditugaskan ke kurir {$kurir->kode_kurir} ({$kurir->nama})"
                        );

                        Notification::make()
                            ->title('Kurir Berhasil Ditugaskan')
                            ->body("Status paket berubah menjadi Dikirim oleh {$kurir->kode_kurir}.")
                            ->success()
                            ->send();
                    }),

                // 3. Gagal Kirim (2x) -> Retur (Final)
                Action::make('ubah_retur')
                    ->label('Ubah ke Retur')
                    ->icon('heroicon-o-arrow-uturn-left')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(fn (Paket $record): bool => Auth::user()?->role === 'admin'
                        && $record->status === 'Gagal Kirim'
                        && $record->jumlah_gagal >= 2)
                    ->action(function (Paket $record): void {
                        $record->update(['status' => 'Retur']);

                        $record->catatRiwayat(
                            status: 'Retur',
                            pelakuTipe: 'Admin',
                            pelakuNama: Auth::user()?->name ?? 'Admin Loket',
                            catatan: 'Paket diretur setelah gagal kirim sebanyak 2 kali'
                        );

                        Notification::make()
                            ->title('Status paket diubah menjadi Retur')
                            ->warning()
                            ->send();
                    }),

                // 4. Catat Setoran COD (Khusus Paket COD Terkirim yang Belum Disetor)
                Action::make('catat_setoran_cod')
                    ->label('Catat Setoran COD')
                    ->icon('heroicon-o-banknotes')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Konfirmasi Setoran Uang COD')
                    ->modalDescription(fn (Paket $record): string => 'Catat setoran penuh sebesar Rp ' . number_format($record->nilai_cod, 0, ',', '.') . ' untuk resi ' . $record->no_resi . '?')
                    ->visible(fn (Paket $record): bool => Auth::user()?->role === 'admin'
                        && $record->is_cod
                        && $record->status === 'Terkirim'
                        && $record->status_setoran_cod === 'belum_disetor')
                    ->action(function (Paket $record): void {
                        $record->update([
                            'status_setoran_cod' => 'sudah_disetor',
                        ]);

                        Notification::make()
                            ->title('Setoran COD Berhasil Dicatat')
                            ->body('Status kas COD diperbarui menjadi Sudah Disetor.')
                            ->success()
                            ->send();
                    }),
            ]);
    }
}
