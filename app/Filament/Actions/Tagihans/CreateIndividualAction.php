<?php


namespace App\Filament\Actions\Tagihans;

use Carbon\Carbon;
use App\Models\Tagihan;
use Filament\Actions\Action;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Resources\RelationManagers\RelationManager;

class CreateIndividualAction
{

    public static function make(): Action
    {
        return Action::make('createIndividual')->label('Buat Tagihan Baru')->icon('heroicon-o-plus')
        ->form([
            Section::make('')
            ->schema([
                Select::make('jenis_tagihan')
                ->options(Tagihan::JENIS_TAGIHAN),
                Select::make('kategori_biaya_id')
                ->live()
                ->afterStateUpdated(fn (Set $set) => $set('nama_biaya', null))
                ->options(\App\Models\KategoriBiaya::all()->pluck('nama_kategori', 'id')->toArray()),
                Select::make('nama_tagihan')
                    ->required()
                    ->options(fn (RelationManager $livewire, callable $get) =>
                    optional($livewire->getOwnerRecord()->kelas)->jenjang
                        ? \App\Models\Biaya::where('jenjang', $livewire->getOwnerRecord()->kelas->jenjang)
                        ->where('kategori_biaya_id', $get('kategori_biaya_id'))
                        ->pluck('nama_biaya', 'nama_biaya')
                        ->toArray()
                        : []
                    ),
                TextInput::make('nama_diskon'),
                Select::make('periode_bulan')
                ->multiple()
                ->options(Tagihan::BULAN),
                Select::make('periode_tahun')
                ->options(Tagihan::TAHUN),
                TextInput::make('jumlah_tagihan')->numeric()->required()
                ->live(onBlur: true)
                ->afterStateUpdated(function (callable $set, callable $get) {
                    $tagihan = (int) $get('jumlah_tagihan');
                    $diskon = (int) $get('jumlah_diskon');
                    $set('tagihan_netto', max($tagihan - $diskon, 0));
                }),
                TextInput::make('jumlah_diskon')->numeric()
                ->default(0)
                ->live(onBlur: true)
                ->afterStateUpdated(function (callable $set, callable $get) {
                    $tagihan = (int) $get('jumlah_tagihan');
                    $diskon = (int) $get('jumlah_diskon');
                    $set('tagihan_netto', max($tagihan - $diskon, 0));
                }),
                TextInput::make('tagihan_netto')->numeric()->required()->columnSpan('full')
                ->disabled()
                ->dehydrated() // agar tetap disimpan walau disabled
                ->hint(fn ($get) => 'Terbilang : ' . \App\Helpers\Terbilang::make((int) $get('tagihan_netto')))
                ->hintColor('gray'),
                Radio::make('status')->required()
                ->options([
                    'baru' => 'baru',
                    'lunas' => 'lunas',
                    'angsur' => 'angsur',
                ])->default('baru'),
                TextInput::make('keterangan'),
            ])
            ->columns(2)
        ])
        ->action(function (array $data, RelationManager $livewire) {

            $siswaId = $livewire->getOwnerRecord()->id;
            // Pastikan tidak ada duplikat (untuk jaga-jaga)
            foreach($data['periode_bulan'] as $bulan) {
                $exists = Tagihan::where('siswa_id', $siswaId)
                ->where('periode_bulan', $bulan)
                ->where('periode_tahun', $data['periode_tahun'])
                ->where('nama_tagihan', $data['nama_tagihan'])
                ->exists();

            if ($exists) {
                Notification::make()
                    ->title('Tagihan gagal dibuat')
                    ->body('Periode yang dipilih sudah memiliki tagihan yang sama.')
                    ->danger()
                    ->send();
                return;
            }
            $tanggal = Carbon::createFromDate($data['periode_tahun'], $bulan, 1)->endOfMonth()->toDateString();
            // Simpan data
            Tagihan::create([
                'siswa_id' => $siswaId,
                'kategori_biaya_id' => $data['kategori_biaya_id'],
                'periode_bulan' => $bulan,
                'periode_tahun' => $data['periode_tahun'],
                'jatuh_tempo' => $tanggal,
                'jumlah_tagihan' => $data['jumlah_tagihan'],
                'jumlah_diskon' => $data['jumlah_diskon'],
                'tagihan_netto' => $data['jumlah_tagihan'] - $data['jumlah_diskon'],
                'nama_tagihan' => $data['nama_tagihan'],
                'nama_diskon' => $data['nama_diskon'],
                'status' => 'baru',
                'jenis_tagihan' => $data['jenis_tagihan'],
                'keterangan' => $data['keterangan'],
            ]);
            }
            

            Notification::make()
                ->title('Tagihan berhasil dibuat')
                ->success()
                ->send();
        });

    }

}
