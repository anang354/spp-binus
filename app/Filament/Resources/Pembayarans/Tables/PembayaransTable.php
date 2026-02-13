<?php

namespace App\Filament\Resources\Pembayarans\Tables;

use Carbon\Carbon;
use Filament\Tables\Table;
use Filament\Actions\EditAction;
use Filament\Tables\Filters\Filter;
use Filament\Actions\BulkActionGroup;
use Filament\Forms\Components\Select;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Schemas\Components\Utilities\Get;

class PembayaransTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->query(
                \App\Models\Pembayaran::query()->orderByDesc('created_at')
            )
            ->columns([
                TextColumn::make('nomor_bayar')
                    ->copyable()
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
                TextColumn::make('bank_accounts')
                    ->label('Bank')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                            'bri' => 'danger',
                            'mandiri' => 'info',
                        }),
                TextColumn::make('tagihan.jenis_tagihan')
                    ->label('Jenis Tagihan')
                     ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('user.name')
                    ->label('operator')
                    ->sortable()
                     ->toggleable(isToggledHiddenByDefault: true),
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
                Filter::make('pembayaran_filter')
                    ->form([
                        // Filter Pertama: Metode Pembayaran
                        Select::make('metode')
                            ->label('Metode Pembayaran')
                            ->options([
                                'tunai' => 'Tunai',
                                'transfer' => 'Transfer',
                            ])
                            ->live(), // Memicu perubahan state secara real-time

                        // Filter Kedua: Bank (Hanya muncul jika 'transfer' dipilih)
                        Select::make('bank')
                            ->label('Pilih Bank')
                            ->options(\App\Models\Pembayaran::BANK_ACCOUNTS)
                            ->visible(fn (Get $get) => $get('metode') === 'transfer'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['metode'],
                                fn (Builder $query, $date): Builder => $query->where('metode_pembayaran', $data['metode']) //
                            )
                            ->when(
                                $data['bank'],
                                fn (Builder $query, $date): Builder => $query->where('bank_accounts', $data['bank'])
                            );
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        if ($data['metode'] ?? null) {
                            $indicators[] = 'Metode: ' . ucfirst($data['metode']);
                        }
                        if ($data['bank'] ?? null) {
                            $indicators[] = 'Bank: ' . $data['bank'];
                        }
                        return $indicators;
                    }),
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
