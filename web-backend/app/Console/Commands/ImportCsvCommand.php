<?php

namespace App\Console\Commands;

use App\Models\Kurir;
use App\Models\Paket;
use App\Models\Pelanggan;
use App\Models\ZonaTarif;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class ImportCsvCommand extends Command
{
    // Nama perintah yang diketik di terminal: php artisan app:import-csv
    protected $signature = 'app:import-csv';

    // Deskripsi singkat perintah
    protected $description = 'Impor dan bersihkan data awal CSV (kurir, pelanggan, paket_historis)';

    // Fungsi utama yang otomatis jalan saat perintah dipanggil
    public function handle(): int
    {
        // Lokasi folder penyimpanan file CSV Data Awal
        $basePath = storage_path('app/Varian_B_Ekspedisi/Data_Awal');

        // Jika foldernya belum ada, tampilkan pesan error dan hentikan
        if (! is_dir($basePath)) {
            $this->error("Folder tidak ditemukan: {$basePath}");
            return self::FAILURE;
        }

        // Jaga-jaga jika tabel zona_tarifs masih kosong, isi tarif awal dulu
        // agar perhitungan ongkir paket historis di bawah tidak error
        if (ZonaTarif::count() === 0) {
            ZonaTarif::insert([
                ['zona' => 1, 'nama_zona' => 'Bandung Raya', 'tarif_per_kg' => 8000, 'created_at' => now(), 'updated_at' => now()],
                ['zona' => 2, 'nama_zona' => 'Jawa Barat lainnya', 'tarif_per_kg' => 12000, 'created_at' => now(), 'updated_at' => now()],
                ['zona' => 3, 'nama_zona' => 'Luar Jawa Barat', 'tarif_per_kg' => 20000, 'created_at' => now(), 'updated_at' => now()],
            ]);
        }

        // Jalankan proses impor berurutan: Kurir -> Pelanggan -> Paket Historis
        $this->imporKurir($basePath . '/kurir.csv');
        $this->imporPelanggan($basePath . '/pelanggan.csv');
        $this->imporPaketHistoris($basePath . '/paket_historis.csv');

        $this->info('Proses impor seluruh CSV Data Awal selesai.');
        return self::SUCCESS;
    }

    /**
     * 1. Fungsi untuk mengimpor data dari kurir.csv
     */
    private function imporKurir(string $filePath): void
    {
        $rows = $this->bacaCsv($filePath);
        $berhasil = 0;

        foreach ($rows as $row) {
            $kode = trim($row['kode'] ?? '');
            // Bersihkan format nomor telepon kurir (+62/62 jadi 08)
            $telepon = $this->normalisasiTelepon($row['telepon'] ?? '');

            // Jika kode kosong atau nomor telepon tidak valid, lewati baris ini
            if ($kode === '' || $telepon === null) {
                continue;
            }

            // Simpan atau perbarui berdasarkan kode_kurir (misal KUR-01)
            Kurir::updateOrCreate(
                ['kode_kurir' => $kode],
                [
                    'nama' => $this->rapikanNama($row['nama'] ?? 'Kurir'),
                    'no_telepon' => $telepon,
                    'password' => 'kurir123', // Password sementara (otomatis di-hash di Model)
                    'is_active' => true, // Status kurir aktif
                    'must_change_password' => true, // Wajib ganti password saat login pertama di APK
                ]
            );
            $berhasil++;
        }

        $this->line("- kurir.csv: {$berhasil} kurir berhasil diimpor (password awal: kurir123).");
    }

    /**
     * 2. Fungsi untuk mengimpor data dari pelanggan.csv
     */
    private function imporPelanggan(string $filePath): void
    {
        $rows = $this->bacaCsv($filePath);
        $berhasil = 0;

        foreach ($rows as $row) {
            // Rapikan huruf nama (contoh: "RINA SANTOSO" -> "Rina Santoso")
            $nama = $this->rapikanNama($row['nama'] ?? '');
            // Rapikan nomor telepon (contoh: "+62882..." -> "0882...")
            $telepon = $this->normalisasiTelepon($row['telepon'] ?? '');

            // Jika nama kosong atau nomor telepon tidak valid, lewati baris ini
            if ($nama === '' || $telepon === null) {
                continue;
            }

            // Gabungkan kolom alamat dan kota agar tersimpan lengkap
            $alamat = trim($row['alamat'] ?? '');
            $kota = trim($row['kota'] ?? '');
            $alamatLengkap = $kota !== '' ? "{$alamat}, {$kota}" : $alamat;

            // Simpan pelanggan ke database (jika nama & no_telepon sudah ada, cukup update alamatnya)
            Pelanggan::updateOrCreate(
                ['nama' => $nama, 'no_telepon' => $telepon],
                ['alamat' => $alamatLengkap]
            );
            $berhasil++;
        }

        $this->line("- pelanggan.csv: {$berhasil} pelanggan berhasil dirapikan & disimpan.");
    }

    /**
     * 3. Fungsi untuk mengimpor data dari paket_historis.csv
     */
    private function imporPaketHistoris(string $filePath): void
    {
        $rows = $this->bacaCsv($filePath);
        // Ambil daftar harga per kg tiap zona: [1 => 8000, 2 => 12000, 3 => 20000]
        $tarifMap = ZonaTarif::pluck('tarif_per_kg', 'zona')->toArray();

        $berhasil = 0;
        $duplikat = 0;
        $perluVerifikasiCount = 0;

        foreach ($rows as $row) {
            $noResi = trim($row['no_resi'] ?? '');
            if ($noResi === '') {
                continue;
            }

            // ATURAN 1: Cek apakah nomor resi sudah ada di database (resi kembar)
            // Jika sudah ada, lewati baris ini agar tidak dobel
            if (Paket::where('no_resi', $noResi)->exists()) {
                $duplikat++;
                continue;
            }

            // Bersihkan nomor HP penerima
            $teleponPenerima = $this->normalisasiTelepon($row['telepon_penerima'] ?? '') ?? '080000000000';

            // Cari data pelanggan (pengirim) berdasarkan namanya; jika belum ada di tabel pelanggans, buat baru
            $namaPengirim = $this->rapikanNama($row['pengirim'] ?? 'Pelanggan Umum');
            $pelanggan = Pelanggan::firstOrCreate(
                ['nama' => $namaPengirim],
                [
                    'no_telepon' => '080000000000',
                    'alamat' => 'Alamat Historis CSV',
                ]
            );

            // ATURAN 2: Cek apakah kolom berat_kg kosong di CSV
            $beratRaw = trim((string) ($row['berat_kg'] ?? ''));
            if ($beratRaw === '' || ! is_numeric($beratRaw) || (float) $beratRaw <= 0) {
                // Jika kosong: berat aktual dikosongkan (null), tagihan dihitung minimal 1 kg,
                // dan ditandai perlu_verifikasi = true supaya muncul label peringatan di Web Admin
                $beratAktual = null;
                $beratTagih = 1;
                $perluVerifikasi = true;
                $perluVerifikasiCount++;
            } else {
                // Jika ada angkanya: bulatkan ke atas (contoh 0.4 kg -> 1 kg, 1.2 kg -> 2 kg)
                $beratAktual = (float) $beratRaw;
                $beratTagih = Paket::hitungBeratTagih($beratAktual);
                $perluVerifikasi = false;
            }

            // Tentukan zona (1/2/3) dari nama kota tujuan dan hitung ongkos kirimnya
            $kotaTujuan = $this->rapikanNama($row['kota_tujuan'] ?? 'Bandung');
            $zona = $this->tentukanZona($kotaTujuan);
            $tarifSnapshot = (int) ($tarifMap[$zona] ?? 8000);
            $layanan = strcasecmp(trim($row['layanan'] ?? ''), 'Express') === 0 ? 'Express' : 'Reguler';
            $ongkosKirim = Paket::hitungOngkir($beratTagih, $tarifSnapshot, $layanan);

            // Validasi nama status paket
            $statusValid = ['Diterima', 'Transit', 'Dikirim', 'Gagal Kirim', 'Terkirim', 'Retur'];
            $status = in_array(trim($row['status'] ?? ''), $statusValid, true)
                ? trim($row['status'])
                : 'Diterima';

            // ATURAN 3: Hitung COD & Status Setoran COD paket lama
            $nilaiCod = (int) preg_replace('/\D/', '', (string) ($row['nilai_cod'] ?? '0'));
            $isCod = $nilaiCod > 0;
            $biayaCod = Paket::hitungBiayaCod($isCod, $nilaiCod);

            if (! $isCod) {
                $statusSetoranCod = 'tidak_cod';
            } elseif ($status === 'Terkirim') {
                // Sesuai soal: paket historis yang sudah Terkirim dianggap uang COD-nya sudah disetor
                $statusSetoranCod = 'sudah_disetor';
            } else {
                $statusSetoranCod = 'belum_disetor';
            }

            // Cari ID kurir dari kode_kurir (misal KUR-02)
            $kodeKurir = trim($row['kode_kurir'] ?? '');
            $kurirId = $kodeKurir !== '' ? Kurir::where('kode_kurir', $kodeKurir)->value('id') : null;
            $tanggalMasuk = ! empty($row['tanggal_terima']) ? Carbon::parse($row['tanggal_terima']) : now();

            // Simpan data paket ke tabel pakets
            $paket = Paket::create([
                'no_resi' => $noResi,
                'tanggal_masuk' => $tanggalMasuk,
                'pelanggan_id' => $pelanggan->id,
                'nama_penerima' => $this->rapikanNama($row['penerima'] ?? 'Penerima'),
                'no_telepon_penerima' => $teleponPenerima,
                'alamat_penerima' => 'Alamat Tujuan ' . $kotaTujuan,
                'kota_tujuan' => $kotaTujuan,
                'zona' => $zona,
                'is_zona_manual' => false,
                'berat_aktual' => $beratAktual,
                'berat_tagih' => $beratTagih,
                'perlu_verifikasi' => $perluVerifikasi,
                'layanan' => $layanan,
                'tarif_dasar_snapshot' => $tarifSnapshot, // Kunci harga tarif saat itu
                'ongkos_kirim' => $ongkosKirim,
                'is_cod' => $isCod,
                'nilai_cod' => $nilaiCod,
                'biaya_cod' => $biayaCod,
                'status_setoran_cod' => $statusSetoranCod,
                'kurir_id' => $kurirId,
                'status' => $status,
                'jumlah_gagal' => $status === 'Retur' ? 2 : ($status === 'Gagal Kirim' ? 1 : 0),
            ]);

            // Buat 1 catatan awal di tabel riwayat_status_pakets agar bisa dilacak di Cek Resi
            $paket->catatRiwayat(
                $status,
                'Sistem',
                'Impor CSV Historis',
                'Data awal dari paket_historis.csv',
                $tanggalMasuk
            );

            $berhasil++;
        }

        $this->line("- paket_historis.csv: {$berhasil} diimpor, {$duplikat} resi kembar dilewati, {$perluVerifikasiCount} ditandai Perlu Verifikasi.");
    }

    /**
     * Fungsi pembantu untuk membaca file CSV dan mengubahnya menjadi array PHP
     */
    private function bacaCsv(string $filePath): array
    {
        if (! file_exists($filePath)) {
            return [];
        }

        $handle = fopen($filePath, 'r');
        // Ambil baris pertama sebagai nama kolom (header)
        $header = fgetcsv($handle);
        if (! $header) {
            fclose($handle);
            return [];
        }

        // Hapus karakter tersembunyi (BOM) di awal file CSV jika ada
        $header[0] = preg_replace('/^\xEF\xBB\xBF/', '', $header[0]);
        $header = array_map('trim', $header);

        $data = [];
        // Baca baris demi baris sampai habis
        while (($row = fgetcsv($handle)) !== false) {
            if (count($row) === count($header)) {
                // Pasangkan nama kolom dengan isinya
                $data[] = array_combine($header, $row);
            }
        }
        fclose($handle);

        return $data;
    }

    /**
     * Fungsi pembantu untuk menyeragamkan nomor HP (+62 / 62 -> 08...)
     */
    private function normalisasiTelepon(string $telepon): ?string
    {
        // Buang semua karakter selain angka dan tanda +
        $clean = preg_replace('/[^0-9+]/', '', trim($telepon));

        // Ubah awalan +62 atau 62 menjadi 0
        if (str_starts_with($clean, '+62')) {
            $clean = '0' . substr($clean, 3);
        } elseif (str_starts_with($clean, '62')) {
            $clean = '0' . substr($clean, 2);
        } elseif (str_starts_with($clean, '8')) {
            $clean = '0' . $clean;
        }

        // Validasi wajib diawali 0 dan panjangnya minimal 10 digit (maks 15 digit)
        return preg_match('/^0[0-9]{9,14}$/', $clean) ? $clean : null;
    }

    /**
     * Fungsi pembantu untuk merapikan huruf nama menjadi Title Case
     */
    private function rapikanNama(string $nama): string
    {
        // Ubah ke huruf kecil semua dulu, baru besarkan huruf depan tiap kata
        return Str::title(strtolower(trim($nama)));
    }

    /**
     * Fungsi pembantu menentukan Zona 1, 2, atau 3 untuk data CSV historis
     */
    private function tentukanZona(string $kota): int
    {
        $k = strtolower(trim($kota));

        // Zona 1: Bandung Raya
        $zona1 = ['bandung', 'cimahi', 'kabupaten bandung', 'bandung barat', 'soreang', 'katapang', 'sumedang'];
        if (in_array($k, $zona1, true)) {
            return 1;
        }

        // Zona 2: Kota/Kabupaten Jawa Barat lainnya
        $zona2 = [
            'bogor', 'depok', 'bekasi', 'tasikmalaya', 'cirebon', 'garut',
            'sukabumi', 'cianjur', 'karawang', 'purwakarta', 'subang',
            'indramayu', 'majalengka', 'kuningan', 'ciamis', 'banjar', 'pangandaran',
        ];
        if (in_array($k, $zona2, true)) {
            return 2;
        }

        // Zona 3: Luar Jawa Barat
        return 3;
    }
}
