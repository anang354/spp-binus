<?php

namespace App\Filament\Resources\Pembayarans\Schemas;

use Closure;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\DB;
use Filament\Forms\Components\Radio;
use Filament\Support\Icons\Heroicon;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;

class PembayaranForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                    Section::make()
                        ->columnSpan('full')
                        ->columns([
                            'sm' => 1,
                            'xl' => 2,
                            '2xl' => 2,
                        ])
                        ->schema([
                            Select::make('siswa_id')
                                ->label('Pilih Siswa')
                                ->prefixIcon(Heroicon::User)
                                ->options(\App\Models\Siswa::all()->pluck('nama_siswa', 'id'))
                                ->preload()
                                ->live()
                                ->required()
                                ->disabledOn('edit')
                                ->afterStateUpdated(fn (Set $set) => $set('tagihan_id', null))
                                ->searchable()
                                ->columnSpan(1),
                            Select::make('tagihan_id')
                                ->prefixIcon(Heroicon::Newspaper)
                                ->label('Tagihan')
                                ->options(function (callable $get) {
                                    return \App\Models\Tagihan::where('siswa_id', $get('siswa_id'))
                                        ->whereColumn('tagihan_netto', '>', DB::raw('(SELECT COALESCE(SUM(jumlah_dibayar), 0) FROM pembayarans WHERE pembayarans.tagihan_id = tagihans.id)'))
                                        ->get()
                                        ->mapWithKeys(function ($tagihan) {
                                            $label = $tagihan->nama_tagihan.' '.\Carbon\Carbon::createFromDate(null, $tagihan->periode_bulan, 1)->translatedFormat('F') . ' ' . $tagihan->periode_tahun.' - Rp.'.number_format($tagihan->sisa_tagihan, 0, ",", ".");

                                            return [$tagihan->id => $label];
                                        });
                                })
                                ->getOptionLabelUsing(function ($value): ?string {
                                    $tagihan = \App\Models\Tagihan::find($value);

                                    if (! $tagihan) {
                                        return null;
                                    }

                                    // Copy-paste formatting label yang sama seperti di atas
                                    $bulan = \Carbon\Carbon::createFromDate(null, $tagihan->periode_bulan, 1)->translatedFormat('F');
                                    // Catatan: sisa_tagihan mungkin perlu dicek aksesornnya di model Tagihan
                                    $sisa = number_format($tagihan->sisa_tagihan, 0, ",", ".");

                                    return "{$tagihan->nama_biaya} {$bulan} {$tagihan->periode_tahun} - Rp.{$sisa}";
                                })
                                ->afterStateUpdated(function ($state, Set $set, Get $get) {
                                    if ($state) {
                                        $tagihan = \App\Models\Tagihan::find($state);
                                        if ($tagihan) {
                                            $set('jumlah_dibayar', $tagihan->sisa_tagihan);
                                        }
                                    }
                                })
                                ->reactive()
                                ->searchable()
                                ->disabledOn('edit')
                                ->required()
                                ->columnSpan(1),
                        ])->columnSpanFull(),

                    Section::make()
                            ->columnSpan('full')
                            ->columns([
                                'sm' => 1,
                                'xl' => 5,
                                '2xl' => 8,
                            ])
                            ->schema([
                                Radio::make('metode_pembayaran')
                                    ->required()
                                    ->live()
                                    ->options([
                                        'tunai' => 'Tunai',
                                        'transfer' => 'Transfer',
                                    ])
                                    ->columnSpan([
                                        'sm' => 1,
                                        'xl' => 1,
                                        '2xl' => 1,
                                    ]),
                                Radio::make('bank_accounts')
                                    ->options(\App\Models\Pembayaran::BANK_ACCOUNTS)
                                        ->visible(fn (Get $get): bool => $get('metode_pembayaran') === 'transfer')
                                        ->afterStateUpdated(fn ($state) => $state)
                                        ->required(fn (Get $get): bool => $get('metode_pembayaran') === 'transfer')
                                        ->columnSpan([
                                            'sm' => 1,
                                            'xl' => 1,
                                            '2xl' => 1,
                                        ]),
                                DatePicker::make('tanggal_pembayaran')
                                    ->default(now())
                                    ->required()
                                    ->columnSpan([
                                        'sm' => 1,
                                        'xl' => 1,
                                        '2xl' => 2,
                                    ]),
                                TextInput::make('jumlah_dibayar')
                                    ->numeric()->prefix('Rp.')
                                    ->live(onBlur: true)
                                    ->label('Jumlah Dibayar (Rp)')
                                    ->hint(fn ($state) => $state ? \App\Helpers\Terbilang::make($state) : null)
                                    ->hintColor('primary')
                                    ->required()
                                    ->rules([
                                        fn ($get, $record): \Closure => function (string $attribute, $value, \Closure $fail) use ($get, $record) {
                                        $tagihanId = $get('tagihan_id');
                                        if (!$tagihanId) return;

                                        $tagihan = \App\Models\Tagihan::find($tagihanId);
                                        if (!$tagihan) return;

                                        // Hitung total bayar dari transaksi LAIN (kecuali transaksi yang sedang diedit ini)
                                        $totalTerbayarLainnya = \App\Models\Pembayaran::where('tagihan_id', $tagihanId)
                                            ->where('id', '!=', $record?->id) // Mengecualikan record saat ini
                                            ->sum('jumlah_dibayar');

                                        $sisaTagihanAsli = $tagihan->tagihan_netto - $totalTerbayarLainnya;

                                        if ($value > $sisaTagihanAsli) {
                                            $fail("Nominal melebihi sisa tagihan. Maksimal yang bisa diinput adalah Rp. " . number_format($sisaTagihanAsli, 0, ',', '.'));
                                        }
                                    },
                                    ])
                                    ->columnSpan([
                                        'sm' => 1,
                                        'xl' => 2,
                                        '2xl' => 4,
                                    ]),
                    ]),

                Section::make()
                    ->columnSpan('full')
                    ->columns([
                        'sm' => 1,
                        'xl' => 2,
                        '2xl' => 4,
                    ])
                    ->schema([
                        TextInput::make('keterangan')
                                ->columnSpan([
                                        'sm' => 'full',
                                        'xl' => 1,
                                        '2xl' => 2,
                                    ]),
                        FileUpload::make('bukti_bayar')
                                ->columnSpan([
                                    'sm' => 'full',
                                    'xl' => 1,
                                    '2xl' => 2,
                                ])
                                ->disk('local')
                                ->directory('bukti-bayar')
                                ->downloadable() // <<< Penting: Mengizinkan file didownload dari Filament
                                ->previewable() // <<< Opsional: Memungkinkan pratinjau gambar atau PDF (jika didukung browser)
                                ->visibility('private'),

                    ]),
                Toggle::make('masukkan_kas')
                    ->onIcon(Heroicon::Bookmark)
                    ->offIcon(Heroicon::BookmarkSlash)
                    ->columnSpanFull()
                    ->label('Masukkan ke Kas')
                    ->default(true)
                    ->dehydrated(false) // Penting: Agar tidak error karena kolom tidak ada di tabel pembayarans
                    ->hidden(fn (string $operation): bool => $operation === 'edit')
                    ->live(),
                Toggle::make('is_whatsapp_sent')
                    ->onIcon(Heroicon::Bell)
                    ->offIcon(Heroicon::BellSlash)
                    ->label('Kirim Notif Whatsapp')
                    ->columnSpanFull()
                    ->default(true)
                    ->hidden(fn (string $operation): bool => $operation === 'edit')
                    ->dehydrated(false),
            ]);
    }
}
