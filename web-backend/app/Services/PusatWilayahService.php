<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class PusatWilayahService
{
    public static function cekZonaKota(string $kota): array
    {
        $baseUrl = rtrim((string) env('PUSAT_WILAYAH_URL', ''), '/');
        $apiKey = trim((string) env('PUSAT_WILAYAH_KEY', ''));

        if (empty($baseUrl) || empty($apiKey)) {
            return [
                'sukses' => false,
                'zona' => null,
                'pesan' => 'Konfigurasi API error, masukkan zona manual',
            ];
        }

        try {
            $response = Http::withoutVerifying()
                ->timeout(5)
                ->withHeaders([
                    'x-api-key' => $apiKey,
                    'Accept' => 'application/json',
                ])
                ->get($baseUrl . '/wilayah', [
                    'kota' => trim($kota),
                ]);

            if ($response->failed()) {
                return [
                    'sukses' => false,
                    'zona' => null,
                    'pesan' => 'Layanan Pusat Wilayah gagal merespons. Silakan pilih zona manual.',
                ];
            }

            $data = $response->json();
            $zona = $data['zona'] ?? null;

            if (! in_array((int) $zona, [1, 2, 3], true)) {
                return [
                    'sukses' => false,
                    'zona' => null,
                    'pesan' => "Kota '{$kota}' tidak dikenal",
                ];
            }

            return [
                'sukses' => true,
                'zona' => (int) $zona,
                'pesan' => "Zona {$zona} ditemukan",
            ];
        } catch (\Throwable $e) {
            return [
                'sukses' => false,
                'zona' => null,
                'pesan' => 'Tidak terhubung ke pusat wilayah',
            ];
        }
    }
}
