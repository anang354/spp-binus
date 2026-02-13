<?php

namespace App\Filament\Resources\Siswas\Tables;

use Carbon\Carbon;
use App\Models\Siswa;
use Filament\Tables\Table;
use Filament\Actions\EditAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Actions\ForceDeleteBulkAction;

class SiswasTable
{
    public static function configure(Table $table): Table
    {
        $now = Carbon::now();
        $tahun = $now->year;
        $bulan = $now->month;
        $periodeAngka = $now->format('Ym'); // contoh: 202507 (untuk Juli 2025)
        $querySiswa = Siswa::query()
             ->select('siswas.*')

        // Total tagihan
        ->selectSub(function ($query) use ($periodeAngka) {
            $query->from('tagihans')
                ->selectRaw('COALESCE(SUM(tagihan_netto), 0)')
                ->whereColumn('tagihans.siswa_id', 'siswas.id')
                ->whereRaw("DATE_FORMAT(jatuh_tempo, '%Y%m') <= ?", [$periodeAngka]);
        }, 'total_tagihan')

        // Total pembayaran
        ->selectSub(function ($query) use ($periodeAngka) {
            $query->from('pembayarans')
                ->selectRaw('COALESCE(SUM(jumlah_dibayar), 0)')
                ->whereColumn('pembayarans.siswa_id', 'siswas.id')
                ->whereExists(function ($sub) use ($periodeAngka) {
                    $sub->selectRaw(1)
                        ->from('tagihans')
                        ->whereColumn('tagihans.id', 'pembayarans.tagihan_id')
                        ->whereRaw("DATE_FORMAT(jatuh_tempo, '%Y%m') <= ?", [$periodeAngka]);
                });
        }, 'total_dibayar')

        // Selisih tagihan dan pembayaran
        ->selectRaw("
            (
                (SELECT COALESCE(SUM(tagihan_netto), 0)
                FROM tagihans
                WHERE tagihans.siswa_id = siswas.id
                AND DATE_FORMAT(jatuh_tempo, '%Y%m') <= ?)
                -
                (SELECT COALESCE(SUM(jumlah_dibayar), 0)
                FROM pembayarans
                WHERE pembayarans.siswa_id = siswas.id
                AND EXISTS (
                    SELECT 1 FROM tagihans
                    WHERE tagihans.id = pembayarans.tagihan_id
                        AND DATE_FORMAT(jatuh_tempo, '%Y%m') <= ?
                )
                )
            ) AS total_tagihan_belum_lunas
        ", [$periodeAngka, $periodeAngka]);
        return $table
            ->query($querySiswa)
            ->paginated([10, 25, 50, 100, 'all'])
            ->defaultPaginationPageOption(25)
            ->columns([
                // ImageColumn::make('foto')
                //     ->disk('public')
                //     ->rounded(),
                TextColumn::make('nama_siswa')
                    ->sortable()
                    ->copyable()
                    ->searchable(),
                TextColumn::make('kelas.nama_kelas')
                    ->sortable(),
                TextColumn::make('jenis_kelamin'),
                TextColumn::make('nama_wali')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('nomor_hp')
                    ->copyable()
                    ->searchable(),
                TextColumn::make('total_tagihan_belum_lunas')
                    ->label('Tagihan Belum Dibayar')
                    ->numeric(decimalPlaces: 0)
                    ->sortable(),
                IconColumn::make('is_active')
                    ->boolean(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->recordActions([
                \Filament\Actions\Action::make('lihat-pdf')
                    ->color('success')
                    ->label('Kartu SPP')
                    ->icon('heroicon-o-document-arrow-down')
                    ->url(function ($record) {
                        return url('/admin/siswas/kartu-spp/'.$record->id);
                    })
                    ->openUrlInNewTab(),
                EditAction::make(),
                RestoreAction::make(),
                ForceDeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ])->visible(fn () => auth()->user()->role !== 'viewer'),
            ]);
    }
}
