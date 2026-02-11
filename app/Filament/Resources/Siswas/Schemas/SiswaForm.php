<?php

namespace App\Filament\Resources\Siswas\Schemas;

use Filament\Schemas\Schema;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\FileUpload;

class SiswaForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('kelas_id')
                    ->label('Kelas')
                    ->required()
                    ->options(
                        \App\Models\Kelas::all()->pluck('nama_kelas', 'id')
                    ),
                TextInput::make('nama_siswa')
                    ->required(),
                Radio::make('jenis_kelamin')
                    ->options(['laki-laki' => 'Laki laki', 'perempuan' => 'Perempuan'])
                    ->required(),
                TextInput::make('nama_wali')
                    ->default(null),
                TextInput::make('alamat')
                    ->default(null),
                TextInput::make('nomor_hp')
                    ->numeric()
                    ->default(null),
                Toggle::make('is_active')
                    ->default(true)
                    ->required(),
                FileUpload::make('foto')
                    ->disk('public')
                    ->directory('siswa/foto')
                    ->image()
                    ->imageEditor()
                    ->optimize('webp')
                    ->resize(50)
                    ->default(null),
            ]);
    }
}
