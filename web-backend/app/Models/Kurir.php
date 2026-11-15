<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Foundation\Auth\User as Authenticatable;

    class Kurir extends Authenticatable{
    use HasApiTokens;

    protected $fillable =['kode_kurir','nama','no_telepon','password','is_active','must_change_password'];

    //menyembunyikan password biar ga bocor saat di kirim ke json
    protected $hidden = ['password'];

    //konversi tipe data
    protected function casts(): array{
        return ['password'=>'hashed','is_active'=>'boolean','must_change_password'=>'boolean'];
    }

    //relasi kurir bisa mengantar banyak paket
    public function pakets(): HasMany{
        return $this->hasMany(Paket::class);
    }

    //menghitung jumlah paket yang di antar kurir
    public function countPaketDikirim():int{
        return $this->pakets()->where('status','Dikirim')->count();
    }

    //kurir tidak boleh lebih membawa 20 paket
    public function bisaDitugaskan():bool{
        return $this->is_active && $this->countPaketDikirim() <20;
    }

}
