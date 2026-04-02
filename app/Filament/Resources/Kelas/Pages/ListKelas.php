<?php

namespace App\Filament\Resources\Kelas\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\Kelas\KelasResource;

class ListKelas extends ListRecords
{
    protected static string $resource = KelasResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return [
            'TK' => Tab::make('TK')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('jenjang', 'TK')->orderByDesc('level')),
            'SD' => Tab::make('SD')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('jenjang', 'SD')->orderByDesc('level')),
            'SMP' => Tab::make('SMP')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('jenjang', 'SMP')->orderByDesc('level')),
            'SMA' => Tab::make('SMA')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('jenjang', 'SMA')->orderByDesc('level')),
        ];
    }
}
