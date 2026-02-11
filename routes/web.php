<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\KartuSppController;

Route::get('/', function () {
    return view('welcome');
});
Route::middleware(['auth'])->group(function () {
    Route::get('admin/siswas/kartu-spp/{id}', [KartuSppController::class, 'index'])->name('kartu-spp-siswa');
});