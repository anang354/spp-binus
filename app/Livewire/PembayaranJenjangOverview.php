<?php

namespace App\Livewire;

use Carbon\Carbon;
use App\Models\Pembayaran;
use Illuminate\Support\Facades\DB;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class PembayaranJenjangOverview extends StatsOverviewWidget
{
    protected int | string | array $columnSpan = 'full';
    
    protected function getStats(): array
    {
        // Mendapatkan awal dan akhir bulan ini
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();

        // Query untuk menjumlahkan pembayaran berdasarkan jenjang di bulan ini
        $statsByJenjang = Pembayaran::query()
            ->join('siswas', 'pembayarans.siswa_id', '=', 'siswas.id')
            ->join('kelas', 'siswas.kelas_id', '=', 'kelas.id')
            ->whereBetween('pembayarans.tanggal_pembayaran', [$startOfMonth, $endOfMonth])
            ->select('kelas.jenjang', DB::raw('SUM(pembayarans.jumlah_dibayar) as total'))
            ->groupBy('kelas.jenjang')
            ->get();
        // Map hasil query menjadi komponen Stat Filament
        return $statsByJenjang->map(function ($stat) {
        $warna = [
            'TK' => 'success',
            'SD' => 'info',
            'SMP' => 'warning',
            'SMA' => 'primary',
        ];
            return Stat::make("Total {$stat->jenjang}", "Rp " . number_format($stat->total, 0, ',', '.'))
                ->description("Pembayaran masuk bulan ".date('F Y'))
                ->descriptionIcon('heroicon-m-banknotes')
                ->color($warna[$stat->jenjang]);
        })->toArray();
    }
}
