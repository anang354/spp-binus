<?php

namespace App\Filament\Resources\Tagihans\Tables;

use Carbon\Carbon;
use App\Models\Tagihan;
use Filament\Tables\Table;
use Filament\Actions\EditAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Columns\Summarizers\Sum;
use Illuminate\Contracts\Database\Eloquent\Builder;

class TagihansTable
{
    public static function configure(Table $table): Table
    {
        return $table
        ->query(
                \App\Models\Tagihan::query()
                    ->with('pembayaran')
                    ->selectRaw('
                tagihans.*,
                (SELECT COALESCE(SUM(jumlah_dibayar), 0) FROM pembayarans WHERE pembayarans.tagihan_id = tagihans.id) as total_dibayar,
                (tagihan_netto - (SELECT COALESCE(SUM(jumlah_dibayar), 0) FROM pembayarans WHERE pembayarans.tagihan_id = tagihans.id)) as sisa_tagihan
            ')
                    ->orderByDesc('created_at')
            )
            ->columns([
                TextColumn::make('nama_tagihan')
                    ->searchable(),
                TextColumn::make('periode')
                    ->getStateUsing(function (Tagihan $record): string {
                    // Buat objek Carbon dari periode_tahun dan periode_bulan
                    // Asumsi periode_bulan adalah 1-12
                    $date = Carbon::createFromDate($record->periode_tahun, $record->periode_bulan, 1);
                    // Format ke 'Nama Bulan Tahun' (misal: Januari 2025)
                    // 'F' untuk nama bulan lengkap, 'Y' untuk tahun 4 digit
                    return $date->translatedFormat('F Y');
                }),
                TextColumn::make('siswa.nama_siswa')
                    ->sortable(),
                TextColumn::make('siswa.kelas.nama_kelas'),
                TextColumn::make('kategoriBiaya.nama_kategori')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),
                TextColumn::make('jatuh_tempo')
                    ->date()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),
                TextColumn::make('jumlah_tagihan')
                    ->numeric()
                    ->summarize(Sum::make()),
                TextColumn::make('jumlah_diskon')
                    ->numeric()
                    ->color('danger')
                    ->summarize(Sum::make()),
                TextColumn::make('tagihan_netto')
                    ->numeric()
                    ->summarize(Sum::make()),
                TextColumn::make('total_dibayar')
                    ->label('Total Dibayar')
                    ->numeric()
                    ->summarize(Sum::make()),
                TextColumn::make('sisa_tagihan')
                    ->label('Sisa Tagihan')
                    ->numeric()
                    ->summarize(Sum::make()),
                TextColumn::make('nama_diskon')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'baru' => 'info',
                        'lunas' => 'success',
                        'angsur' => 'warning',
                    })
                    ->icons([
                        'heroicon-m-check-badge' => 'lunas',
                        'heroicon-m-arrow-path' => 'angsur',
                        'heroicon-m-clock' => 'baru',
                    ]),
                TextColumn::make('jenis_tagihan')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->searchable(),
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
                SelectFilter::make('kelas_id')
                    ->label('Filter Berdasarkan Kelas')
                    ->relationship('siswa.kelas', 'nama_kelas', fn (Builder $query) => $query->orderBy('nama_kelas')) // Relasi nested
                    ->preload()
                    ->searchable(),
                SelectFilter::make('jenjang')
                    ->label('Filter Berdasarkan Jenjang')
                    ->options(
                        \App\Models\Kelas::query()
                            ->distinct()
                            ->orderBy('jenjang')
                            ->pluck('jenjang', 'jenjang')
                    )
                    ->searchable()
                    ->query(function (Builder $query, array $data): Builder {
                        if ($data['value']) {
                            return $query->whereHas('siswa.kelas', function ($q) use ($data) {
                                $q->where('jenjang', $data['value']);
                            });
                        }

                        return $query;
                    }),
                SelectFilter::make('status')
                    ->options([
                        'angsur' => 'angsur',
                        'lunas' => 'lunas',
                        'baru' => 'baru',
                    ])
                    ->multiple(),
                SelectFilter::make('jenis_tagihan')
                    ->options(Tagihan::JENIS_TAGIHAN),

                // <<< FILTER BERDASARKAN PERIODE BULAN >>>
                SelectFilter::make('periode_bulan')
                    ->label('Filter Berdasarkan Bulan')
                    ->multiple()
                    ->options(Tagihan::BULAN)
                    ->attribute('periode_bulan'), // Kolom database yang difilter
                // <<< AKHIR FILTER BULAN >>>

                // <<< FILTER BERDASARKAN PERIODE TAHUN >>>
                SelectFilter::make('periode_tahun')
                    ->label('Filter Berdasarkan Tahun')
                    ->options(Tagihan::TAHUN)
                    ->attribute('periode_tahun'), // Kolom database yang difilter
                // <<< AKHIR FILTER TAHUN >>>
            ])
            ->headerActions([
                \App\Filament\Actions\Tagihans\CreateAction::make(),
                \App\Filament\Actions\Tagihans\OptionalAction::make(),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
