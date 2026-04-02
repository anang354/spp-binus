<?php

namespace App\Filament\Resources\KasLaporans\Pages;

use App\Models\KasTransaksi;
use Illuminate\Support\Facades\DB;
use Filament\Resources\Pages\CreateRecord;
use App\Filament\Resources\KasLaporans\KasLaporanResource;

class CreateKasLaporan extends CreateRecord
{
    protected static string $resource = KasLaporanResource::class;
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $tanggalTutup = $data['tanggal_tutup'];
        $saldoTunai = KasTransaksi::where('tanggal_transaksi', '<=', $tanggalTutup)
        ->where('metode', 'tunai')
        ->sum(DB::raw("CASE WHEN jenis_transaksi = 'masuk' THEN jumlah ELSE -jumlah END"));
        $saldoNonTunai = KasTransaksi::where('tanggal_transaksi', '<=', $tanggalTutup)
        ->where('metode', 'non-tunai')
        ->sum(DB::raw("CASE WHEN jenis_transaksi = 'masuk' THEN jumlah ELSE -jumlah END"));
        $data['saldo_akhir_tunai'] = $saldoTunai;
        $data['saldo_akhir_bank'] = $saldoNonTunai;
        $data['total_saldo'] = $saldoTunai + $saldoNonTunai;
        return $data;
    }
}
