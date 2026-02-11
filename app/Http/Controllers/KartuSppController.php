<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class KartuSppController extends Controller
{
    public function index(Request $request)
    {
        $siswa = \App\Models\Siswa::withTrashed()->where('id',$request->id)->with(['tagihans', 'pembayarans', 'kelas'])->first()->toArray();
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
        // $urlCode = route('verification', ['encrypted_nisn' => \Illuminate\Support\Facades\Crypt::encryptString($siswa['nisn'])]);
        // $qrCode =  base64_encode(\SimpleSoftwareIO\QrCode\Facades\QrCode::format('svg')->size(100)->generate($urlCode));
        // dd($siswa);
        $pdf = Pdf::loadView('templates.kartu-spp-siswa',[
            'siswa' => $siswa,
            'logo' => $image,
            'dataSekolah' => $dataSekolah
            // 'qrcode' => $qrCode
        ]);
        $namaFile = 'Kartu-SPP-'.$siswa['nama_siswa'].'.pdf';
        return $pdf->stream($namaFile);
    }
}
