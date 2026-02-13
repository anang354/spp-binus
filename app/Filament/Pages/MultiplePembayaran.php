<?php

namespace App\Filament\Pages;

use UnitEnum;
use BackedEnum;
use Carbon\Carbon;
use Filament\Pages\Page;
use App\Models\Pembayaran;
use Filament\Actions\Action;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\DB;
use Filament\Forms\Components\Radio;
use Filament\Support\Icons\Heroicon;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Support\Exceptions\Halt;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Forms\Concerns\InteractsWithForms;

class MultiplePembayaran extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $model = Pembayaran::class;
    protected string $view = 'filament.pages.multiple-pembayaran';
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSwatch;
    protected static string | UnitEnum | null $navigationGroup = 'Keuangan';
    protected static ?int $navigationSort = 1;

     public ?array $data = [];
     public static function canAccess() : bool
    {
        return auth()->user()->role === 'admin' || auth()->user()->role === 'editor';
    }
    public function mount(): void
    {
        $this->form->fill(); // Or with specific data: $this->form->fill($this->record->toArray());
    }
    public function form(Schema $schema): Schema
    {
        return $schema
            ->statePath('data')
            ->components([
                Section::make()
                    ->columns([
                        'sm' => 4,
                        'xl' => 8,
                        '2xl' => 9,
                    ])
                    ->schema([
                        Radio::make('metode_pembayaran')
                            ->live()
                            ->required()
                            ->options([
                                'tunai' => 'Tunai',
                                'transfer' => 'Transfer',
                            ])
                            ->columnSpan([
                                'sm' => 1,
                                'xl' => 2,
                                '2xl' => 1,
                            ]),
                        Radio::make('bank_accounts')
                            ->options(\App\Models\Pembayaran::BANK_ACCOUNTS)
                            ->visible(fn (Get $get): bool => $get('metode_pembayaran') === 'transfer')
                            ->afterStateUpdated(fn ($state) => $state)
                            ->required(fn (Get $get): bool => $get('metode_pembayaran') === 'transfer')
                            ->columnSpan([
                                'sm' => 1,
                                'xl' => 2,
                                '2xl' => 1,
                        ]),
                        DatePicker::make('tanggal_pembayaran')
                            ->default(now())
                            ->required()
                            ->columnSpan([
                                'sm' => 2,
                                'xl' => 2,
                                '2xl' => 3,
                            ]),
                        TextInput::make('keterangan')
                            ->columnSpan([
                                'sm' => 'full',
                                'xl' => 2,
                                '2xl' => 3,
                            ]),
                    ]),
                Select::make('siswa_id')
                    ->label('Pilih Siswa')
                    ->options(\App\Models\Siswa::all()->pluck('nama_siswa', 'id')->toArray())
                    ->searchable()
                    ->reactive()
                    ->required()
                    ->afterStateUpdated(fn ($state, Set $set) => $set('Tagihan', [])),

                Repeater::make('Tagihan')
                    ->label('Daftar Tagihan')
                    ->addActionLabel('Tambah Tagihan yang akan dibayar')
                    ->reactive()
                    ->afterStateUpdated(fn (Set $set, Get $get) => self::updateTotalSemua($set, $get)) // Update saat baris dihapus
                    ->schema([
                        Select::make('tagihan_id')
                            ->label('Tagihan')
                            ->required()
                            ->reactive()
                            ->distinct()
                            ->disableOptionsWhenSelectedInSiblingRepeaterItems()
                            ->options(function (Get $get) {
                                $siswaId = $get('../../siswa_id');
                                if (!$siswaId) return [];

                                return \App\Models\Tagihan::where('siswa_id', $siswaId)
                                    ->whereRaw('(tagihan_netto - (SELECT COALESCE(SUM(jumlah_dibayar), 0) FROM pembayarans WHERE pembayarans.tagihan_id = tagihans.id)) > 0')
                                    ->get()
                                    ->mapWithKeys(function ($tagihan) {
                                        $bulan = \Carbon\Carbon::createFromDate(null, $tagihan->periode_bulan, 1)->translatedFormat('F');
                                        return [$tagihan->id => "{$tagihan->nama_tagihan} - {$bulan} {$tagihan->periode_tahun} - Rp. " . number_format($tagihan->sisa_tagihan, 0, ",", ".")];
                                    });
                            })
                            ->afterStateUpdated(function ($state, Set $set, Get $get) {
                                if ($state) {
                                    $tagihan = \App\Models\Tagihan::find($state);
                                    if ($tagihan) {
                                        $set('jumlah_dibayar', $tagihan->sisa_tagihan);
                                    }
                                }
                                // Paksa update total semua setelah tagihan dipilih dan nominal otomatis terisi
                                self::updateTotalSemua($set, $get);
                            }),

                        TextInput::make('jumlah_dibayar')
                            ->numeric()
                            ->required()
                            ->hint(fn ($state) => $state ? \App\Helpers\Terbilang::make($state) : null)
                            ->hintColor('primary')
                            ->live(onBlur: true) // Mengirim state ke server saat kursor keluar dari box
                            ->rules([
                                fn (Get $get): \Closure => function (string $attribute, $value, \Closure $fail) use ($get) {
                                    $tagihanId = $get('tagihan_id');
                                    if (!$tagihanId) return;

                                    $tagihan = \App\Models\Tagihan::find($tagihanId);
                                    if (!$tagihan) return;

                                    $totalTerbayar = \App\Models\Pembayaran::where('tagihan_id', $tagihanId)->sum('jumlah_dibayar'); //
                                    $sisaTagihan = $tagihan->tagihan_netto - $totalTerbayar; //

                                    if ($value > $sisaTagihan) {
                                        $fail("Nominal melebihi sisa tagihan. Maksimal adalah Rp. " . number_format($sisaTagihan, 0, ',', '.'));
                                    }
                                },
                            ])
                            ->afterStateUpdated(fn (Set $set, Get $get) => self::updateTotalSemua($set, $get)),
                    ])
                    ->columns(2),
                Grid::make([
                    'sm' => 1,
                    'md' => 1,
                    'lg' => 2,
                ])
                ->schema([
                    TextInput::make('total_semua_dibayar')
                        ->label('Total Semua Dibayar')
                        ->disabled()
                        ->dehydrated(true)
                        ->prefix('Rp. ')
                        ->numeric()
                        ->default(0)
                        ->live()
                        ->hint(fn ($get) => \App\Helpers\Terbilang::make((int) $get('total_semua_dibayar')))
                        ->hintColor('warning'),
                    FileUpload::make('bukti_bayar')
                        ->disk('local')
                        ->directory('bukti-bayar')
                        ->downloadable() // <<< Penting: Mengizinkan file didownload dari Filament
                        ->previewable() // <<< Opsional: Memungkinkan pratinjau gambar atau PDF (jika didukung browser)
                        ->visibility('private'),
                ]),
                Toggle::make('masukkan_kas')
                    ->label('Masukkan ke Kas')
                    ->default(true),
                Toggle::make('is_whatsapp')
                    ->label('Kirim Notif WA')
                    ->default(true),
            ]);
    }
    public static function updateTotalSemua(Set $set, Get $get): void
    {
        // Mengambil state 'Tagihan' dari level yang tepat
        // Jika dipanggil dari dalam repeater, gunakan ../../Tagihan
        // Jika dipanggil dari level repeater, gunakan Tagihan
        $repeaterState = $get('Tagihan') ?? $get('../../Tagihan') ?? [];

        $total = collect($repeaterState)
            ->map(fn ($item) => (int) ($item['jumlah_dibayar'] ?? 0))
            ->sum();

        // Set field total_semua_dibayar yang berada di luar repeater
        $set('../../total_semua_dibayar', $total);
    }

    protected function getFormActions(): array
    {
        return [
            // Tombol 1: Buat (Simpan & Redirect)
            Action::make('create')
                ->label('Buat Pembayaran')
                ->icon('heroicon-m-check')
                 ->submit('create'), // Memanggil function create()

            // Tombol 2: Buat & Buat Lainnya (Simpan & Reset Form)
            Action::make('createAnother')
                ->label('Buat & Buat Lainnya')
                ->icon('heroicon-m-plus')
                ->color('gray')
                 ->action('createAnother'), // Memanggil function createAnother()

            // Tombol 3: Batal (Kembali ke Index)
            Action::make('cancel')
                ->label('Batal')
                ->color('gray')
                ->url(fn () => auth()->user()->can('view_any_pembayaran')
                ? \App\Filament\Resources\PembayaranResource::getUrl('index')
                : url('/admin')),
        ];
    }
    protected function handleSave(): void
{
    $nomorBayar = Pembayaran::generateNomorBayar();
    try {
        $data = $this->form->getState();
        $getSiswa = \App\Models\Siswa::select('nama_siswa', 'nomor_hp')->findOrFail($data['siswa_id']);
        $siswaNama = $getSiswa->nama_siswa;
        $target = $getSiswa->nomor_hp;
        $tanggalPembayaran = Carbon::parse($data['tanggal_pembayaran'])->translatedFormat('d F Y');
         $totalBayar = 0;
         $itemBayar = "";
        \DB::beginTransaction();
        foreach ($data['Tagihan'] as $bayar) {
            $tagihan = \App\Models\Tagihan::find($bayar['tagihan_id']);
            $bulanNama = \App\Models\Tagihan::BULAN[$tagihan->periode_bulan] ?? '-'; // Handle jika key tidak ada
            // $templatePesan .= "- {$tagihan->daftar_biaya} - {$bulanNama} {$tagihan->periode_tahun} : Rp. " . number_format($bayar['jumlah_dibayar'], 0, ",", ".") . "\n";
            $totalBayar += $bayar['jumlah_dibayar'];
            $itemBayar .= ' '.$tagihan->kategoriBiaya->nama_kategori.' '.$bulanNama;

            Pembayaran::create([
                'siswa_id' => $data['siswa_id'],
                'user_id' => auth()->user()->id,
                'tagihan_id' => $bayar['tagihan_id'],
                'jumlah_dibayar' => $bayar['jumlah_dibayar'],
                'tanggal_pembayaran' => $data['tanggal_pembayaran'],
                'metode_pembayaran' => $data['metode_pembayaran'],
                'bank_accounts' => $data['bank_accounts'] ?? null,
                'keterangan' => $data['keterangan'] ?? null,
                'bukti_bayar' => $data['bukti_bayar'] ?? null,
                'nomor_bayar' => $nomorBayar,
            ]);
        }
        // Logika simpan data tagihan/pembayaran Anda di sini...
        // (Gunakan logic yang sudah kita buat sebelumnya)
        if ($data['masukkan_kas'] ?? false) {

            // Dapatkan kategori kas urutan pertama
            $kategori = \App\Models\KasKategori::first();

            if ($kategori) {
                \App\Models\KasTransaksi::create([
                    'user_id' => auth()->id(),
                    'kas_kategori_id' => $kategori->id,
                    'tanggal_transaksi' => $data['tanggal_pembayaran'],
                    'nomor_referensi' => 'P'.$nomorBayar,
                    'jenis_transaksi' => 'masuk',
                    // Mapping metode: transfer di pembayaran = non-tunai di kas
                    'metode' => $data['metode_pembayaran'] === 'tunai' ? 'tunai' : 'non-tunai',
                    'jumlah' => $totalBayar,
                    // Keterangan: Nomor Bayar + Nama Siswa
                    'keterangan' => "{$getSiswa->nama_siswa} {$itemBayar}",
                ]);
            }
        }
        \DB::commit();
        Notification::make()
                    ->success()
                    ->title('Pembayaran berhasil disimpan')
                    ->send();

    } catch (Halt $exception) {
        \DB::rollBack();
        return;
    } catch (\Exception $e) {
        \DB::rollBack();
        Notification::make()->danger()->title('Gagal!')->body($e->getMessage())->send();
        throw $e;
    }
}

public function create()
{
    try {
        $this->handleSave();
        // Redirect setelah sukses
        $this->redirect('/admin/pembayarans');
    } catch (\Exception $e) {
        // Error sudah dihandle di processPayment, biarkan form tetap terbuka
    }
}

public function createAnother(): void
{
    $this->handleSave();

    // Reset form agar kosong kembali untuk inputan berikutnya
    $this->form->fill();

    // Reset total jika Anda menggunakan state manual
    $this->data['total_semua_dibayar'] = 0;
}
}
