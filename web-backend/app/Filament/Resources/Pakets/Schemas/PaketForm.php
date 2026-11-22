<?php

namespace App\Filament\Resources\Pakets\Schemas;

use App\Models\Paket;
use App\Models\Pelanggan;
use App\Models\ZonaTarif;
use App\Services\PusatWilayahService;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class PaketForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                // Data pengirim, penerima, dan zona
                Section::make('Data Pengirim & Penerima')
                    ->columns(2)
                    ->schema([
                        Select::make('pelanggan_id')
                            ->label('Pengirim (Pelanggan)')
                            ->options(fn () => Pelanggan::query()->pluck('nama', 'id'))
                            ->searchable()
                            ->required()
                            ->columnSpanFull()
                            ->createOptionForm([
                        TextInput::make('nama')
                            ->label('Nama Pengirim')
                            ->required(),
                        TextInput::make('no_telepon')
                            ->label('No. Telepon')
                            ->tel()
                            ->required()
                            ->minLength(10)
                            ->maxLength(14)
                            ->rules(['regex:/^08[0-9]+$/'])
                            ->validationMessages([
                                'regex' => 'Nomor telepon wajib berisi angka saja dan diawali 08.',
                            ]),
                                Textarea::make('alamat')
                                    ->label('Alamat Pengirim')
                                    ->required(),
                            ])
                            ->createOptionUsing(function (array $data): int {
                                return Pelanggan::create($data)->getKey();
                            }),

                        TextInput::make('nama_penerima')
                            ->label('Nama Penerima')
                            ->required(),

                        TextInput::make('no_telepon_penerima')
                            ->label('No. Telepon Penerima')
                            ->tel()
                            ->required()
                            ->minLength(10)
                            ->maxLength(14)
                            ->rules(['regex:/^08[0-9]+$/'])
                            ->validationMessages([
                                'regex' => 'Nomor telepon wajib berisi angka saja dan diawali 08.',
                            ]),

                        Textarea::make('alamat_penerima')
                            ->label('Alamat Lengkap Penerima')
                            ->required()
                            ->columnSpanFull(),

                        // Input kota dari API Pusat Wilayah
                        TextInput::make('kota_tujuan')
                            ->label('Kota Tujuan')
                            ->required()
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (?string $state, Set $set, Get $get) {
                                if (blank($state)) {
                                    return;
                                }

                                $hasil = PusatWilayahService::cekZonaKota($state);
                                $set('pesan_api_wilayah', $hasil['pesan']);

                                if ($hasil['sukses'] && $hasil['zona']) {
                                    $set('zona', $hasil['zona']);
                                    $set('is_zona_manual', false);
                                } else {
                                    $set('zona', null);
                                    $set('is_zona_manual', true);
                                }

                                self::hitungSemuaBiaya($set, $get);
                            }),

                        Select::make('zona')
                            ->label('Zona Tarif')
                            ->options([
                                1 => 'Zona 1 - Bandung Raya',
                                2 => 'Zona 2 - Jawa Barat Lainnya',
                                3 => 'Zona 3 - Luar Jawa Barat',
                            ])
                            ->required()
                            ->live()
                            ->disabled(fn (Get $get): bool => $get('is_zona_manual') === false && filled($get('zona')))
                            ->dehydrated()
                            ->afterStateUpdated(fn (Set $set, Get $get) => self::hitungSemuaBiaya($set, $get)),

                        Placeholder::make('info_wilayah')
                            ->label('Status Cek Wilayah')
                            ->content(fn (Get $get): string => $get('pesan_api_wilayah') ?? 'Ketik kota tujuan untuk mengecek zona otomatis.')
                            ->columnSpanFull(),
                    ]),

                Section::make('Detail Paket, Layanan & Biaya')
                    ->columns(2)
                    ->schema([
                        TextInput::make('berat_aktual')
                            ->label('Berat Aktual (kg)')
                            ->numeric()
                            ->step('0.01')
                            ->minValue(0.01)
                            ->required()
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn (Set $set, Get $get) => self::hitungSemuaBiaya($set, $get)),

                        TextInput::make('berat_tagih')
                            ->label('Berat Tagih (Pembulatan ke Atas, Min 1 kg)')
                            ->numeric()
                            ->default(1)
                            ->readOnly()
                            ->dehydrated(),

                        Radio::make('layanan')
                            ->label('Jenis Layanan')
                            ->options([
                                'Reguler' => 'Reguler (Tarif Normal)',
                                'Express' => 'Express (+50%)',
                            ])
                            ->default('Reguler')
                            ->inline()
                            ->required()
                            ->live()
                            ->afterStateUpdated(fn (Set $set, Get $get) => self::hitungSemuaBiaya($set, $get))
                            ->columnSpanFull(),

                        Checkbox::make('is_cod')
                            ->label('Layanan Bayar di Tempat (COD)')
                            ->default(false)
                            ->live()
                            ->afterStateUpdated(fn (Set $set, Get $get) => self::hitungSemuaBiaya($set, $get))
                            ->columnSpanFull(),

                        TextInput::make('nilai_cod')
                            ->label('Nilai Barang COD (Rp)')
                            ->numeric()
                            ->default(0)
                            ->minValue(1000)
                            ->visible(fn (Get $get): bool => (bool) $get('is_cod'))
                            ->required(fn (Get $get): bool => (bool) $get('is_cod'))
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn (Set $set, Get $get) => self::hitungSemuaBiaya($set, $get)),

                        TextInput::make('biaya_cod')
                            ->label('Biaya Layanan COD (2%, Min Rp2.000)')
                            ->numeric()
                            ->default(0)
                            ->readOnly()
                            ->dehydrated()
                            ->visible(fn (Get $get): bool => (bool) $get('is_cod')),

                        TextInput::make('ongkos_kirim')
                            ->label('Total Ongkos Kirim (Rp)')
                            ->numeric()
                            ->default(0)
                            ->readOnly()
                            ->dehydrated(),

                        Hidden::make('tarif_dasar_snapshot')->default(0),
                        Hidden::make('is_zona_manual')->default(false),
                        Hidden::make('pesan_api_wilayah')->dehydrated(false),
                    ]),
            ]);
    }

    protected static function hitungSemuaBiaya(Set $set, Get $get): void
    {
        $beratAktual = (float) ($get('berat_aktual') ?? 0);
        $beratTagih = Paket::hitungBeratTagih($beratAktual);
        $set('berat_tagih', $beratTagih);

        $zona = (int) ($get('zona') ?? 0);
        $tarifPerKg = $zona > 0
            ? (int) (ZonaTarif::where('zona', $zona)->value('tarif_per_kg') ?? 0)
            : 0;
        $set('tarif_dasar_snapshot', $tarifPerKg);

        $layanan = (string) ($get('layanan') ?? 'Reguler');
        $ongkir = Paket::hitungOngkir($beratTagih, $tarifPerKg, $layanan);
        $set('ongkos_kirim', $ongkir);

        $isCod = (bool) ($get('is_cod') ?? false);
        $nilaiCod = (int) ($get('nilai_cod') ?? 0);
        $biayaCod = Paket::hitungBiayaCod($isCod, $nilaiCod);
        $set('biaya_cod', $biayaCod);
    }
}
