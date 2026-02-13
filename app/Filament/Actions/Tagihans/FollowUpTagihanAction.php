<?php

namespace App\Filament\Actions\Tagihans;

use App\Jobs\BroadcastTagihan;
use Filament\Actions\BulkAction;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Collection;

class FollowUpTagihanAction
{
    public static function make(): BulkAction
    {
        return BulkAction::make('pesan2')
            ->icon('heroicon-o-chat-bubble-bottom-center-text')
            ->color('warning')
            ->label('Follow-up Tagihan')
            ->action(function (Collection $records) {
                // 1. Ambil template dari tabel pengaturans
                $settings = \App\Models\Pengaturan::select('pesan2', 'token_wa')->first();
                $token = $settings->token_wa;
                $templateAsli = $settings ? $settings->pesan2 : "Yth {nama_wali}, tagihan {nama_siswa} adalah {total_tagihan}";

                // 2. Kelompokkan tagihan berdasarkan siswa_id
                $groupedBySiswa = $records->groupBy('siswa_id');

                foreach ($groupedBySiswa as $index => $tagihans) {
                    $siswa = $tagihans->first()->siswa; //

                    if (!$siswa->nomor_hp) continue; //
                    $target = $siswa->nomor_hp;
                    $daftarTagihan = "";
                    $totalSeluruhnya = 0;

                    // 3. Susun isi variabel {daftar_tagihan} kecualikan tagihan yang statusnya sudah lunas
                    foreach ($tagihans as $t) {
                        if($t->status === 'lunas') continue;
                        $terbayar = $t->pembayaran?->sum('jumlah_dibayar') ?? 0;
                        $sisa = $t->tagihan_netto - $terbayar;

                        if ($sisa > 0) {
                            $bulan = \App\Models\Tagihan::BULAN[$t->periode_bulan];
                            $daftarTagihan .= "- *{$t->nama_tagihan}* ({$bulan} {$t->periode_tahun}): Rp " . number_format($sisa, 0, ',', '.') . "\n";
                            $totalSeluruhnya += $sisa;
                        }
                    }

                    // 4. Proses Replacement Variabel
                    $pesanFinal = str_replace([
                        '{nama_siswa}',
                        '{nama_wali}',
                        '{daftar_tagihan}',
                        '{total_tagihan}'
                    ], [
                        $siswa->nama_siswa, //
                        $siswa->nama_wali,  //
                        trim($daftarTagihan),
                        "Rp " . number_format($totalSeluruhnya, 0, ',', '.')
                    ], $templateAsli);
                    BroadcastTagihan::dispatch($pesanFinal, $target, $token)
                        ->delay(now()->addSeconds($index * 8));
                    }
                    Notification::make()
                    ->title('Pesan sedang diproses untuk dikirimkan secara bertahap.')
                    ->success()
                    ->send();
            })
            ->deselectRecordsAfterCompletion()
            ;

    }
}
