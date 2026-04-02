<?php

namespace App\Filament\Resources\Diskons\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\Diskons\DiskonResource;

class ListDiskons extends ListRecords
{
    protected static string $resource = DiskonResource::class;

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
                ->modifyQueryUsing(fn (Builder $query) => $query->where('jenjang', 'TK')),
            'SD' => Tab::make('SD')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('jenjang', 'SD')),
            'SMP' => Tab::make('SMP')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('jenjang', 'SMP')),
            'SMA' => Tab::make('SMA')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('jenjang', 'SMA')),
        ];
    }
}
