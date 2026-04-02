<?php

namespace App\Filament\Resources\Pembayarans\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use App\Livewire\PembayaranJenjangOverview;
use App\Filament\Resources\Pembayarans\PembayaranResource;

class ListPembayarans extends ListRecords
{
    protected static string $resource = PembayaranResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
    // Daftarkan widget di sini
    protected function getHeaderWidgets(): array
    {
        return [
            PembayaranJenjangOverview::class,
        ];
    }
}
