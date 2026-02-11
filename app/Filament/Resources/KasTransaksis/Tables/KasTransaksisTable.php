<?php

namespace App\Filament\Resources\KasTransaksis\Tables;

use Filament\Tables\Table;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Illuminate\Support\Facades\DB;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\Summarizers\Summarizer;
use Illuminate\Contracts\Database\Eloquent\Builder;

class KasTransaksisTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function (Builder $query) use ($table) {
                // 1. Ambil Filter LANGSUNG dari Component Livewire (Pasti Akurat)
                $livewire = $table->getLivewire();

                // Mengakses properti public $tableFilters milik component
                $filters = $livewire->tableFilters;

                $filterMetode = $filters['metode']['value'] ?? null;
                $filterDariTanggal = $filters['rentang_tanggal']['dari_tanggal'] ?? null;
                $filterSampaiTanggal = $filters['rentang_tanggal']['sampai_tanggal'] ?? null;

                // 2. Bangun Subquery Saldo
                $subQuery = DB::table('kas_transaksis as sub')
                    ->selectRaw("SUM(
                        CASE
                            WHEN sub.jenis_transaksi = 'masuk' THEN sub.jumlah
                            ELSE -sub.jumlah
                        END
                    )")
                    // Logika dasar: Hitung history ke belakang
                    ->where(function ($q) {
                        $q->whereColumn('sub.tanggal_transaksi', '<', 'kas_transaksis.tanggal_transaksi')
                        ->orWhere(function ($q2) {
                            $q2->whereColumn('sub.tanggal_transaksi', '=', 'kas_transaksis.tanggal_transaksi')
                                ->whereColumn('sub.id', '<=', 'kas_transaksis.id');
                        });
                    });

                // 3. Terapkan Filter ke Subquery

                // A. Filter Metode
                if ($filterMetode) {
                    $subQuery->where('sub.metode', $filterMetode);
                }

                // B. Filter Tanggal (Start from 0 logic)
                if ($filterDariTanggal) {
                    $subQuery->whereDate('sub.tanggal_transaksi', '>=', $filterDariTanggal);
                }
                // Filter Sampai Tanggal (Opsional, demi konsistensi)
                if ($filterSampaiTanggal) {
                    $subQuery->whereDate('sub.tanggal_transaksi', '<=', $filterSampaiTanggal);
                }

                // 4. Inject Subquery ke Query Utama
                $query->select('kas_transaksis.*') // Pastikan select all dari tabel utama
                    ->selectSub($subQuery, 'saldo_berjalan');

                return $query;
            })
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('kategori.nama_kategori'),
                TextColumn::make('metode')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'tunai' => 'success',
                        'non-tunai' => 'info',
                    }),
                TextColumn::make('tanggal_transaksi')
                    ->date('d F Y'),
                TextColumn::make('nomor_referensi')
                    ->copyable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->searchable(),
            TextColumn::make('masuk')
                ->label('Masuk (Rp)')
                ->color('success')
                ->state(function (\Illuminate\Database\Eloquent\Model $record) {
                    return $record->jenis_transaksi === 'masuk' ? $record->jumlah : 0;
                })
                ->numeric(decimalPlaces: 0)
                ->summarize(
                    Summarizer::make()
                    ->label('Total Masuk')
                    ->numeric(decimalPlaces: 0)
                    ->using(fn ($query) => $query->where('jenis_transaksi', 'masuk')->sum('jumlah'))
                ),

            // Kolom Uang Keluar
            TextColumn::make('keluar')
                ->label('Keluar (Rp)')
                ->state(function (\Illuminate\Database\Eloquent\Model $record) {
                    return $record->jenis_transaksi === 'keluar' ? $record->jumlah : 0;
                })
                ->numeric(decimalPlaces: 0)
                ->color('danger')
                ->summarize(
                    Summarizer::make()
                        ->label('Total Keluar')
                        ->numeric(decimalPlaces: 0)
                        ->using(fn ($query) => $query->where('jenis_transaksi', 'keluar')->sum('jumlah')),
                ),
                TextColumn::make('keterangan')
                    ->words(5, end: ' ...')
                    ->searchable(),
                TextColumn::make('saldo_berjalan')
                ->label('Saldo')
                ->money('IDR')
                ->weight('bold') // Tebalkan agar terlihat seperti saldo akhir
                // Opsional: Matikan sorting agar user tidak mengacak urutan saldo
                ->sortable(false),
                TextColumn::make('user.name')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                \Filament\Tables\Filters\SelectFilter::make('metode')
                    ->options([
                        'tunai' => 'Tunai',
                        'non-tunai' => 'Non-Tunai',
                    ])
                    // PENTING: Saat filter aktif, query utama otomatis terfilter oleh Filament.
                    // Subquery di getEloquentQuery() akan menangkap nilai ini via request().
                    ->indicator('Metode'),
                \Filament\Tables\Filters\Filter::make('rentang_tanggal')
                    ->form([
                        \Filament\Forms\Components\DatePicker::make('dari_tanggal')->label('Dari Tanggal'),
                        \Filament\Forms\Components\DatePicker::make('sampai_tanggal')->label('Sampai Tanggal'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['dari_tanggal'],
                                fn (Builder $query, $date) => $query->whereDate('tanggal_transaksi', '>=', $date),
                            )
                            ->when(
                                $data['sampai_tanggal'],
                                fn (Builder $query, $date) => $query->whereDate('tanggal_transaksi', '<=', $date),
                            );
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        if ($data['dari_tanggal'] ?? null) {
                            $indicators[] = 'Dari: ' . \Carbon\Carbon::parse($data['dari_tanggal'])->toFormattedDateString();
                        }
                        if ($data['sampai_tanggal'] ?? null) {
                            $indicators[] = 'Sampai: ' . \Carbon\Carbon::parse($data['sampai_tanggal'])->toFormattedDateString();
                        }
                        return $indicators;
                    }),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    // DeleteBulkAction::make(),
                ]),
            ]);
    }
}
