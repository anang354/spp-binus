<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pembayaran extends Model
{
    //
    protected $guarded = ['id'];

    public function tagihan(): BelongsTo
    {
        return $this->belongsTo(Tagihan::class);
    }
    public function siswa(): BelongsTo
    {
        return $this->belongsTo(Siswa::class)->withTrashed();
    }
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class)->withTrashed();
    }
    public static function generateNomorBayar(): string
    {
        $bulan = Carbon::now()->format('m');
        $tahun = Carbon::now()->format('Y');
        $number = random_int(0, 999999); 
        $nomorBayar = str_pad($number, 6, '0', STR_PAD_LEFT);

        return "{$bulan}{$tahun}{$nomorBayar}";
    }
    protected static function booted()
    {
        static::creating(function ($pembayaran) {
            $pembayaran->user_id = auth()->user()->id;
            //generate nomor pembayaran untuk pembayaranResource (tagihan satuan)
            if(!$pembayaran->nomor_bayar){
                $pembayaran->nomor_bayar = self::generateNomorBayar();
            }
            $tagihan = $pembayaran->tagihan;
            $totalTerbayar = \App\Models\Pembayaran::where('tagihan_id', $pembayaran->tagihan_id)->sum('jumlah_dibayar');
            $sisa = $tagihan->tagihan_netto - $totalTerbayar;

            if ($pembayaran->jumlah_dibayar > $sisa) {
                // Menghentikan proses simpan dan melempar error
                throw new \Exception("Transaksi digagalkan: Nominal bayar melebihi sisa tagihan.");
            }
        });
        static::created(function ($pembayaran) {
            $tagihan = $pembayaran->tagihan;

            $totalBayar = $tagihan->pembayaran()->sum('jumlah_dibayar');

            if ($totalBayar >= $tagihan->tagihan_netto) {
                $tagihan->status = 'lunas';
            } elseif ($totalBayar > 0) {
                $tagihan->status = 'angsur';
            } else {
                $tagihan->status = 'baru';
            }

            $tagihan->save();
        });
    }
}
