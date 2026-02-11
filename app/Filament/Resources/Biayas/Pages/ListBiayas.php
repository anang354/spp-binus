<?php

namespace App\Filament\Resources\Biayas\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use App\Filament\Resources\Biayas\BiayaResource;
use Illuminate\Contracts\Database\Eloquent\Builder;

class ListBiayas extends ListRecords
{
    protected static string $resource = BiayaResource::class;

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
