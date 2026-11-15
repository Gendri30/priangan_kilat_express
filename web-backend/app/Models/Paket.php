<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

class Paket extends Model
{
    protected $fillable = ['no_resi','tanggal_masuk','pelanggan_id','nama_penerima','no_telepon_penerima',
    'alamat_penerima','kota_tujuan','zona','is_zona_manual','berat_aktual','berat_tagih','perlu_verifikasi',
    'layanan','tarif_dasar_snapshot','ongkos_kirim','is_cod','nilai_cod','biaya_cod','status_setoran_cod',
    'kurir_id','status','jumlah_gagal'];

    protected function casts(): array{
        return['tanggal_masuk' => 'datetime', 'is_zona_manual' =>'boolean', 'perlu_verifikasi' => 'boolean',
        'is_cod' => 'boolean', 'berat_aktual' => 'decimal:2'];
    }

    public function pelanggan(): BelongsTo{
        return $this->belongsTo(Pelanggan::class);
    }
    public function kurir(): BelongsTo{
        return $this->belongsTo(Kurir::class);
    }
    public function riwayatStatus(): HasMany{
        return $this->hasMany(RiwayatStatusPaket::class)->orderByDesc('waktu');
    }

    //membuat resi otomatis harian Format: PKE-YYMMDD-0001
    public static function generateNoResi (?Carbon $tanggal = null):string{
        $tgl = $tanggal ?? now();
        $prefix = 'PKE-' . $tgl->format('ymd') . '-';

        $lastResi = self::where('no_resi', 'like', $prefix . '%')
        -> orderByDesc('no_resi') ->value('no_resi');

        $nextNumber = $lastResi ? ((int) substr($lastResi, -4)) + 1 : 1;
        return $prefix . str_pad((string) $nextNumber, 4,'0', STR_PAD_LEFT);
    }

    //berat di bulatkan keatas
    public static function hitungBeratTagih(?float $beratAktual): int{
        if ($beratAktual === null || $beratAktual <= 0){
            return 1;
        }
        return max(1, (int) ceil($beratAktual));
    }

    //perhitungan ongkir express
    public static function hitungOngkir(int $beratTagih, int $tarifPerKg, string $layanan): int{
        $dasar = $beratTagih * $tarifPerKg;
        return $layanan === 'Express' ? (int) round($dasar * 1.5) : $dasar;
    }

    //biaya cod
    public static function hitungBiayaCod(bool $isCod, int $nilaiCod){
        if(! $isCod || $nilaiCod <= 0){
            return 0;
        }
        return max(2000, (int) round($nilaiCod * 0.02));
    }

    //sensor no telepon penerima
    public function getMaskedTeleponPenerimaAttribute(): string{
        $hp = preg_replace('/\D/','', $this->no_telepon_penerima);
        if (strlen($hp) <= 7){
            return substr($hp, 0,2) . '****' . substr($hp, -2);
        }
        $bintang = str_repeat('*', strlen($hp) -7);
        return substr($hp, 0, 4) . $bintang . substr($hp, -3);
    }

    //mempermudah penyimpanan rawat
    public function catatRiwayat(string $status, string $pelakuTipe, string $pelakuNama, ?string $catatan = null, ?Carbon $waktu = null) : void{
        $this->riwayatStatus()->create([
            'status' => $status,
            'pelaku_tipe' => $pelakuTipe,
            'pelaku_nama' => $pelakuNama,
            'catatan' => $catatan,
            'waktu' => $waktu ?? now(),
        ]);

    }

}
