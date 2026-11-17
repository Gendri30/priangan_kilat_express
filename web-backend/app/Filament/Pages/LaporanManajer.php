<?php

namespace App\Filament\Pages;

use App\Models\Kurir;
use App\Models\Paket;
use App\Models\ZonaTarif;
use BackedEnum;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;

class LaporanManajer extends Page
{
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-chart-bar';

    protected static ?string $navigationLabel = 'Laporan Manajer';

    protected static ?string $title = 'Laporan Operasional & Keuangan';

    protected static ?int $navigationSort = 10;

    protected string $view = 'filament.pages.laporan-manajer';

    public string $tanggalMulai = '';

    public string $tanggalSelesai = '';

    public static function canAccess(): bool
    {
        return Auth::user()?->role === 'manajer';
    }

    public function mount(): void
    {
        abort_unless(Auth::user()?->role === 'manajer', 403, 'Hanya Manajer yang dapat mengakses halaman laporan.');

        $this->tanggalMulai = now()->startOfMonth()->toDateString();
        $this->tanggalSelesai = now()->toDateString();
    }

    public function resetFilter(): void
    {
        $this->tanggalMulai = now()->startOfMonth()->toDateString();
        $this->tanggalSelesai = now()->toDateString();
    }

    // 1. Laporan Pengiriman per Status
    public function getLaporanPengirimanProperty(): array
    {
        $daftarStatus = ['Diterima', 'Transit', 'Dikirim', 'Terkirim', 'Gagal Kirim', 'Retur'];
        $hasil = [];
        $totalSemua = 0;

        foreach ($daftarStatus as $status) {
            $jumlah = Paket::query()
                ->when($this->tanggalMulai, fn ($q) => $q->whereDate('tanggal_masuk', '>=', $this->tanggalMulai))
                ->when($this->tanggalSelesai, fn ($q) => $q->whereDate('tanggal_masuk', '<=', $this->tanggalSelesai))
                ->where('status', $status)
                ->count();

            $hasil[] = [
                'status' => $status,
                'jumlah' => $jumlah,
            ];
            $totalSemua += $jumlah;
        }

        return [
            'rows' => $hasil,
            'total' => $totalSemua,
        ];
    }

    // 2. Laporan Pendapatan per Zona (Ongkir + Biaya COD berdasarkan tanggal_masuk)
    public function getLaporanPendapatanZonaProperty(): array
    {
        $zonas = ZonaTarif::orderBy('zona')->get();
        $hasil = [];
        $grandPaket = 0;
        $grandOngkir = 0;
        $grandBiayaCod = 0;

        foreach ($zonas as $z) {
            $query = Paket::query()
                ->when($this->tanggalMulai, fn ($q) => $q->whereDate('tanggal_masuk', '>=', $this->tanggalMulai))
                ->when($this->tanggalSelesai, fn ($q) => $q->whereDate('tanggal_masuk', '<=', $this->tanggalSelesai))
                ->where('zona', $z->zona);

            $jumlahPaket = (clone $query)->count();
            $totalOngkir = (int) (clone $query)->sum('ongkos_kirim');
            $totalBiayaCod = (int) (clone $query)->sum('biaya_cod');

            $hasil[] = [
                'zona' => 'Zona ' . $z->zona . ' - ' . $z->nama_zona,
                'jumlah_paket' => $jumlahPaket,
                'ongkos_kirim' => $totalOngkir,
                'biaya_cod' => $totalBiayaCod,
                'total_pendapatan' => $totalOngkir + $totalBiayaCod,
            ];

            $grandPaket += $jumlahPaket;
            $grandOngkir += $totalOngkir;
            $grandBiayaCod += $totalBiayaCod;
        }

        return [
            'rows' => $hasil,
            'grand_paket' => $grandPaket,
            'grand_ongkir' => $grandOngkir,
            'grand_biaya_cod' => $grandBiayaCod,
            'grand_total' => $grandOngkir + $grandBiayaCod,
        ];
    }

    // 3. Laporan Rekap COD per Kurir (Paket COD berstatus Terkirim)
    public function getLaporanCodKurirProperty(): array
    {
        $kurirs = Kurir::orderBy('kode_kurir')->get();
        $hasil = [];
        $totalBelumDisetorSemua = 0;
        $totalSudahDisetorSemua = 0;

        foreach ($kurirs as $kurir) {
            $baseQuery = Paket::query()
                ->when($this->tanggalMulai, fn ($q) => $q->whereDate('tanggal_masuk', '>=', $this->tanggalMulai))
                ->when($this->tanggalSelesai, fn ($q) => $q->whereDate('tanggal_masuk', '<=', $this->tanggalSelesai))
                ->where('kurir_id', $kurir->id)
                ->where('is_cod', true)
                ->where('status', 'Terkirim');

            $jumlahPaketCod = (clone $baseQuery)->count();

            $belumDisetor = (int) (clone $baseQuery)
                ->where('status_setoran_cod', 'belum_disetor')
                ->sum('nilai_cod');

            $sudahDisetor = (int) (clone $baseQuery)
                ->where('status_setoran_cod', 'sudah_disetor')
                ->sum('nilai_cod');

            $hasil[] = [
                'kode_kurir' => $kurir->kode_kurir,
                'nama_kurir' => $kurir->nama,
                'jumlah_paket' => $jumlahPaketCod,
                'belum_disetor' => $belumDisetor,
                'sudah_disetor' => $sudahDisetor,
                'total_cod' => $belumDisetor + $sudahDisetor,
            ];

            $totalBelumDisetorSemua += $belumDisetor;
            $totalSudahDisetorSemua += $sudahDisetor;
        }

        return [
            'rows' => $hasil,
            'total_belum_disetor' => $totalBelumDisetorSemua,
            'total_sudah_disetor' => $totalSudahDisetorSemua,
            'grand_total_cod' => $totalBelumDisetorSemua + $totalSudahDisetorSemua,
        ];
    }
}
