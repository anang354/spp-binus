<?php

namespace App\Exports;

use Carbon\Carbon;
use App\Models\Siswa;
use App\Models\Tagihan;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use Illuminate\Contracts\View\View; // Ganti ini
use Maatwebsite\Excel\Concerns\FromView; // Ganti ini
use Maatwebsite\Excel\Concerns\ShouldAutoSize; // Tambahkan ini

class SiswaTagihanExport implements FromView, ShouldAutoSize
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function view(): View
    {
        $now = Carbon::now();
        $bulan = $now->month;
        $tahun = $now->year;

        // Base query untuk header
        $baseHeader = Tagihan::query()
            ->join('kategori_biayas', 'tagihans.kategori_biaya_id', '=', 'kategori_biayas.id')
            ->where(fn ($q) => $this->applyPeriodFilter($q, $bulan, $tahun));

        // 1. Header Dinamis untuk Tagihan SEKALI (Per Kategori)
        $sekaliHeaders = (clone $baseHeader)->where('jenis_tagihan', 'sekali')
            ->select('tagihans.kategori_biaya_id', 'kategori_biayas.nama_kategori')
            ->distinct()->get();

        // 2. Header Dinamis untuk Tagihan TAHUNAN (Per Kategori)
        $tahunanHeaders = (clone $baseHeader)->where('jenis_tagihan', 'tahunan')
            ->select('tagihans.kategori_biaya_id', 'kategori_biayas.nama_kategori')
            ->distinct()->get();

        // 3. Header Dinamis untuk Tagihan BULANAN (Kategori + Periode)
        $bulananHeaders = (clone $baseHeader)->where('jenis_tagihan', 'bulanan')
            ->select('tagihans.kategori_biaya_id', 'kategori_biayas.nama_kategori', 'tagihans.periode_bulan', 'tagihans.periode_tahun')
            ->distinct()
            ->orderBy('tagihans.periode_tahun', 'asc')
            ->orderBy('tagihans.periode_bulan', 'asc')
            ->get();

        // 4. Query Siswa (Urut Jenjang & Filter Piutang)
        $students = Siswa::query()
            ->join('kelas', 'siswas.kelas_id', '=', 'kelas.id')
            ->select('siswas.*', 'kelas.nama_kelas', 'kelas.jenjang')
            ->with(['tagihans' => function($q) use ($bulan, $tahun) {
                $this->applyPeriodFilter($q, $bulan, $tahun)->with('pembayaran');
            }])
            ->whereHas('tagihans', function ($query) use ($bulan, $tahun) {
                $this->applyPeriodFilter($query, $bulan, $tahun)
                     ->whereRaw('(tagihan_netto - (SELECT COALESCE(SUM(jumlah_dibayar), 0) FROM pembayarans WHERE pembayarans.tagihan_id = tagihans.id)) > 0');
            })
            ->orderBy('kelas.jenjang', 'asc')
            ->get();

            $totalTagihanCols = count($sekaliHeaders) + count($tahunanHeaders) + count($bulananHeaders);
            // Kolom data dimulai dari D (index 4), maka kolom terakhir adalah:
            $lastColumnLetter = Coordinate::stringFromColumnIndex(3 + $totalTagihanCols);

        return view('exports.siswa_tagihan', compact('sekaliHeaders', 'tahunanHeaders', 'bulananHeaders', 'students', 'bulan', 'tahun', 'lastColumnLetter'));
    }

    protected function applyPeriodFilter($query, $bulan, $tahun) {
        return $query->where(function ($q) use ($bulan, $tahun) {
            $q->where('periode_tahun', '<', $tahun)
              ->orWhere(function ($sq) use ($bulan, $tahun) {
                  $sq->where('periode_tahun', $tahun)
                    ->where('periode_bulan', '<=', $bulan);
              });
        });
    }
}
