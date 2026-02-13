<?php

namespace App\Http\Controllers;

use App\Models\Pembayaran;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class KwitansiPembayaranController extends Controller
{
    public function index (Request $request)
    {
        $pembayaran = Pembayaran::where('nomor_bayar', $request->nomor_bayar)->with(['siswa', 'user', 'tagihan'])->get()->toArray();
        $pengaturan = \App\Models\Pengaturan::first();
        $dataSekolah = [
            'nama_sekolah' => $pengaturan->nama_sekolah ?? '',
            'alamat_sekolah' => $pengaturan->alamat_sekolah ?? '',
            'telepon_sekolah' => $pengaturan->telepon_sekolah ?? '',
        ];
        $path = storage_path('app/public/' . $pengaturan->logo_sekolah);
        if (file_exists($path)) {
            $type = pathinfo($path, PATHINFO_EXTENSION);
            $data = file_get_contents($path);
            $image = 'data:image/' . $type . ';base64,' . base64_encode($data);
        } else {
            // Sediakan gambar fallback jika logo tidak ditemukan
            $image = null;
        }
        $url = route('verification-payment', ['encrypted_nomor' => \Illuminate\Support\Facades\Crypt::encryptString($request->nomor_bayar)]);
        $qrCode =  base64_encode(\SimpleSoftwareIO\QrCode\Facades\QrCode::format('svg')->size(100)->generate($url));

        $pdf = Pdf::loadView('templates.kwitansi-siswa',[
            'pembayaran' => $pembayaran,
            'logo' => $image,
            'dataSekolah' => $dataSekolah,
            'qrcode' => $qrCode
        ]);
        $namaFile = $request->nomor_bayar.'_'.$pembayaran[0]['siswa']['nama_siswa'].'.pdf';
        return $pdf->stream($namaFile);
    }
    public function verification($encrypted_nomor)
    {
        $pengaturan = \App\Models\Pengaturan::select('nama_sekolah', 'logo_sekolah', 'telepon_sekolah')->first();
        $nomorBayar = \Illuminate\Support\Facades\Crypt::decryptString($encrypted_nomor);
        $checkPembayaran = Pembayaran::where('nomor_bayar', $nomorBayar)->with(['siswa'])->get();
        if(!$checkPembayaran){
            return abort(404);
        }
        return view('verification-payment', [
                'nomor_bayar' => $nomorBayar,
                'pembayaran' => $checkPembayaran,
                'pengaturan' => $pengaturan,
            ]);
    }
}
