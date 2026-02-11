<?php

namespace App\Filament\Resources\KasTransaksis\Pages;

use App\Filament\Resources\KasTransaksis\KasTransaksiResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListKasTransaksis extends ListRecords
{
    protected static string $resource = KasTransaksiResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
