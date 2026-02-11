<?php

namespace App\Filament\Resources\Diskons;

use App\Filament\Resources\Diskons\Pages\CreateDiskon;
use App\Filament\Resources\Diskons\Pages\EditDiskon;
use App\Filament\Resources\Diskons\Pages\ListDiskons;
use App\Filament\Resources\Diskons\Schemas\DiskonForm;
use App\Filament\Resources\Diskons\Tables\DiskonsTable;
use App\Models\Diskon;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

class DiskonResource extends Resource
{
    protected static ?string $model = Diskon::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPercentBadge;
    protected static string | UnitEnum | null $navigationGroup = 'Diskon';
    protected static ?int $navigationSort = 7;

    public static function form(Schema $schema): Schema
    {
        return DiskonForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return DiskonsTable::configure($table);
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
            'index' => ListDiskons::route('/'),
            'create' => CreateDiskon::route('/create'),
            'edit' => EditDiskon::route('/{record}/edit'),
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
