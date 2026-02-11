<?php

namespace App\Filament\Resources\Tagihans\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use App\Livewire\SisaTagihanJenjangOverview;
use App\Filament\Resources\Tagihans\TagihanResource;

class ListTagihans extends ListRecords
{
    protected static string $resource = TagihanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            //CreateAction::make(),
        ];
    }
    protected function getHeaderWidgets(): array
    {
        return [
            SisaTagihanJenjangOverview::class,
        ];
    }
}
