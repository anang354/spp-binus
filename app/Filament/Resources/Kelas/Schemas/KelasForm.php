<?php

namespace App\Filament\Resources\Kelas\Schemas;

use App\Models\Kelas;
use Filament\Schemas\Schema;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;

class KelasForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('nama_kelas')
                    ->required(),
                Select::make('jenjang')
                    ->options(Kelas::JENJANG)->live()
                    ->afterStateUpdated(fn (Set $set) => $set('level', null)),
                Select::make('level')
                    ->options(function (Get $get): array {
                        $jenjang = $get('jenjang');
                        if ($jenjang === 'TK') {
                        return Kelas::LEVELS_TK;
                        } elseif ($jenjang === 'SD') {
                            return Kelas::LEVELS_SD;
                        } elseif ($jenjang === 'SMP') {
                            return Kelas::LEVELS_SMP;
                        } elseif ($jenjang === 'SMA') {
                            return Kelas::LEVELS_SMA;
                        }
                        return [];
                    })
            ]);
    }
}
