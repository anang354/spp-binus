<?php

namespace App\Filament\Resources\Siswas\Pages;

use Filament\Actions\CreateAction;
use Filament\Actions\ImportAction;
use App\Filament\Imports\SiswaImporter;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\Siswas\SiswaResource;

class ListSiswas extends ListRecords
{
    protected static string $resource = SiswaResource::class;

    public function getTabs(): array
    {
        $jenjangPendidikan = \App\Models\Kelas::select('jenjang')
            ->distinct()
            ->pluck('jenjang')
            ->toArray();
            foreach ($jenjangPendidikan as $jenjang) {
            $tabs[$jenjang] = Tab::make(strtoupper($jenjang)) // Ubah ke uppercase untuk label tab
                ->modifyQueryUsing(fn (Builder $query) =>
                    $query->whereHas('kelas', fn (Builder $kelasQuery) =>
                        $kelasQuery->where('jenjang', $jenjang)
                    )
                )
                ->badge(
                    // Menghitung jumlah siswa untuk jenjang ini
                    $this->getResource()::getModel()::whereHas('kelas', fn (Builder $kelasQuery) =>
                        $kelasQuery->where('jenjang', $jenjang)
                    )->count()
                );
        }
        return $tabs;
    }


    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->icon('heroicon-o-user-plus'),
            ImportAction::make()
                ->icon('heroicon-o-document-arrow-up')
                ->color('success')
                ->importer(SiswaImporter::class),
        ];
    }
}
