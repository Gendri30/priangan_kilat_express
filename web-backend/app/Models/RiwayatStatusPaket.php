<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

//buat cek resi di web
class RiwayatStatusPaket extends Model
{
    protected $fillable = ['paket_id','status','pelaku_tipe','pelaku_nama','catatan','waktu'];

    protected function casts(): array{
        return ['waktu'=>'datetime'];
    }

    //relasi balik ke paket
    public function paket(): BelongsTo{
        return $this->belongsTo(Paket::class);
    }
}
