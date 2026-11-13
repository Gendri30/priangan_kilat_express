<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Pelanggan extends Model
{
    protected $fillable = ['nama','no_telepon','alamat'];
    protected static function booted(): void{
        static::saving(function (Pelanggan $pelanggan){
            if (!empty($pelanggan->nama)){
                $pelanggan->nama = Str::title(strtolower(trim($pelanggan->nama)));
            }
        });
    }
    public function pakets():HasMany{
        return $this->hasMany(Paket::class);
    }
}
