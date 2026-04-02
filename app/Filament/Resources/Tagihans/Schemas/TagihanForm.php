<?php

namespace App\Filament\Resources\Tagihans\Schemas;

use Carbon\Carbon;
use Filament\Schemas\Schema;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Grid;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Resources\RelationManagers\RelationManager;

class TagihanForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make([
                        'default' => 1,
                        'sm' => 1,
                        'md' => 4,
                    ])->columnSpan('full')
                        ->schema([
                            Placeholder::make('siswa')
                                ->content(fn ($record): string => $record->siswa->nama_siswa)
                                ->hidden(fn ($livewire) => $livewire instanceof RelationManager),
                            Placeholder::make('kelas')
                                ->content(fn ($record): string => $record->siswa->kelas->nama_kelas)
                                ->hidden(fn ($livewire) => $livewire instanceof RelationManager),
                            Placeholder::make('periode')
                                ->content(function ($record): string {
                                    $date = Carbon::createFromDate($record->periode_tahun, $record->periode_bulan, 1);
                                    // Format ke 'Nama Bulan Tahun' (misal: Januari 2025)
                                    // 'F' untuk nama bulan lengkap, 'Y' untuk tahun 4 digit
                                    return $record->nama_tagihan.' '. $date->translatedFormat('F Y');
                                }),
                            Placeholder::make('jenis_tagihan')
                                ->content(fn ($record): string => $record->jenis_tagihan),
                        ]),
                    Grid::make([
                        'default' => 1,
                        'sm' => 1,
                        'md' => 2,
                    ])
                        ->schema([
                            TextInput::make('jumlah_tagihan')
                                ->numeric()
                                ->label('Jumlah Tagihan')
                                ->live(debounce: 500)
                                ->afterStateUpdated(function (callable $set, callable $get) {
                                    $tagihan = (int) $get('jumlah_tagihan');
                                    $diskon = (int) $get('jumlah_diskon');
                                    $set('tagihan_netto', max($tagihan - $diskon, 0));
                                }),

                            TextInput::make('jumlah_diskon')
                                ->numeric()
                                ->label('Jumlah Diskon')
                                ->live(debounce: 500)
                                ->afterStateUpdated(function (callable $set, callable $get) {
                                    $tagihan = (int) $get('jumlah_tagihan');
                                    $diskon = (int) $get('jumlah_diskon');
                                    $set('tagihan_netto', max($tagihan - $diskon, 0));
                                }),
                        ]),
                    TextInput::make('tagihan_netto')
                        ->numeric()
                        ->label('Jumlah Netto')
                        ->disabled()
                        ->dehydrated() // agar tetap disimpan walau disabled
                        ->hint(fn ($get) => 'Terbilang : ' . \App\Helpers\Terbilang::make((int) $get('tagihan_netto')))
                        ->hintColor('gray'),
                    TextInput::make('nama_diskon'),
                    DatePicker::make('jatuh_tempo')->required(),
                    Select::make('status')
                        ->options([
                            'baru' => 'baru',
                            'angsur' => 'angsur',
                            'lunas' => 'lunas',
                        ]),
            ]);
    }
}
