<?php

namespace App\Filament\Resources\KasTransaksis\Pages;

use App\Filament\Resources\KasTransaksis\KasTransaksiResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditKasTransaksi extends EditRecord
{
    protected static string $resource = KasTransaksiResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
