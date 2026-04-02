<?php

namespace App\Filament\Widgets;

use Carbon\Carbon;
use App\Models\Tagihan;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class TagihanKategoriBarChart extends ChartWidget
{
    protected ?string $heading = '💵 Tagihan Berdasarkan Kategori Biaya';

    protected function getData(): array
    {
        $currentMonth = Carbon::now()->month;
        $currentYear = Carbon::now()->year;

        // Query menghitung Sisa Piutang (Tagihan Netto - Total Terbayar) per Kategori
        $data = Tagihan::query()
            ->join('kategori_biayas', 'tagihans.kategori_biaya_id', '=', 'kategori_biayas.id') //
            // Filter periode: Hingga bulan dan tahun sekarang
            ->where(function ($query) use ($currentMonth, $currentYear) {
                $query->where('tagihans.periode_tahun', '<', $currentYear)
                    ->orWhere(function ($q) use ($currentMonth, $currentYear) {
                        $q->where('tagihans.periode_tahun', $currentYear)
                            ->where('tagihans.periode_bulan', '<=', $currentMonth);
                    });
            })
            ->select(
                'kategori_biayas.nama_kategori', //
                DB::raw('SUM(tagihans.tagihan_netto - (SELECT COALESCE(SUM(p.jumlah_dibayar), 0) FROM pembayarans p WHERE p.tagihan_id = tagihans.id)) as sisa_piutang') //
            )
            ->groupBy('kategori_biayas.nama_kategori')
            ->get();

        return [
            'datasets' => [
                [
                    'label' => 'Sisa Tagihan (Rp)',
                    'data' => $data->pluck('sisa_piutang')->toArray(),
                    'backgroundColor' => [
                        'rgba(255, 159, 64, 0.2)',
                        'rgba(75, 192, 192, 0.2)',
                        'rgba(54, 162, 235, 0.2)',
                        'rgba(153, 102, 255, 0.2)',
                        ],
                    'borderColor' => [
                        'rgb(255, 159, 64)',
                        'rgb(75, 192, 192)',
                        'rgb(54, 162, 235)',
                        'rgb(153, 102, 255)',
                        ],
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
