<?php

namespace App\Filament\Resources\Pembayarans\Tables;

use Carbon\Carbon;
use Filament\Tables\Table;
use Filament\Actions\EditAction;
use Filament\Tables\Filters\Filter;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Columns\Summarizers\Sum;

class PembayaransTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nomor_bayar')
                    ->searchable(),
                TextColumn::make('siswa.nama_siswa')
                    ->sortable(),
                TextColumn::make('periode_tagihan')
                    ->label('Nama Tagihan')
                    ->getStateUsing(function ($record) {
                        $bulan = $record->tagihan->periode_bulan ?? null;
                        $tahun = $record->tagihan->periode_tahun ?? null;

                        if ($bulan && $tahun) {
                            return $record->tagihan->nama_tagihan. ' ' .Carbon::createFromDate(null, $bulan, 1)->translatedFormat('F') . ' ' . $tahun;
                        }

                        return '-';
                }),
                TextColumn::make('tanggal_pembayaran')
                    ->date()
                    ->sortable(),
                TextColumn::make('jumlah_dibayar')
                    ->numeric()
                    ->summarize(Sum::make()),
                TextColumn::make('metode_pembayaran')
                    ->label('metode')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                            'tunai' => 'success',
                            'transfer' => 'warning',
                        }),
                TextColumn::make('tagihan.jenis_tagihan')
                    ->label('Jenis Tagihan')
                     ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('user.name')
                    ->label('operator')
                    ->sortable(),
                TextColumn::make('keterangan')
                    ->toggleable(isToggledHiddenByDefault: true),
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
                SelectFilter::make('metode_pembayaran')
                    ->options([
                        'tunai' => 'Tunai',
                        'transfer' => 'Transfer',
                    ]),
                SelectFilter::make('kategori_biaya')
                    ->label('Kategori Biaya')
                    ->relationship('tagihan.kategoriBiaya', 'nama_kategori') // Menggunakan dot notation untuk relasi bersarang
                    ->searchable()
                    ->preload(),
                SelectFilter::make('jenis_tagihan')
                    ->label('Jenis Tagihan')
                    ->options(\App\Models\Tagihan::JENIS_TAGIHAN)
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            $data['value'],
                            fn (Builder $query, $value): Builder => $query->whereHas(
                                'tagihan',
                                fn ($q) => $q->where('jenis_tagihan', $value)
                            )
                        );
                    }),
                Filter::make('Tanggal Pembayaran')
                    ->form([
                        DatePicker::make('tanggal_mulai')->label('Dari'),
                        DatePicker::make('tanggal_selesai')->label('Sampai'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when($data['tanggal_mulai'], fn ($q, $date) => $q->whereDate('tanggal_pembayaran', '>=', $date))
                            ->when($data['tanggal_selesai'], fn ($q, $date) => $q->whereDate('tanggal_pembayaran', '<=', $date));
                    }),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ])->visible(fn () => auth()->user()->role !== 'viewer'),
            ]);
    }
}
