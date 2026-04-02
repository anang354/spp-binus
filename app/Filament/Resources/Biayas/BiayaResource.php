<?php

namespace App\Filament\Resources\Biayas;

use App\Filament\Resources\Biayas\Pages\CreateBiaya;
use App\Filament\Resources\Biayas\Pages\EditBiaya;
use App\Filament\Resources\Biayas\Pages\ListBiayas;
use App\Filament\Resources\Biayas\Schemas\BiayaForm;
use App\Filament\Resources\Biayas\Tables\BiayasTable;
use App\Models\Biaya;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

class BiayaResource extends Resource
{
    protected static ?string $model = Biaya::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBanknotes;
    protected static string | UnitEnum | null $navigationGroup = 'Biaya';
    protected static ?int $navigationSort = 5;

    public static function form(Schema $schema): Schema
    {
        return BiayaForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return BiayasTable::configure($table);
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
            'index' => ListBiayas::route('/'),
            'create' => CreateBiaya::route('/create'),
            'edit' => EditBiaya::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
