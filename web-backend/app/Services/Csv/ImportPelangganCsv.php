<?php

namespace App\Services\Csv;

use App\Models\Pelanggan;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Class ImportPelangganCsv
 * Bertugas membersihkan data kotor di pelanggan.csv (huruf nama acak & format HP)
 * lalu menyimpannya ke tabel 'pelanggans'.
 */
class ImportPelangganCsv
{
    public static function jalankan(string $filePath): string
    {
        $fullPath = Storage::disk('local')->path($filePath);
        $rows = self::bacaCsv($fullPath);

        // Hapus file salinan sementara setelah selesai dibaca
        Storage::disk('local')->delete($filePath);

        $berhasil = 0;

        foreach ($rows as $row) {
            // Ubah huruf besar/kecil acak menjadi Title Case (contoh: "RINA SANTOSO" -> "Rina Santoso")
            $nama = Str::title(strtolower(trim($row['nama'] ?? '')));
            // Seragamkan nomor HP ke awalan 08...
            $telepon = self::normalisasiTelepon($row['telepon'] ?? '');

            // Lewati jika nama kosong atau nomor telepon tidak valid
            if ($nama === '' || $telepon === null) {
                continue;
            }

            // Gabungkan kolom alamat dan kota menjadi satu alamat lengkap
            $alamat = trim($row['alamat'] ?? '');
            $kota = trim($row['kota'] ?? '');
            $alamatLengkap = $kota !== '' ? "{$alamat}, {$kota}" : $alamat;

            // Simpan ke database (jika nama & no_telepon sudah ada, cukup update alamatnya)
            Pelanggan::updateOrCreate(
                ['nama' => $nama],
                [
                    'no_telepon' => $telepon,
                    'alamat' => $alamatLengkap,
                ]
            );
            $berhasil++;
        }

        return "{$berhasil} data pelanggan berhasil dirapikan dan disimpan.";
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
}
