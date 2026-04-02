<?php

namespace App\Filament\Widgets;

use Carbon\Carbon;
use App\Models\KasTransaksi;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class KasKeluarKategoriChart extends ChartWidget
{
    protected ?string $heading = 'Pengeluaran Kas Bulan Ini per Kategori';
     protected static bool $isDiscovered = false; //sembunyikan di dashboard

    protected function getData(): array
    {
        $currentMonth = Carbon::now()->month;
        $currentYear = Carbon::now()->year;

        $data = KasTransaksi::query()
            ->join('kas_kategoris', 'kas_transaksis.kas_kategori_id', '=', 'kas_kategoris.id')
            ->where('kas_transaksis.jenis_transaksi', 'keluar')
            ->whereMonth('kas_transaksis.tanggal_transaksi', $currentMonth)
            ->whereYear('kas_transaksis.tanggal_transaksi', $currentYear)
            ->select('kas_kategoris.nama_kategori', DB::raw('SUM(kas_transaksis.jumlah) as total'))
            ->groupBy('kas_kategoris.nama_kategori')
            ->get();

        return [
            'datasets' => [
                [
                    'label' => 'Total Keluar (Rp)',
                    'data' => $data->pluck('total')->toArray(),
                    'backgroundColor' => '#ef4444',
                ],
            ],
            'labels' => $data->pluck('nama_kategori')->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
