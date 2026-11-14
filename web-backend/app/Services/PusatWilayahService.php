<?php

    namespace App\Services;

    use Illuminate\Support\Facades\Http;

    class PusatWilayahService{
        public static function cekZonaKota(string $kota): array{
            $baseUrl = rtrim((string) env('PUSAT_WILAYAH_URL',''), '/');
            $apiKey =(string) env('PUSAT_WILAYAH_KEY','');

            if (empty($baseUrl)|| empty($apiKey)){
                return ['sukses'=> false, 'zona'=> null, 'pesan'=>'konfigurasi api error, masukan zona manual'];
            }
            try{
                $response = Http::timeout(5)->withHeaders(['X-API-Key'=> $apiKey,'Accept'=> 'application/json'])
                ->get($baseUrl . '/zona', ['kota'=> trim($kota),]);

                if ($response->failed()) {
                return ['sukses' => false,'zona' => null, 'pesan' => 'Layanan Pusat Wilayah gagal merespons. Silakan pilih zona manual.',];
                }

                $data = $response->json();
                $zona = $data['zona']??null;
                if (!in_array((int) $zona, [1,2,3], true)){
                    return['sukses'=> false,'zona'=>null,'pesan'=>"kota '{$kota}' tidak di kenal"];
                    }

                    return['sukses'=>true, 'zona'=>(int)$zona,'pesan'=>"zona {$zona} ditemukan"];
                    }
                    catch (\Throwable $e){
                        return ['sukses'=>false,'zona'=>null,'pesan'=>'tidak terhubung ke pusat wilayah'];
                    }
        }
    }
