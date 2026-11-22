<?php

namespace App\Services\Csv;

use App\Models\Kurir;
use App\Models\Paket;
use App\Models\Pelanggan;
use App\Models\ZonaTarif;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Class ImportPaketCsv
 * Bertugas mengimpor data paket lama (paket_historis.csv) dengan 4 aturan wajib modul:
 * 1. Melewati nomor resi kembar agar tidak ganda di database.
 * 2. Paket tanpa berat tetap masuk dengan berat tagih 1 kg + tanda 'Perlu Verifikasi'.
 * 3. Merapikan nama pengirim/penerima dan nomor HP penerima.
 * 4. Paket COD lama yang sudah 'Terkirim' otomatis ditandai 'sudah_disetor'.
 */
class ImportPaketCsv
{
    public static function jalankan(string $filePath): string
    {
        // Jaga-jaga: isi tarif dasar Zona 1-3 jika tabel zona_tarifs masih kosong
        if (ZonaTarif::count() === 0) {
            ZonaTarif::insert([
                ['zona' => 1, 'nama_zona' => 'Bandung Raya', 'tarif_per_kg' => 8000, 'created_at' => now(), 'updated_at' => now()],
                ['zona' => 2, 'nama_zona' => 'Jawa Barat lainnya', 'tarif_per_kg' => 12000, 'created_at' => now(), 'updated_at' => now()],
                ['zona' => 3, 'nama_zona' => 'Luar Jawa Barat', 'tarif_per_kg' => 20000, 'created_at' => now(), 'updated_at' => now()],
            ]);
        }

        $fullPath = Storage::disk('local')->path($filePath);
        $rows = self::bacaCsv($fullPath);

        // Hapus file salinan sementara setelah dibaca
        Storage::disk('local')->delete($filePath);

        // Ambil daftar harga per zona dari database (contoh: [1 => 8000, 2 => 12000, 3 => 20000])
        $tarifMap = ZonaTarif::pluck('tarif_per_kg', 'zona')->toArray();

        $berhasil = 0;
        $duplikat = 0;
        $perluVerifikasiCount = 0;

        foreach ($rows as $row) {
            $noResi = trim($row['no_resi'] ?? '');
            if ($noResi === '') {
                continue;
            }

            // ATURAN 1: Cegah nomor resi ganda!
            // Jika nomor resi sudah pernah masuk ke database, lewati baris duplikatnya
            if (Paket::where('no_resi', $noResi)->exists()) {
                $duplikat++;
                continue;
            }

            // Rapikan nomor telepon penerima dan nama pengirim
            $teleponPenerima = self::normalisasiTelepon($row['telepon_penerima'] ?? '') ?? '080000000000';
            $namaPengirim = self::rapikanNama($row['pengirim'] ?? 'Pelanggan Umum');

            // Cari data pelanggan pengirim; jika belum ada di tabel pelanggans, buatkan otomatis
            $pelanggan = Pelanggan::firstOrCreate(
                ['nama' => $namaPengirim],
                [
                    'no_telepon' => '080000000000',
                    'alamat' => 'Alamat Historis CSV',
                ]
            );

            // ATURAN 2: Tangani paket yang kolom beratnya kosong!
            // Tidak boleh ditolak; diisi berat tagih 1 kg dan ditandai perlu_verifikasi = true
            $beratRaw = trim((string) ($row['berat_kg'] ?? ''));
            if ($beratRaw === '' || ! is_numeric($beratRaw) || (float) $beratRaw <= 0) {
                $beratAktual = null;
                $beratTagih = 1;
                $perluVerifikasi = true;
                $perluVerifikasiCount++;
            } else {
                $beratAktual = (float) $beratRaw;
                $beratTagih = Paket::hitungBeratTagih($beratAktual); // Bulatkan ke atas (min 1 kg)
                $perluVerifikasi = false;
            }

            // Tentukan zona (1/2/3) berdasarkan kota tujuan & kunci harga tarif saat transaksi (snapshot)
            $kotaTujuan = self::rapikanNama($row['kota_tujuan'] ?? 'Bandung');
            $zona = self::tentukanZona($kotaTujuan);
            $tarifSnapshot = (int) ($tarifMap[$zona] ?? 8000);
            $layanan = strcasecmp(trim($row['layanan'] ?? ''), 'Express') === 0 ? 'Express' : 'Reguler';
            $ongkosKirim = Paket::hitungOngkir($beratTagih, $tarifSnapshot, $layanan);

            // Validasi status pengiriman sesuai daftar status resmi sistem
            $statusValid = ['Diterima', 'Transit', 'Dikirim', 'Gagal Kirim', 'Terkirim', 'Retur'];
            $status = in_array(trim($row['status'] ?? ''), $statusValid, true)
                ? trim($row['status'])
                : 'Diterima';

            // ATURAN 3: Atur status setoran COD paket lama
            $nilaiCod = (int) preg_replace('/\D/', '', (string) ($row['nilai_cod'] ?? '0'));
            $isCod = $nilaiCod > 0;
            $biayaCod = Paket::hitungBiayaCod($isCod, $nilaiCod);

            if (! $isCod) {
                $statusSetoranCod = 'tidak_cod'; // Paket biasa (non-COD)
            } elseif ($status === 'Terkirim') {
                $statusSetoranCod = 'sudah_disetor'; // Paket COD lama yang sudah Terkirim dianggap sudah setor
            } else {
                $statusSetoranCod = 'belum_disetor'; // Paket COD aktif yang belum Terkirim
            }

            // Hubungkan kode_kurir dari CSV (misal KUR-02) ke ID kurir di database
            $kodeKurir = trim($row['kode_kurir'] ?? '');
            $kurirId = $kodeKurir !== '' ? Kurir::where('kode_kurir', $kodeKurir)->value('id') : null;
            $tanggalMasuk = ! empty($row['tanggal_terima']) ? Carbon::parse($row['tanggal_terima']) : now();

            // Simpan paket bersih ke tabel 'pakets'
            $paket = Paket::create([
                'no_resi' => $noResi,
                'tanggal_masuk' => $tanggalMasuk,
                'pelanggan_id' => $pelanggan->id,
                'nama_penerima' => self::rapikanNama($row['penerima'] ?? 'Penerima'),
                'no_telepon_penerima' => $teleponPenerima,
                'alamat_penerima' => 'Alamat Tujuan ' . $kotaTujuan,
                'kota_tujuan' => $kotaTujuan,
                'zona' => $zona,
                'is_zona_manual' => false,
                'berat_aktual' => $beratAktual,
                'berat_tagih' => $beratTagih,
                'perlu_verifikasi' => $perluVerifikasi,
                'layanan' => $layanan,
                'tarif_dasar_snapshot' => $tarifSnapshot,
                'ongkos_kirim' => $ongkosKirim,
                'is_cod' => $isCod,
                'nilai_cod' => $nilaiCod,
                'biaya_cod' => $biayaCod,
                'status_setoran_cod' => $statusSetoranCod,
                'kurir_id' => $kurirId,
                'status' => $status,
                'jumlah_gagal' => $status === 'Retur' ? 2 : ($status === 'Gagal Kirim' ? 1 : 0),
            ]);

            // Catat jejak pertama kali ke tabel riwayat_status_pakets agar tampil saat cek resi
            $paket->catatRiwayat(
                $status,
                'Sistem',
                'Impor CSV Paket',
                'Data awal dari paket_historis.csv',
                $tanggalMasuk
            );

            $berhasil++;
        }

        return "{$berhasil} paket masuk, {$duplikat} resi ganda dilewati, {$perluVerifikasiCount} ditandai Perlu Verifikasi.";
    }

    private static function bacaCsv(string $fullPath): array
    {
        if (! file_exists($fullPath)) {
            return [];
        }

        $handle = fopen($fullPath, 'r');
        $header = fgetcsv($handle);
        if (! $header) {
            fclose($handle);
            return [];
        }

        $header[0] = preg_replace('/^\xEF\xBB\xBF/', '', $header[0]);
        $header = array_map('trim', $header);

        $data = [];
        while (($row = fgetcsv($handle)) !== false) {
            if (count($row) === count($header)) {
                $data[] = array_combine($header, $row);
            }
        }
        fclose($handle);

        return $data;
    }

    private static function normalisasiTelepon(string $telepon): ?string
    {
        $clean = preg_replace('/[^0-9+]/', '', trim($telepon));

        if (str_starts_with($clean, '+62')) {
            $clean = '0' . substr($clean, 3);
        } elseif (str_starts_with($clean, '62')) {
            $clean = '0' . substr($clean, 2);
        } elseif (str_starts_with($clean, '8')) {
            $clean = '0' . $clean;
        }

        return preg_match('/^0[0-9]{9,14}$/', $clean) ? $clean : null;
    }

    private static function rapikanNama(string $nama): string
    {
        return Str::title(strtolower(trim($nama)));
    }

    // Menentukan Zona 1 (Bandung Raya), Zona 2 (Jawa Barat lainnya), atau Zona 3 (Luar Jawa Barat)
    private static function tentukanZona(string $kota): int
    {
        $k = strtolower(trim($kota));

        $zona1 = ['bandung', 'cimahi', 'kabupaten bandung', 'bandung barat', 'soreang', 'katapang', 'sumedang'];
        if (in_array($k, $zona1, true)) {
            return 1;
        }

        $zona2 = [
            'bogor', 'depok', 'bekasi', 'tasikmalaya', 'cirebon', 'garut',
            'sukabumi', 'cianjur', 'karawang', 'purwakarta', 'subang',
            'indramayu', 'majalengka', 'kuningan', 'ciamis', 'banjar', 'pangandaran',
        ];
        if (in_array($k, $zona2, true)) {
            return 2;
        }

        return 3;
    }
}
