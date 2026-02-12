<?php

namespace App\Livewire;

use Carbon\Carbon;
use App\Models\KasTransaksi;
use Filament\Widgets\ChartWidget;

class HistoryKasLineChart extends ChartWidget
{
    protected ?string $heading = 'History Kas Masuk & Keluar 6 bulan terakhir';

    protected function getData(): array
    {
        $months = collect(range(5, 0))->map(fn ($i) => Carbon::now()->subMonths($i));

        $dataMasuk = $months->map(function ($month) {
            return KasTransaksi::where('jenis_transaksi', 'masuk')
                ->whereMonth('tanggal_transaksi', $month->month)
                ->whereYear('tanggal_transaksi', $month->year)
                ->sum('jumlah');
        });

        $dataKeluar = $months->map(function ($month) {
            return KasTransaksi::where('jenis_transaksi', 'keluar')
                ->whereMonth('tanggal_transaksi', $month->month)
                ->whereYear('tanggal_transaksi', $month->year)
                ->sum('jumlah');
        });

        return [
            'datasets' => [
                [
                    'label' => 'Kas Masuk',
                    'data' => $dataMasuk->toArray(),
                    'borderColor' => '#2ecc71', // Hijau
                    'backgroundColor' => 'rgba(46, 204, 113, 0.2)',
                    'fill' => true,
                    'tension' => 0.4,
                ],
                [
                    'label' => 'Kas Keluar',
                    'data' => $dataKeluar->toArray(),
                    'borderColor' => '#e74c3c', // Merah
                    'backgroundColor' => 'rgba(231, 76, 60, 0.2)',
                    'fill' => true,
                    'tension' => 0.4,
                ],
            ],
            'labels' => $months->map(fn ($month) => $month->translatedFormat('F'))->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
