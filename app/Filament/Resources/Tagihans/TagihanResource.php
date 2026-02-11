<?php

namespace App\Filament\Resources\Tagihans;

use App\Filament\Resources\Tagihans\Pages\CreateTagihan;
use App\Filament\Resources\Tagihans\Pages\EditTagihan;
use App\Filament\Resources\Tagihans\Pages\ListTagihans;
use App\Filament\Resources\Tagihans\Schemas\TagihanForm;
use App\Filament\Resources\Tagihans\Tables\TagihansTable;
use App\Models\Tagihan;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class TagihanResource extends Resource
{
    protected static ?string $model = Tagihan::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedNewspaper;
    protected static string | UnitEnum | null $navigationGroup = 'Keuangan';
    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return TagihanForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TagihansTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }
    protected function getHeaderActions(): array
    {
        return [
            // Actions\\CreateAction::make(), // Comment out or remove this line
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTagihans::route('/'),
            // 'create' => CreateTagihan::route('/create'),
            'edit' => EditTagihan::route('/{record}/edit'),
        ];
    }
}
