<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\ZonaTarif;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        //akun admin
        User::create(['name'=>'Admin Loket PKE',
                        'username'=>'admin',
                        'email' =>'admin@pke.id',
                        'password'=> Hash::make('admin123'),
                        'role'=> 'admin'
                        ]);
        //akun manajer
        User::create(['name'=>'Pa Dedi',
                        'username'=>'manajer',
                        'email' =>'manajer@pke.id',
                        'password'=> Hash::make('manajer123'),
                        'role'=> 'manajer'
                        ]);

        ZonaTarif::create(['zona' => 1, 'nama_zona' => 'Bandung Raya', 'tarif_per_kg' => 8000]);
        ZonaTarif::create(['zona' => 2, 'nama_zona' => 'Jawa Barat lainnya', 'tarif_per_kg' => 12000]);
        ZonaTarif::create(['zona' => 3, 'nama_zona' => 'Luar Jawa Barat', 'tarif_per_kg' => 20000]);
    }
}

