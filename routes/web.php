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


Route::get('/test', function(){
    $curl = curl_init();

curl_setopt_array($curl, array(
  CURLOPT_URL => 'https://api.fonnte.com/device',
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => '',
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 0,
  CURLOPT_FOLLOWLOCATION => true,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => 'POST',
  CURLOPT_HTTPHEADER => array(
    'Authorization: sG3KNqMcVcYx62DMfaos'
  ),
));

$response = curl_exec($curl);

curl_close($curl);
echo $response;
});
