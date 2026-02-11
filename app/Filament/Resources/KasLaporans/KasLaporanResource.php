<?php

namespace App\Filament\Resources\KasLaporans;

use App\Filament\Resources\KasLaporans\Pages\CreateKasLaporan;
use App\Filament\Resources\KasLaporans\Pages\EditKasLaporan;
use App\Filament\Resources\KasLaporans\Pages\ListKasLaporans;
use App\Filament\Resources\KasLaporans\Schemas\KasLaporanForm;
use App\Filament\Resources\KasLaporans\Tables\KasLaporansTable;
use App\Models\KasLaporan;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class KasLaporanResource extends Resource
{
    protected static ?string $model = KasLaporan::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentList;
    protected static string | UnitEnum | null $navigationGroup = 'Buku Kas';
    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return KasLaporanForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return KasLaporansTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListKasLaporans::route('/'),
            'create' => CreateKasLaporan::route('/create'),
            'edit' => EditKasLaporan::route('/{record}/edit'),
        ];
    }
}
