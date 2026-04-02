<?php

namespace App\Filament\Resources\KasLaporans\Schemas;

use Filament\Schemas\Schema;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;

class KasLaporanForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Hidden::make('user_id')->default(fn() => auth()->id()),
                TextInput::make('nama_laporan')
                    ->required()
                    ->maxLength(255),
                TextInput::make('catatan')
                    ->maxLength(255)
                    ->default(null),
                DatePicker::make('tanggal_mulai')
                    ->helperText('Transaksi kas akan dicatat mulai dari tanggal ini.')
                    ->required(),
                DatePicker::make('tanggal_tutup')
                ->afterOrEqual(fn () => \App\Models\KasLaporan::max('tanggal_tutup') ?? '2000-01-01')
                ->helperText('Semua transaksi sebelum dan pada tanggal ini akan dikunci permanen.')
                ->required(),
                Hidden::make('saldo_akhir_tunai'),
                Hidden::make('saldo_akhir_bank'),
                Hidden::make('total_saldo'),
                Toggle::make('is_closed')
                    ->label('Tutup Laporan')
                    ->helperText('Menandai laporan ini sebagai tutup buku. Setelah ditutup, laporan tidak dapat diubah lagi.')
                    ->default(false),
            ]);
    }
}
