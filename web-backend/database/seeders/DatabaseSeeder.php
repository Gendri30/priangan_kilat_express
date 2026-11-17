<?php

namespace Database\Seeders;

use App\Models\Kurir;
use App\Models\Paket;
use App\Models\Pelanggan;
use App\Models\User;
use App\Models\ZonaTarif;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Buat Akun Petugas Web (Admin Loket & Manajer)
        User::unguarded(function () {
            User::updateOrCreate(
                ['username' => 'admin'],
                [
                    'name' => 'Admin Loket PKE',
                    'email' => 'admin@priangankilat.test',
                    'password' => 'admin123', // Password login Admin
                    'role' => 'admin',
                ]
            );

            User::updateOrCreate(
                ['username' => 'manajer'],
                [
                    'name' => 'Pak Dedi (Manajer)',
                    'email' => 'manajer@priangankilat.test',
                    'password' => 'manajer123', // Password login Manajer
                    'role' => 'manajer',
                ]
            );
        });

        // 2. Isi Master Zona Tarif dari master_zona_tarif.csv
        // PENTING: Pakai firstOrCreate supaya kalau Manajer sudah mengubah tarif,
        // tarif baru pilihan Manajer TIDAK tertimpa balik ke awal saat db:seed dijalankan ulang
        $zonaCsv = storage_path('app/Varian_B_Ekspedisi/Data_Seeder_CSV/master_zona_tarif.csv');
        if (file_exists($zonaCsv)) {
            $handle = fopen($zonaCsv, 'r');
            $header = array_map('trim', fgetcsv($handle));
            while (($row = fgetcsv($handle)) !== false) {
                $item = array_combine($header, $row);
                ZonaTarif::firstOrCreate(
                    ['zona' => (int) $item['zona']], // Cek berdasarkan nomor zona (1, 2, 3)
                    [
                        'nama_zona' => trim($item['nama_zona']),
                        'tarif_per_kg' => (int) $item['tarif_per_kg'],
                    ]
                );
            }
            fclose($handle);
        }

        // 3. Pastikan akun kurir KUR-02 tersedia di database
        $kurir02 = Kurir::firstOrCreate(
            ['kode_kurir' => 'KUR-02'],
            [
                'nama' => 'Mega Saputra',
                'no_telepon' => '083303528534',
                'password' => 'kurir123',
                'is_active' => true,
                'must_change_password' => true,
            ]
        );

        // 4. Syarat Wajib Ujian: KUR-02 harus membawa TEPAT 20 paket berstatus 'Dikirim'
        // Di paket_historis.csv sudah ada 4 paket Dikirim milik KUR-02,
        // jadi perulangan di bawah hanya menambah sisanya (16 paket) sampai pas 20 paket
        $seedPaketCsv = storage_path('app/Varian_B_Ekspedisi/Data_Seeder_CSV/seed_paket_dikirim.csv');
        if (file_exists($seedPaketCsv) && $kurir02->countPaketDikirim() < 20) {
            // Buat 1 data pengirim contoh untuk paket demo
            $pelangganDemo = Pelanggan::firstOrCreate(
                ['nama' => 'Pengirim Demo'],
                [
                    'no_telepon' => '081234567890',
                    'alamat' => 'Jl. Raya Katapang No. 1, Bandung',
                ]
            );

            $tarifMap = ZonaTarif::pluck('tarif_per_kg', 'zona')->toArray();
            $handle = fopen($seedPaketCsv, 'r');
            $header = array_map('trim', fgetcsv($handle));

            while (($row = fgetcsv($handle)) !== false) {
                // Begitu jumlah paket Dikirim milik KUR-02 sudah mencapai 20, langsung berhenti!
                if ($kurir02->countPaketDikirim() >= 20) {
                    break;
                }

                $item = array_combine($header, $row);
                $zona = (int) ($item['zona'] ?? 1);
                $beratTagih = Paket::hitungBeratTagih((float) ($item['berat_kg'] ?? 1));
                $tarifSnapshot = (int) ($tarifMap[$zona] ?? 8000);
                $layanan = trim($item['layanan'] ?? 'Reguler');
                $ongkir = Paket::hitungOngkir($beratTagih, $tarifSnapshot, $layanan);

                $paket = Paket::create([
                    'no_resi' => Paket::generateNoResi(), // Buat nomor resi otomatis PKE-YYMMDD-XXXX
                    'tanggal_masuk' => now(),
                    'pelanggan_id' => $pelangganDemo->id,
                    'nama_penerima' => trim($item['penerima']),
                    'no_telepon_penerima' => trim($item['telepon_penerima']),
                    'alamat_penerima' => trim($item['alamat_penerima']),
                    'kota_tujuan' => trim($item['kota_tujuan']),
                    'zona' => $zona,
                    'is_zona_manual' => false,
                    'berat_aktual' => (float) ($item['berat_kg'] ?? 1),
                    'berat_tagih' => $beratTagih,
                    'perlu_verifikasi' => false,
                    'layanan' => $layanan,
                    'tarif_dasar_snapshot' => $tarifSnapshot,
                    'ongkos_kirim' => $ongkir,
                    'is_cod' => false,
                    'nilai_cod' => 0,
                    'biaya_cod' => 0,
                    'status_setoran_cod' => 'tidak_cod',
                    'kurir_id' => $kurir02->id, // Tugaskan ke KUR-02
                    'status' => 'Dikirim', // Status langsung Dikirim
                    'jumlah_gagal' => 0,
                ]);

                // Catat riwayat statusnya
                $paket->catatRiwayat('Dikirim', 'Admin', 'Admin Loket PKE', 'Ditugaskan ke KUR-02 (Data Uji Kuota 20 Paket)');
            }
            fclose($handle);
        }
    }
}
