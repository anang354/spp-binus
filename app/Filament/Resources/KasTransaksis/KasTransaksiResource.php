<?php

namespace App\Filament\Resources\KasTransaksis;

use App\Filament\Resources\KasTransaksis\Pages\CreateKasTransaksi;
use App\Filament\Resources\KasTransaksis\Pages\EditKasTransaksi;
use App\Filament\Resources\KasTransaksis\Pages\ListKasTransaksis;
use App\Filament\Resources\KasTransaksis\Schemas\KasTransaksiForm;
use App\Filament\Resources\KasTransaksis\Tables\KasTransaksisTable;
use App\Models\KasTransaksi;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class KasTransaksiResource extends Resource
{
    protected static ?string $model = KasTransaksi::class;
    protected static ?string $navigationLabel = 'Catatan Kas';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedFire;
    protected static string | UnitEnum | null $navigationGroup = 'Buku Kas';
    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return KasTransaksiForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return KasTransaksisTable::configure($table);
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
            'index' => ListKasTransaksis::route('/'),
            'create' => CreateKasTransaksi::route('/create'),
            'edit' => EditKasTransaksi::route('/{record}/edit'),
        ];
    }
}
