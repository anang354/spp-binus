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
}
