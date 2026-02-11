<?php

namespace App\Filament\Resources\Siswas\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class PembayaransRelationManager extends RelationManager
{
    protected static string $relationship = 'pembayarans';

    public function form(Schema $schema): Schema
    {
        return \App\Filament\Resources\Pembayarans\Schemas\PembayaranForm::configure($schema);
    }

    public function table(Table $table): Table
    {
        return \App\Filament\Resources\Pembayarans\Tables\PembayaransTable::configure($table);
    }
}
