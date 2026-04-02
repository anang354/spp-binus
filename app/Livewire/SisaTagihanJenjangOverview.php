<?php

namespace App\Livewire;

use Carbon\Carbon;
use App\Models\Tagihan;
use Illuminate\Support\Facades\DB;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class SisaTagihanJenjangOverview extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $now = Carbon::now();
        $currentMonth = $now->month;
        $currentYear = $now->year;

        // Query menghitung sisa tagihan (Netto - Terbayar) per Jenjang
        $statsByJenjang = Tagihan::query()
            ->join('siswas', 'tagihans.siswa_id', '=', 'siswas.id')
            ->join('kelas', 'siswas.kelas_id', '=', 'kelas.id')
            // Menggunakan left join agar tagihan yang belum ada pembayarannya tetap terhitung
            ->leftJoin('pembayarans', 'tagihans.id', '=', 'pembayarans.tagihan_id')
            // Filter: Periode <= Bulan & Tahun Sekarang
            ->where(function($query) use ($currentMonth, $currentYear) {
                $query->where('tagihans.periode_tahun', '<', $currentYear)
                      ->orWhere(function($q) use ($currentMonth, $currentYear) {
                          $q->where('tagihans.periode_tahun', $currentYear)
                            ->where('tagihans.periode_bulan', '<=', $currentMonth);
                      });
            })
            ->select(
                'kelas.jenjang',
                DB::raw('SUM(tagihans.tagihan_netto) as total_piutang_awal'),
                DB::raw('SUM(COALESCE(pembayarans.jumlah_dibayar, 0)) as total_masuk')
            )
            ->groupBy('kelas.jenjang')
            ->get();

        return $statsByJenjang->map(function ($stat) {
            $sisaPiutang = $stat->total_piutang_awal - $stat->total_masuk;

            return Stat::make("Sisa Tagihan {$stat->jenjang}", "Rp " . number_format($sisaPiutang, 0, ',', '.'))
                ->description("Hingga periode " . Carbon::now()->translatedFormat('F Y'))
                ->descriptionIcon('heroicon-m-clock')
                ->color($sisaPiutang > 0 ? 'danger' : 'success');
        })->toArray();
    
    }
}
