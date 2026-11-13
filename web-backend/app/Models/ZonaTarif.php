<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ZonaTarif extends Model
{
    protected $fillable = ['zona','nama_zona','tarif_per_kg'];
}
