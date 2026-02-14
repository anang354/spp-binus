<?php

namespace App\Filament\Pages;

use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Infolists\Components\TextEntry;
use Filament\Pages\Page;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Exceptions\Halt;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\HtmlString;

class Pengaturan extends Page implements HasForms
{
    use InteractsWithForms;

    protected string $view = 'filament.pages.pengaturan';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCog6Tooth;

    public ?array $data = [];
    public ?array $deviceData = [];

    public ?\App\Models\Pengaturan $record = null;

    public static function canAccess() : bool
    {
        return auth()->user()->role === 'admin' || auth()->user()->role === 'editor';
    }

    public function mount(): void
    {
        $this->record = \App\Models\Pengaturan::firstOrCreate([]);
        $this->form->fill($this->record->attributesToArray());
        // Mengambil data dari API Fonnte
        $response = \Illuminate\Support\Facades\Http::withHeaders([
            'Authorization' => env('FONNTE_TOKEN'),
        ])->post('https://api.fonnte.com/device');

        if ($response->successful()) {
            $this->deviceData = $response->json();
        }
    }
    public function infolist(Schema $schema): Schema
    {
        return $schema
            ->state($this->deviceData) // Memasukkan data JSON tadi sebagai state
            ->components([
                Section::make('Informasi Perangkat Fonnte')
                    ->description('Status koneksi dan sisa kuota pengiriman pesan.')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('name')
                                    ->label('Nama Perangkat')
                                    ->weight('bold'),

                                TextEntry::make('device')
                                    ->label('Nomor WhatsApp'),

                                TextEntry::make('device_status')
                                    ->label('Status Koneksi')
                                    ->badge()
                                    ->color(fn (string $state): string => match ($state) {
                                        'connect' => 'success',
                                        'disconnect' => 'danger',
                                        default => 'gray',
                                    }),

                                TextEntry::make('package')
                                    ->label('Paket'),

                                TextEntry::make('quota')
                                    ->label('Sisa Kuota Pesan')
                                    ->numeric(),

                                TextEntry::make('expired')
                                    ->label('Masa Aktif')
                                    ->icon('heroicon-m-calendar-days'),

                                TextEntry::make('messages')
                                    ->label('Total Pesan Terkirim')
                                    ->numeric(),
                            ]),
                    ])->columns(1),
            ]);
    }
    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make([
                    'md' => 1,
                    'lg' => 3,
                ])
                ->schema([

                Section::make('Pesan WhatsApp')
                    ->columnSpan(2)
                    ->schema([
                        Components\TextInput::make('token_wa')
                            ->label('Token WhatsApp'),
                        Components\Toggle::make('wa_active')
                            ->label('Aktifkan WhatsApp'),
                        Components\Textarea::make('pesan1')
                            ->label('Pesan 1 (Broadcast Tagihan)')
                            ->label(new HtmlString('<span style="color: #006eff;">Pesan 1 (Broadcast Tagihan)</span>'))
                            ->rows(5)
                            ->columnSpan(2),
                        Components\Textarea::make('pesan2')
                            ->label(new HtmlString('<span style="color: #f08d1d;">Pesan 2 (Follow-up Tagihan)</span>'))
                            ->rows(5)
                            ->columnSpan(2),
                        Components\Textarea::make('pesan3')
                            ->label(new HtmlString('<span style="color: #1be236;">Pesan 3 (Pembayaran)</span>'))
                            ->rows(5)
                            ->columnSpan(2),
                        Components\Placeholder::make('info')
                        ->content(new \Illuminate\Support\HtmlString('
                        <p>Gunakan hanya parameter dibawah ini untuk mengisi pesan otomatis&nbsp;</p>
<p><strong>Untuk Pesan1 dan Pesan 2 berfungsi mengirim tagihan</strong></p>
<p><span style="color: #ff0000;">{nama_siswa},&nbsp;{nama_wali},&nbsp;{daftar_tagihan}&nbsp;,{total_tagihan}</span></p>
<p>&nbsp;</p>
<p><strong>Untuk Pesan3 berfungsi mengirim informasi pembayaran</strong></p>
<p><span style="color: #ff0000;">{nama_siswa},&nbsp;{nama_wali},&nbsp;{nomor_bayar}, {daftar_pembayaran}&nbsp;, {total_pembayaran}</span></p>
                                                ')),
                    ])
                    ->columns(2),
                    Section::make('Informasi Sekolah')
                    ->columnSpan(1)
                    ->schema([
                        Components\TextInput::make('nama_sekolah')
                            ->prefixIcon(Heroicon::BuildingLibrary)
                            ->label('Nama Sekolah'),
                        Components\TextInput::make('alamat_sekolah')
                            ->prefixIcon(Heroicon::OutlinedMapPin)
                            ->label('Alamat Sekolah'),
                        Components\TextInput::make('telepon_sekolah')
                            ->numeric()
                            ->prefixIcon(Heroicon::OutlinedPhone)
                            ->label('Telepon Sekolah'),
                        Components\FileUpload::make('logo_sekolah')
                            ->label('Logo Sekolah')
                            ->disk('public')
                            ->image()
                            ->imageEditor(),
                    ]),
                ])
            ])
            ->statePath('data');
    }
    protected function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label('Simpan Pengaturan')
                ->button()
                ->color('primary')
                ->action('save'),
        ];
    }
    public function save(): void
    {
        try {
            $data = $this->form->getState();

            $this->record->update($data);
        } catch (Halt $exception) {
            return;
        }

        \Filament\Notifications\Notification::make()
            ->success()
            ->title('Berhasil menyimpan data')
            ->send();
    }
}
