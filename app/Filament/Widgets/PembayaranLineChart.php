<?php

namespace App\Filament\Widgets;

use Carbon\Carbon;
use App\Models\Pembayaran;
use Filament\Support\RawJs;
use Filament\Widgets\ChartWidget;

class PembayaranLineChart extends ChartWidget
{
    protected ?string $heading = '💹 History Pembayaran 6 Bulan';
    protected static ?int $sort = 1;

    protected function getData(): array
    {
        $data = collect(range(5, 0))->mapWithKeys(function ($i) {
            $month = Carbon::now()->subMonths($i);
            $yearMonth = $month->format('Y-m');
            
            // Menghitung total jumlah_dibayar dari tabel pembayarans
            $total = Pembayaran::where('tanggal_pembayaran', 'like', "{$yearMonth}%")
                ->sum('jumlah_dibayar');

            return [$month->translatedFormat('F') => $total];
        });

        return [
            'datasets' => [
                [
                    'label' => 'Total Masuk (Rp)',
                    'data' => $data->values()->toArray(),
                    'backgroundColor' => 'rgba(59, 130, 246, 0.2)',
                    'fill' => true,
                    'tension' => 0.4,
                ],
            ],
            'labels' => $data->keys()->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
