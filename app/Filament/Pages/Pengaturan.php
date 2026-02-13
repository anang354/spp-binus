<?php

namespace App\Filament\Pages;

use BackedEnum;
use Filament\Pages\Page;
use Filament\Actions\Action;
use Filament\Schemas\Schema;
use Filament\Forms\Components;
use Illuminate\Support\HtmlString;
use Filament\Support\Icons\Heroicon;
use Filament\Support\Exceptions\Halt;
use Filament\Forms\Contracts\HasForms;
use Filament\Schemas\Components\Section;
use Filament\Forms\Concerns\InteractsWithForms;

class Pengaturan extends Page implements HasForms
{
    use InteractsWithForms;

    protected string $view = 'filament.pages.pengaturan';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCog6Tooth;

    public ?array $data = [];

    public ?\App\Models\Pengaturan $record = null;

    public static function canAccess() : bool
    {
        return auth()->user()->role === 'admin' || auth()->user()->role === 'editor';
    }

    public function mount(): void
    {
        $this->record = \App\Models\Pengaturan::firstOrCreate([]);
        $this->form->fill($this->record->attributesToArray());
    }
    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('informasi_sekolah')
                    ->schema([
                        Components\TextInput::make('nama_sekolah')
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
                    ])
                    ->columns(2),
                Section::make('Pesan WhhatsApp')
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
            ])->statePath('data');;
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
