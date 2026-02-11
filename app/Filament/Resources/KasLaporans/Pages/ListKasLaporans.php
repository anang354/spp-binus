<?php

namespace App\Filament\Resources\KasLaporans\Pages;

use App\Filament\Resources\KasLaporans\KasLaporanResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListKasLaporans extends ListRecords
{
    protected static string $resource = KasLaporanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
