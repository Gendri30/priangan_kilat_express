<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Paket;

class CekResiController extends Controller
{
    public function index(Request $request){
        $noResi = trim((string) $request->query('no_resi', ''));
        $paket = null;
        $tidakDitemukan = false;

        if ($noResi !== ''){
            $paket = Paket::with('riwayatStatus')->where('no_resi', strtoupper($noResi))
            ->first();

            if (! $paket){
                $tidakDitemukan = true;
            }
        }

        return view('lacak', ['noResi' => $noResi, 'paket'=> $paket, 'tidakDitemukan'=> $tidakDitemukan]);
    }
}
