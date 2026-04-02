<?php

namespace App\Filament\Widgets;

use App\Models\Siswa;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class SiswaJenjangPieChart extends ChartWidget
{
    protected ?string $heading = 'Jumlah Siswa tiap jenjang';
    protected static ?int $sort = 4;
    protected ?string $maxHeight = '400px';

    protected function getData(): array
    {
        // Query menghitung jumlah siswa per jenjang dari tabel kelas
        $data = Siswa::query()
            ->join('kelas', 'siswas.kelas_id', '=', 'kelas.id')
            ->where('siswas.is_active', true) // Hanya hitung siswa aktif
            ->select('kelas.jenjang', DB::raw('count(*) as total'))
            ->groupBy('kelas.jenjang')
            ->get();

        return [
            'datasets' => [
                [
                    'data' => $data->pluck('total')->toArray(),
                    'backgroundColor' => ['#3498db', '#e74c3c', '#f1c40f', '#2ecc71'],
                ],
            ],
            // Label akan muncul sebagai "SD (150)", "SMP (120)", dst.
            'labels' => $data->map(fn ($item) => "{$item->jenjang} ({$item->total})")->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}
