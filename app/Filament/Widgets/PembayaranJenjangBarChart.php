<?php

namespace App\Filament\Widgets;

use Carbon\Carbon;
use App\Models\Pembayaran;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class PembayaranJenjangBarChart extends ChartWidget
{
    protected ?string $heading = '💰 Pembayaran Bulan Ini';
    protected static ?int $sort = 2;
    // protected ?string $color = 'warning';

    protected function getData(): array
    {
        $currentMonth = Carbon::now()->month;
        $currentYear = Carbon::now()->year;

        // Query join antara pembayarans, siswas, dan kelas
        $dataByJenjang = Pembayaran::query()
            ->join('siswas', 'pembayarans.siswa_id', '=', 'siswas.id') //
            ->join('kelas', 'siswas.kelas_id', '=', 'kelas.id') //
            ->whereMonth('pembayarans.tanggal_pembayaran', $currentMonth)
            ->whereYear('pembayarans.tanggal_pembayaran', $currentYear)
            ->select('kelas.jenjang', DB::raw('SUM(pembayarans.jumlah_dibayar) as total')) //
            ->groupBy('kelas.jenjang')
            ->get();

        return [
            'datasets' => [
                [
                    'label' => 'Pendapatan (Rp)',
                    'data' => $dataByJenjang->pluck('total')->toArray(),
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
            'labels' => $dataByJenjang->pluck('jenjang')->toArray(), // Menggunakan kolom jenjang dari tabel kelas
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
