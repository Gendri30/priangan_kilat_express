<?php

use App\Http\Controllers\Api\KurirApiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/login', [KurirApiController::class, 'login']);
Route::middleware('auth:sanctum')->group(function(){
    Route::post('/change-password',[KurirApiController::class, 'changePassword']);
    Route::get('/paket-dikirim',[KurirApiController::class, 'paketDikirim']);
    Route::post('/paket/{no_resi}/update-status',[KurirApiController::class, 'updateStatus']);
    Route::get('/rekap-cod',[KurirApiController::class, 'rekapCod']);
    Route::post('/logout',[KurirApiController::class, 'logout']);
});
