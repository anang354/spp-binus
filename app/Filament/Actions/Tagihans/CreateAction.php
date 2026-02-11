<?php


namespace App\Filament\Actions\Tagihans;

use Carbon\Carbon;
use App\Models\Biaya;
use App\Models\Siswa;
use App\Models\Tagihan;
use Filament\Actions\Action;
use Illuminate\Support\Facades\DB;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Wizard;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\DatePicker;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\Placeholder;
use Filament\Schemas\Components\Wizard\Step;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;

class CreateAction
{
    public static function make(): Action
    {
        return Action::make('create')->label('Buat Tagihan Massal')->icon('heroicon-o-plus')
            ->form([
                Grid::make()
                    ->columns([
                        'sm' => 1,
                        'xl' => 6,
                    ])
                    ->schema([
                        Placeholder::make('info')
                        ->color('danger')
                        ->content('Tagihan akan dibuat otomatis untuk siswa sesuai jenjang dan kelas yang dipilih berdasarkan biaya dan diskon yang telah ditentukan')->columnSpanFull(),
                        Radio::make('jenis_tagihan')->options(\App\Models\Tagihan::JENIS_TAGIHAN)
                        ->required()
                        ->afterStateUpdated(fn (Set $set) => $set('biaya', null))
                        ->columnSpan(3),
                        Radio::make('jenjang')->options(\App\Models\Kelas::JENJANG)->required()
                        ->live()->columnSpan(3)
                        ->afterStateUpdated(fn (Set $set) => $set('kelas', null)),
                        Select::make('biaya')
                            ->options(function (Get $get) {
                                $jenisTagihan = $get('jenis_tagihan');
                                $jenjang = $get('jenjang');
                                return \App\Models\Biaya::where('jenjang', $jenjang)->where('jenis_biaya', $jenisTagihan)->pluck('nama_biaya', 'id')->toArray();
                            })
                            ->multiple()
                            ->disabled(fn (Get $get) => empty($get('jenjang')))
                            ->columnSpan(3)
                            ->required(),
                        Select::make('kelas')->options(function (Get $get): array {
                            $jenjang = $get('jenjang');
                            return \App\Models\Kelas::where('jenjang', $jenjang)->pluck('nama_kelas', 'id')->toArray();
                        })
                        ->required()
                        ->disabled(fn (Get $get) => empty($get('jenjang')))
                        ->multiple()
                        ->columnSpan(3),
                        Select::make('periode_bulan')
                        ->options([Tagihan::BULAN])
                        ->required()->columnSpan(2),
                        Select::make('periode_tahun')->options([
                            Tagihan::TAHUN
                        ])->required()->columnSpan(2),
                        DatePicker::make('jatuh_tempo')->required()->columnSpan(2)
                    ])
            ])
            ->slideOver()
            ->action(function (array $data){
                $biayaIds = $data['biaya'];
                $jenisTagihan = $data['jenis_tagihan'];
                $jenjang = $data['jenjang'];
                $kelasIds = $data['kelas'];
                $listBiaya = \App\Models\Biaya::whereIn('id', $biayaIds)->get();
                $getSiswa = \App\Models\Siswa::where('is_active', true)
                    ->whereHas('kelas', function ($query) use ($jenjang, $kelasIds) {
                        $query->where('jenjang', $jenjang)->whereIn('id', $kelasIds);
                    })
                    ->with('diskon.biaya') 
                    ->get();
                DB::beginTransaction();
                try {
                    $hitung = 0;
                    foreach($getSiswa as $siswa) {
                        $check = Tagihan::where('siswa_id', $siswa->id)
                        ->where('jenis_tagihan', $jenisTagihan)
                        ->where('periode_bulan', $data['periode_bulan'])
                        ->where('periode_tahun', $data['periode_tahun'])->count();
                        if($check === 0)
                        {
                            foreach($listBiaya as $biaya) {
                                $totalBiaya = $biaya->nominal;
                                $kategoriBiayaId = $biaya->kategori_biaya_id;
                                $totalDiskon = 0;
                                $idsDiskon = [];

                                foreach ($siswa->diskon as $diskon) {
                                    if($diskon->biaya->id === $biaya->id) {
                                        if ($diskon->tipe === 'nominal') {
                                            $totalDiskon += $diskon->nominal;
                                        } elseif ($diskon->tipe === 'persentase') {
                                            $diskonIs = $totalBiaya * ($diskon->persentase / 100);
                                            $totalDiskon += intval($diskonIs);
                                        }
                                        $idsDiskon[] = (string)$diskon->nama_diskon;
                                    }
                                }

                                $saveIdsDiskon = implode(', ', $idsDiskon);
                                $jumlahNetto = $totalBiaya - $totalDiskon;
                                //$tanggal = Carbon::createFromDate($data['periode_tahun'], $data['periode_bulan'], 1)->endOfMonth()->toDateString();

                                Tagihan::create([
                                    'siswa_id' => $siswa->id,
                                    'kategori_biaya_id' => $kategoriBiayaId,
                                    'periode_bulan' => $data['periode_bulan'],
                                    'periode_tahun' => $data['periode_tahun'],
                                    'jatuh_tempo' => $data['jatuh_tempo'],
                                    'jumlah_tagihan' => $totalBiaya,
                                    'jumlah_diskon' => $totalDiskon,
                                    'nama_tagihan' => $biaya->nama_biaya,
                                    'nama_diskon' => $saveIdsDiskon,
                                    'tagihan_netto' => $jumlahNetto,
                                    'jenis_tagihan' => $data['jenis_tagihan'],
                                    'status' => 'baru',
                                ]);
                                $hitung++;
                            }
                        }
                    }
                    DB::commit();
                    $notif = 'Berhasil membuat '.$hitung.' Tagihan';
                    Notification::make()
                    ->title('Berhasil!')
                    ->body($notif)
                    ->success()
                    ->send();
                } catch(\Exception $e) {
                    DB::rollBack();
                    Notification::make()
                    ->title('Gagal Membuat Tagihan!')
                    ->body($e)
                    ->danger()
                    ->send();
                }
            });
    }
}
