<?php

namespace App\Filament\Resources\Pakets\Pages;

use App\Filament\Resources\Pakets\PaketResource;
use App\Models\Paket;
use App\Models\ZonaTarif;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreatePaket extends CreateRecord
{
    protected static string $resource = PaketResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $zona = (int) ($data['zona'] ?? 0);
        $tarifPerKg = (int) (ZonaTarif::where('zona', $zona)->value('tarif_per_kg') ?? 0);
        $beratTagih = Paket::hitungBeratTagih((float) ($data['berat_aktual'] ?? 1));
        $layanan = (string) ($data['layanan'] ?? 'Reguler');
        $isCod = ! empty($data['is_cod']);
        $nilaiCod = $isCod ? (int) ($data['nilai_cod'] ?? 0) : 0;

        $data['berat_tagih'] = $beratTagih;
        $data['tarif_dasar_snapshot'] = $tarifPerKg;
        $data['ongkos_kirim'] = Paket::hitungOngkir($beratTagih, $tarifPerKg, $layanan);
        $data['nilai_cod'] = $nilaiCod;
        $data['biaya_cod'] = Paket::hitungBiayaCod($isCod, $nilaiCod);

        $data['no_resi'] = Paket::generateNoResi();
        $data['tanggal_masuk'] = now();
        $data['status'] = 'Diterima';
        $data['jumlah_gagal'] = 0;
        $data['perlu_verifikasi'] = false;
        $data['status_setoran_cod'] = $isCod ? 'belum_disetor' : 'tidak_cod';

        return $data;
    }
    public function mount(): void
    {
        abort_unless(Auth::user()?->role === 'admin', 403);

        parent::mount();
    }

    protected function afterCreate(): void
    {
        $adminNama = Auth::user()?->name ?? 'Admin Loket';

        $this->record->catatRiwayat(
            status: 'Diterima',
            pelakuTipe: 'Admin',
            pelakuNama: $adminNama,
            catatan: 'Paket diterima di loket Priangan Kilat Express'
        );
    }
}
