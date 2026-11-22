<?php

namespace App\Services\Csv;

use App\Models\Kurir;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

//untuk membaca kurir.csv, merapikan nomer hp, dan nama. lalu menyimpan ke tabel kurir
class ImportKurirCsv
{
    public static function jalankan(string $filePath): string
    {
        // 1. Ambil alamat lengkap file sementara yang baru di-upload Filament
        $fullPath = Storage::disk('local')->path($filePath);

        // 2. Baca isi file CSV menjadi array baris per baris
        $rows = self::bacaCsv($fullPath);

        // 3. Hapus file sementara dari folder storage agar tidak menumpuk sampah
        Storage::disk('local')->delete($filePath);

        $berhasil = 0;

        // 4. Periksa dan simpan setiap baris data kurir
        foreach ($rows as $row) {
            $kode = trim($row['kode'] ?? ''); // Contoh: KUR-01
            $telepon = self::normalisasiTelepon($row['telepon'] ?? ''); // Ubah ke format 08...

            // Lewati baris jika kode kurir kosong atau nomor HP tidak valid
            if ($kode === '' || $telepon === null) {
                continue;
            }

            // updateOrCreate: Jika kode_kurir sudah ada -> perbarui datanya (cegah duplikat)
            // Jika belum ada -> buat akun kurir baru
            Kurir::updateOrCreate(
                ['kode_kurir' => $kode],
                [
                    'nama' => Str::title(strtolower(trim($row['nama'] ?? 'Kurir'))), // Rapikan huruf kapital (Title Case)
                    'no_telepon' => $telepon,
                    'password' => 'kurir123', // Password sementara untuk login pertama di APK
                    'is_active' => true, // Status kurir aktif bekerja
                    'must_change_password' => true, // Wajib ganti password saat pertama kali login APK
                ]
            );
            $berhasil++;
        }

        return "{$berhasil} data kurir berhasil masuk (password awal: kurir123).";
    }

    // Fungsi bantuan untuk membuka file CSV dan memasangkan header dengan isinya
    private static function bacaCsv(string $fullPath): array
    {
        if (! file_exists($fullPath)) {
            return [];
        }

        $handle = fopen($fullPath, 'r'); // Buka file mode baca (read)
        $header = fgetcsv($handle); // Baca baris pertama sebagai nama kolom
        if (! $header) {
            fclose($handle);
            return [];
        }

        // Hapus karakter tersembunyi (BOM) bawaan Excel pada kolom pertama jika ada
        $header[0] = preg_replace('/^\xEF\xBB\xBF/', '', $header[0]);
        $header = array_map('trim', $header);

        $data = [];
        while (($row = fgetcsv($handle)) !== false) {
            // Pastikan jumlah kolom di baris ini sama dengan jumlah header
            if (count($row) === count($header)) {
                $data[] = array_combine($header, $row);
            }
        }
        fclose($handle); // Tutup file setelah selesai dibaca

        return $data;
    }

    // Fungsi bantuan untuk menyeragamkan format nomor HP (+62 / 62 / 8 -> 08...)
    private static function normalisasiTelepon(string $telepon): ?string
    {
        // Buang spasi, strip (-), atau huruf; sisakan angka dan tanda '+'
        $clean = preg_replace('/[^0-9+]/', '', trim($telepon));

        if (str_starts_with($clean, '+62')) {
            $clean = '0' . substr($clean, 3); // +62812... -> 0812...
        } elseif (str_starts_with($clean, '62')) {
            $clean = '0' . substr($clean, 2); // 62812... -> 0812...
        } elseif (str_starts_with($clean, '8')) {
            $clean = '0' . $clean; // 812... -> 0812...
        }

        // Pastikan hasil akhirnya diawali '0' dan panjangnya 10–15 digit angka
        return preg_match('/^0[0-9]{9,14}$/', $clean) ? $clean : null;
    }
}
