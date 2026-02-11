<?php

namespace App\Filament\Resources\Diskons\Schemas;

use Filament\Schemas\Schema;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;

class DiskonForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Radio::make('tipe')
                    ->options(['persentase' => 'Persentase', 'nominal' => 'Nominal'])
                    ->live()
                    ->required(),
                Select::make('jenjang')
                    ->required()
                    ->live()
                    ->options(\App\Models\Kelas::JENJANG),
                TextInput::make('nama_diskon')
                    ->required(),
                Select::make('biaya_id')
                    ->label('Biaya yang didiskon')
                   ->options(function (Get $get) {
                        // Mengambil nilai 'jenjang' yang sedang dipilih
                        $jenjang = $get('jenjang');
                        // Jika jenjang belum dipilih, jangan tampilkan opsi biaya
                        if (! $jenjang) {
                            return [];
                        }
                        // Query biaya berdasarkan jenjang yang dipilih
                        return \App\Models\Biaya::query()
                            ->where('jenjang', $jenjang)
                            ->get()
                            ->mapWithKeys(fn ($a) => [
                                $a->id => "{$a->nama_biaya} - {$a->jenjang}"
                            ]);
                    })
                    // Opsional: disable jika jenjang belum dipilih agar user tidak bingung
                    ->disabled(fn (Get $get) => empty($get('jenjang')))
                    ->required(),
                Section::make('Diskon')->schema([
                    TextInput::make('persentase')->suffix('%')->columnSpan(1)
                        ->visible(fn (callable $get) => $get('tipe') === 'persentase'),
                    TextInput::make('nominal')->numeric()->prefix('Rp.')->columnSpan('full')
                    ->live(onBlur: true) // Update setiap kali mengetik
                    ->hint(fn ($state) => $state ? \App\Helpers\Terbilang::make($state) : null)
                    ->hintColor('primary')
                    ->visible(fn (callable $get) => $get('tipe') === 'nominal'),
                    TextInput::make('keterangan')->columnSpan('full')
                ])->columns(6)->columnSpanFull(),
            ]);
    }
}
