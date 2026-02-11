<?php

namespace App\Filament\Resources\Pembayarans\Schemas;

use Closure;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\DB;
use Filament\Forms\Components\Radio;
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
                                ->options(\App\Models\Siswa::all()->pluck('nama_siswa', 'id'))
                                ->preload()
                                ->live()
                                ->required()
                                ->disabledOn('edit')
                                ->afterStateUpdated(fn (Set $set) => $set('tagihan_id', null))
                                ->searchable()
                                ->columnSpan(1),
                            Select::make('tagihan_id')
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
                                'xl' => 4,
                                '2xl' => 8,
                            ])
                            ->schema([
                                Radio::make('metode_pembayaran')
                                    ->required()
                                    ->options([
                                        'tunai' => 'Tunai',
                                        'transfer' => 'Transfer',
                                    ])
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
                                        fn ($get): \Closure => function (string $attribute, $value, \Closure $fail) use ($get) {
                                            $tagihanId = $get('tagihan_id');
                                            if (!$tagihanId) return;

                                            $tagihan = \App\Models\Tagihan::find($tagihanId);
                                            if (!$tagihan) return;

                                            // Hitung sisa tagihan asli (tagihan_netto dikurangi pembayaran yang sudah masuk sebelumnya)
                                            $totalTerbayar = \App\Models\Pembayaran::where('tagihan_id', $tagihanId)->sum('jumlah_dibayar');
                                            $sisaTagihan = $tagihan->tagihan_netto - $totalTerbayar;

                                            if ($value > $sisaTagihan) {
                                                $fail("Nominal melebihi sisa tagihan. Maksimal pembayaran adalah Rp. " . number_format($sisaTagihan, 0, ',', '.'));
                                            }
                                        },
                                    ])
                                    ->disabled(function (string $operation) {
                                        if ($operation === 'edit') {
                                            // Jika user punya role 'editor' atau BUKAN 'admin', maka disabled
                                            return auth()->user()->isEditor();
                                        }
                                        return false;
                                    })
                                    ->columnSpan([
                                        'sm' => 1,
                                        'xl' => 2,
                                        '2xl' => 5,
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
                    ->label('Masukkan ke Kas')
                    ->default(true)
                    ->dehydrated(false) // Penting: Agar tidak error karena kolom tidak ada di tabel pembayarans
                    ->hidden(fn (string $operation): bool => $operation === 'edit')
                    ->live(),
                Toggle::make('is_whatsapp_sent')
                    ->label('Kirim Notif Whatsapp')
                    ->default(true)
                    ->hidden(fn (string $operation): bool => $operation === 'edit')
                    ->dehydrated(false),
            ]);
    }
}
