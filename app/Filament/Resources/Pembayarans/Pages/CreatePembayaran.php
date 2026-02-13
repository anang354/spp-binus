<?php

namespace App\Filament\Resources\Pembayarans\Pages;

use App\Filament\Resources\Pembayarans\PembayaranResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePembayaran extends CreateRecord
{
    protected static string $resource = PembayaranResource::class;
    protected function afterCreate(): void
    {
        // 1. Ambil data dari form (termasuk toggle tadi)
        $formData = $this->form->getRawState();

        // 2. Ambil record pembayaran yang baru saja disimpan
        $pembayaran = $this->record;

        if($formData['is_whatsapp_sent'] ?? false) {
            $pengaturan = \App\Models\Pengaturan::first();
            $token = $pengaturan->token_wa;
            if ($pengaturan && $pengaturan->wa_active)
            {
                $templatePesan = $pengaturan->pesan3;
                $bulan = \App\Models\Tagihan::BULAN[$pembayaran->tagihan->periode_bulan];
                $daftarPembayaran = "";
                $daftarPembayaran .= "- *{$pembayaran->tagihan->nama_tagihan}* ({$bulan} {$pembayaran->tagihan->periode_tahun}): Rp " . number_format($pembayaran->jumlah_dibayar, 0, ',', '.') . "\n";
                $params = [
                    '{nomor_bayar}'  => $pembayaran->nomor_bayar,
                    '{tanggal_pembayaran}' => $pembayaran->tanggal_pembayaran,
                    '{nama_siswa}' => $pembayaran->siswa->nama_siswa,
                    '{nama_wali}' => $pembayaran->siswa->nama_wali,
                    '{daftar_pembayaran}' => $daftarPembayaran,
                    '{total_pembayaran}' => number_format($pembayaran->jumlah_dibayar, 0, ',', '.'),
                ];
                $pesanFinal = str_replace(array_keys($params), array_values($params), $templatePesan);
                $target = $pembayaran->siswa->nomor_hp;
                $this->kirimPesan($pesanFinal, $target, $token);
            }

        }

        // 3. Cek jika user mengaktifkan toggle 'masukkan_kas'
        if ($formData['masukkan_kas'] ?? false) {

            // Dapatkan kategori kas urutan pertama
            $kategori = \App\Models\KasKategori::first();

            if ($kategori) {
                $periode = \App\Models\Tagihan::BULAN[$pembayaran->tagihan->periode_bulan];
                \App\Models\KasTransaksi::create([
                    'user_id' => auth()->id(),
                    'kas_kategori_id' => $kategori->id,
                    'tanggal_transaksi' => $pembayaran->tanggal_pembayaran,
                    'nomor_referensi' => 'P'.$pembayaran->nomor_bayar,
                    'jenis_transaksi' => 'masuk',
                    // Mapping metode: transfer di pembayaran = non-tunai di kas
                    'metode' => $pembayaran->metode_pembayaran === 'tunai' ? 'tunai' : 'non-tunai',
                    'jumlah' => $pembayaran->jumlah_dibayar,
                    // Keterangan: Nomor Bayar + Nama Siswa
                    'keterangan' => "{$pembayaran->siswa->nama_siswa} {$pembayaran->tagihan->kategoriBiaya->nama_kategori} {$periode}",
                ]);
            }
        }
    }

    protected function kirimPesan($pesanFinal, $target, $token)
    {
        $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => 'https://api.fonnte.com/send',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => http_build_query(array(
                    'target' => $target,
                    'message' => $pesanFinal,
                )),
                CURLOPT_HTTPHEADER => array(
                    "Authorization: $token"
                ),
            ));
            $response = curl_exec($curl);
            if (curl_errno($curl)) {
                $error_msg = curl_error($curl);
                // Log error jika perlu
            }
            curl_close($curl);
    }
}
