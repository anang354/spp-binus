<?php

namespace App\Filament\Pages;

use BackedEnum;
use UnitEnum;
use App\Models\Siswa;
use Filament\Pages\Page;
use Filament\Tables\Table;
use Filament\Actions\Action;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Components\Select;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Columns\TagsColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Contracts\HasTable;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Placeholder;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Tables\Concerns\InteractsWithTable;

class DiskonSiswa extends Page implements  HasForms, HasTable
{
    use InteractsWithTable, InteractsWithForms;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedAtSymbol;
    protected static string | UnitEnum | null $navigationGroup = 'Diskon';
    protected static ?int $navigationSort = 6;

    public $siswa_id;
    public $diskon_ids = [];
    public $editSiswaId = null;
    public $editDiskonIds = [];
    public $isEditModalOpen = false;

 
    protected string $view = 'filament.pages.diskon-siswa';

    protected function getTableQuery()
    {
        return Siswa::query()->whereHas('diskon')->with('diskon');
    }
    public function table(Table $table): Table
    {
    return $table
        ->query(Siswa::query()->whereHas('diskon')->with('diskon'))
        ->columns([
            TextColumn::make('nama_siswa')
                ->label('Nama Siswa')
                ->sortable()
                ->searchable(),
            TextColumn::make('kelas.nama_kelas')
                ->label('Kelas'),

            TagsColumn::make('diskon')
                ->label('Diskon Dimiliki')
                ->getStateUsing(fn ($record) => $record->diskon->pluck('nama_diskon')->toArray())
                ->separator(',')
                ->limit(4),
        ])
        ->actions([
            Action::make('edit')->label('Edit Diskon')
            ->icon('heroicon-o-pencil-square')
            ->form([
                Placeholder::make('siswa')
                ->content(fn ($record): string => $record->nama_siswa),
                // <<< PERBAIKAN PENTING DI SINI >>>
                Select::make('diskon_ids') // <<< GANTI DARI 'diskon_ids' MENJADI 'diskon'
                    ->label('Pilih Diskon')
                    ->multiple()
                    ->options(
                        \App\Models\Diskon::pluck('nama_diskon', 'id')
                    )
                    ->default(fn ($record) => $record->diskon->pluck('id')->toArray())
                    ->preload()
                    ->searchable()
                    ->placeholder('Pilih diskon yang dimiliki siswa')
                    ->helperText('Pilih diskon yang ingin diberikan kepada siswa. Untuk menghapus diskon, cukup klik x pada diskon yang sudah dipilih.'),
                // <<< AKHIR PERBAIKAN >>>
                ])
            ->action(function ($record, array $data) {
                    $siswa = $record;
        
                    // Buat pivot data dengan user_id
                    $pivotData = collect($data['diskon_ids'])->mapWithKeys(function ($diskonId) {
                        return [$diskonId => ['user_id' => Auth::id()]];
                    })->toArray();
        
                    $siswa->diskon()->sync($pivotData); // Replace diskon siswa
                })
            ->modalHeading('Edit Diskon Siswa')
        ]);
    }
    

    public function submit()
    {
        $this->validate([
            'siswa_id' => 'required|exists:siswas,id',
            'diskon_ids' => 'required|array|min:1',
            'diskon_ids.*' => 'exists:diskons,id',
        ]);

        $siswa = Siswa::find($this->siswa_id);

        $userId = auth()->user()->id;

        // Buat array pivot data
        $pivotData = collect($this->diskon_ids)->mapWithKeys(function ($diskonId) use ($userId) {
            return [$diskonId => ['user_id' => $userId]];
        })->toArray();

        $siswa->diskon()->syncWithoutDetaching($pivotData); // tambahkan dengan user_id

        $this->reset(['siswa_id', 'diskon_ids']);
        $this->dispatch('notify', type: 'success', message: 'Diskon berhasil ditambahkan ke siswa.');
    }

    public function getFormSchema(): array
    {
        return [
            Select::make('siswa_id')
                ->label('Pilih Siswa')
                ->options(\App\Models\Siswa::pluck('nama_siswa', 'id'))
                ->searchable()
                ->live()
                ->required(),

            Select::make('diskon_ids')
                ->label('Pilih Diskon')
                ->multiple()
                ->options(function (Get $get){
                    $siswaId = $get('siswa_id');
                    if (! $siswaId) {
                        return [];
                    }
                    // Ambil data siswa beserta jenjang dari kelasnya
                    $siswa = \App\Models\Siswa::with('kelas')->find($siswaId);
                    if (! $siswa || ! $siswa->kelas) {
                        return [];
                    }
                    $jenjangSiswa = $siswa->kelas->jenjang;
                    // Tampilkan diskon yang memiliki jenjang yang sama dengan siswa
                    return \App\Models\Diskon::query()
                        ->where('jenjang', $jenjangSiswa)
                        ->pluck('nama_diskon', 'id');
                })
                ->preload()
                ->required(),
        ];
    }

    public function openEditModal($siswaId)
    {
        $this->editSiswaId = $siswaId;

        $siswa = \App\Models\Siswa::with('diskon')->findOrFail($siswaId);
        $this->editDiskonIds = $siswa->diskon->pluck('id')->toArray();

        $this->isEditModalOpen = true;
    }

}
