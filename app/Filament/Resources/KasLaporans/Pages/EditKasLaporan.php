<?php

namespace App\Filament\Resources\KasLaporans\Pages;

use App\Filament\Resources\KasLaporans\KasLaporanResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditKasLaporan extends EditRecord
{
    protected static string $resource = KasLaporanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
