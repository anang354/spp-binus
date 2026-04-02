<?php

namespace App\Filament\Resources\Biayas\Schemas;

use Filament\Schemas\Schema;
use Filament\Forms\Components\Radio;
use Filament\Support\Icons\Heroicon;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;

class BiayaForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('nama_biaya')
                    ->required()->columnSpan(2),
                 Select::make('kategori_biaya_id')
                    ->prefixIcon(Heroicon::Tag)
                    ->relationship('kategoriBiaya', 'nama_kategori')
                    ->required()->columnSpan(2),
                TextInput::make('nominal')->numeric()->prefix('Rp.')->required()
                ->live(onBlur: true) // Update setiap kali mengetik
                ->hint(fn ($state) => $state ? \App\Helpers\Terbilang::make($state) : null)
                ->hintColor('primary')->columnSpan(2),
                Select::make('jenjang')
                    ->options(\App\Models\Kelas::JENJANG)
                    ->required()->columnSpan(1),
                Radio::make('jenis_biaya')
                    ->options(\App\Models\Biaya::JENIS_BIAYA)
                    ->required()->columnSpan(1),
            ])->columns(4);
    }
}
