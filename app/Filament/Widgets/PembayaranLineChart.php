<?php

namespace App\Filament\Widgets;

use App\Models\Pembayaran;
use Carbon\Carbon;
use Filament\Support\RawJs;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class PembayaranLineChart extends ChartWidget
{
    protected ?string $heading = '💹 History Pembayaran SPP 6 Bulan';
    protected static ?int $sort = 1;

    protected function getData(): array
    {
        $today = Carbon::now()->startOfMonth();
        $months = collect();
        for ($i = 5; $i >= 0; $i--) {
            // Karena $today sudah tanggal 1, subMonths tidak akan error overflow lagi
            $months->push($today->copy()->subMonths($i)->format('Y-m'));
        }
        $pembayaran = DB::table('pembayarans')
            ->selectRaw("DATE_FORMAT(pembayarans.tanggal_pembayaran, '%Y-%m') as bulan, SUM(pembayarans.jumlah_dibayar) as total")
            ->where('pembayarans.tanggal_pembayaran', '>=', $today->copy()->subMonths(4))
            ->groupBy('bulan')
            ->pluck('total', 'bulan');
        $dataPembayaran = $months->mapWithKeys(fn($m) => [$m => $pembayaran[$m] ?? 0]);

        return [
            'datasets' => [
                [
                    'label' => 'Total Pembayaran (Rp)',
                    'data' => $dataPembayaran->values()->all(),
                    'backgroundColor' => 'rgba(59, 130, 246, 0.2)',
                    'fill' => true,
                    'tension' => 0.4,
                ],
            ],
            'labels' => $months->map(fn($m) => Carbon::createFromFormat('Y-m', $m)->translatedFormat('F Y'))->all(),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
