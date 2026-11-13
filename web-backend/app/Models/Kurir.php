<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Laravel\Sanctum\HasApiTokens;

class Kurir extends Model Authenticatable{
   use HasApiTokens;
   protected $filable =['kode_kurir','nama','no_telepon','password','is_active','must_change_password'];
}
