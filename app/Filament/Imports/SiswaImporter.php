<?php

namespace App\Filament\Imports;

use App\Models\Siswa;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Support\Number;

class SiswaImporter extends Importer
{
    protected static ?string $model = Siswa::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('kelas')
                ->label('kelas_id')
                ->helperText('Lihat id di halaman Kelas')
                ->requiredMapping()
                ->relationship()
                ->rules(['required']),
            ImportColumn::make('nama_siswa')
                ->requiredMapping()
                ->rules(['required', 'max:255']),
            ImportColumn::make('jenis_kelamin')
                ->helperText('laki-laki/perempuan')
                ->requiredMapping()
                ->rules(['required']),
            ImportColumn::make('nama_wali')
                ->rules(['max:255']),
            ImportColumn::make('alamat')
                ->rules(['max:255']),
            ImportColumn::make('nomor_hp')
                ->rules(['max:255']),
            ImportColumn::make('is_active')
                ->helperText('siswa aktif isi 1')
                ->requiredMapping()
                ->boolean()
                ->rules(['required', 'boolean']),
            ImportColumn::make('foto')
                ->rules(['max:255']),
        ];
    }

    public function resolveRecord(): Siswa
    {
        return new Siswa();
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your siswa import has completed and ' . Number::format($import->successful_rows) . ' ' . str('row')->plural($import->successful_rows) . ' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . Number::format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to import.';
        }

        return $body;
    }
}
