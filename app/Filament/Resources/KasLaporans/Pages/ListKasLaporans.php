<?php

namespace App\Filament\Resources\KasLaporans\Pages;

use Filament\Actions\CreateAction;
use App\Livewire\HistoryKasLineChart;
use Filament\Resources\Pages\ListRecords;
use App\Filament\Widgets\KasKeluarKategoriChart;
use App\Filament\Resources\KasLaporans\KasLaporanResource;

class ListKasLaporans extends ListRecords
{
    protected static string $resource = KasLaporanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
    protected function getFooterWidgets(): array
    {
        return [
            KasKeluarKategoriChart::class,
            HistoryKasLineChart::class,
        ];
    }
}
