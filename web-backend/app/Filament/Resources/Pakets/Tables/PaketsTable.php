<?php

namespace App\Filament\Resources\Pakets\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PaketsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('no_resi')
                    ->searchable(),
                TextColumn::make('tanggal_masuk')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('pelanggan_id')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('nama_penerima')
                    ->searchable(),
                TextColumn::make('no_telepon_penerima')
                    ->searchable(),
                TextColumn::make('kota_tujuan')
                    ->searchable(),
                TextColumn::make('zona')
                    ->numeric()
                    ->sortable(),
                IconColumn::make('is_zona_manual')
                    ->boolean(),
                TextColumn::make('berat_aktual')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('berat_tagih')
                    ->numeric()
                    ->sortable(),
                IconColumn::make('perlu_verifikasi')
                    ->boolean(),
                TextColumn::make('layanan')
                    ->badge(),
                TextColumn::make('tarif_dasar_snapshot')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('ongkos_kirim')
                    ->numeric()
                    ->sortable(),
                IconColumn::make('is_cod')
                    ->boolean(),
                TextColumn::make('nilai_cod')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('biaya_cod')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('status_setoran_cod')
                    ->badge(),
                TextColumn::make('kurir_id')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('status')
                    ->badge(),
                TextColumn::make('jumlah_gagal')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
