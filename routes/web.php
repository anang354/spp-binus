<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\KartuSppController;
use App\Http\Controllers\KwitansiPembayaranController;

Route::get('/', function () {
    return view('welcome');
});
Route::middleware(['auth'])->group(function () {
    Route::get('admin/siswas/kartu-spp/{id}', [KartuSppController::class, 'index'])->name('kartu-spp-siswa');
    Route::get('admin/pembayarans/kwitansi/{nomor_bayar}', [KwitansiPembayaranController::class, 'index'])->name('kwitansi-pembayaran-siswa');
});
Route::get('/verification-payment/{encrypted_nomor}', [KwitansiPembayaranController::class, 'verification'])->name('verification-payment');
