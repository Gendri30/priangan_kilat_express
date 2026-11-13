<?php

namespace App\Filament\Resources\Pakets\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class PaketForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('no_resi')
                    ->required(),
                DateTimePicker::make('tanggal_masuk')
                    ->required(),
                TextInput::make('pelanggan_id')
                    ->required()
                    ->numeric(),
                TextInput::make('nama_penerima')
                    ->required(),
                TextInput::make('no_telepon_penerima')
                    ->tel()
                    ->required(),
                Textarea::make('alamat_penerima')
                    ->required()
                    ->columnSpanFull(),
                TextInput::make('kota_tujuan')
                    ->required(),
                TextInput::make('zona')
                    ->required()
                    ->numeric(),
                Toggle::make('is_zona_manual')
                    ->required(),
                TextInput::make('berat_aktual')
                    ->numeric(),
                TextInput::make('berat_tagih')
                    ->required()
                    ->numeric()
                    ->default(1),
                Toggle::make('perlu_verifikasi')
                    ->required(),
                Select::make('layanan')
                    ->options(['Reguler' => 'Reguler', 'Express' => 'Express'])
                    ->default('Reguler')
                    ->required(),
                TextInput::make('tarif_dasar_snapshot')
                    ->required()
                    ->numeric(),
                TextInput::make('ongkos_kirim')
                    ->required()
                    ->numeric(),
                Toggle::make('is_cod')
                    ->required(),
                TextInput::make('nilai_cod')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('biaya_cod')
                    ->required()
                    ->numeric()
                    ->default(0),
                Select::make('status_setoran_cod')
                    ->options([
            'belum_disetor' => 'Belum disetor',
            'sudah_disetor' => 'Sudah disetor',
            'tidak_cod' => 'Tidak cod',
        ])
                    ->default('tidak_cod')
                    ->required(),
                TextInput::make('kurir_id')
                    ->numeric(),
                Select::make('status')
                    ->options([
            'Diterima' => 'Diterima',
            'Transit' => 'Transit',
            'Dikirim' => 'Dikirim',
            'Gagal Kirim' => 'Gagal kirim',
            'Terkirim' => 'Terkirim',
            'Retur' => 'Retur',
        ])
                    ->default('Diterima')
                    ->required(),
                TextInput::make('jumlah_gagal')
                    ->required()
                    ->numeric()
                    ->default(0),
            ]);
    }
}
