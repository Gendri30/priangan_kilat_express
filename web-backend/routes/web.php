<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CekResiController;

Route::get('/', [CekResiController::class, 'index'])->name('lacak.resi');

