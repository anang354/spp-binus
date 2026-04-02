<?php

namespace App\Filament\Resources\KasTransaksis\Schemas;

use Filament\Schemas\Schema;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\DatePicker;

class KasTransaksiForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Hidden::make('user_id')
                    ->default(fn () => auth()->id()),
                Section::make('Model Transaksi Kas')
                ->columnSpanFull()
                ->schema([
                    TextInput::make('nomor_referensi')
                        ->default(function() {
                            $prefix = date('mY');
                            $random = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
                            return $prefix . $random;
                        })
                        ->readonly()
                        ->required()
                        ->unique(ignoreRecord: true),
                    Radio::make('jenis_transaksi')
                        ->options([
                            'masuk' => 'Masuk',
                            'keluar' => 'Keluar',
                        ])
                        ->required(),
                        Radio::make('metode')
                        ->options([
                            'tunai' => 'Tunai',
                            'non-tunai' => 'Non-Tunai',
                        ])
                        ->required(),
                    ])->columns(3),
                    Section::make('Detail')
                        ->columnSpanFull()
                        ->schema([
                        Select::make('kas_kategori_id')
                            ->relationship('kategori', 'nama_kategori')
                            ->searchable()
                            ->preload()
                            ->createOptionForm([
                                TextInput::make('nama_kategori')
                                    ->required()
                                    ->maxLength(255),
                            ])
                            ->required(),
                        DatePicker::make('tanggal_transaksi')
                            ->default(now())
                            ->required(),
                        TextInput::make('jumlah')
                            ->live(onBlur: true) // agar update terbilang secara live
                            ->hint(fn ($state) => $state ? \App\Helpers\Terbilang::make($state) : null)
                            ->hintColor('primary')
                            ->required()
                            ->numeric(),
                        TextInput::make('keterangan')
                            ->maxLength(255)
                            ->default(null),
                    ])->columns(2)
            ]);
    }
}
