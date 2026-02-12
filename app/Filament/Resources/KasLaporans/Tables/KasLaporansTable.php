<?php

namespace App\Filament\Resources\KasLaporans\Tables;

use App\Models\KasLaporan;
use Filament\Tables\Table;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;

class KasLaporansTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nama_laporan')
                    ->searchable(),
                TextColumn::make('tanggal_mulai')
                    ->date()
                    ->sortable(),
                TextColumn::make('tanggal_tutup')
                    ->date()
                    ->sortable(),
                TextColumn::make('saldo_akhir_tunai')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('saldo_akhir_bank')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('total_saldo')
                    ->numeric()
                    ->sortable(),
                IconColumn::make('is_closed')
                    ->boolean(),
                TextColumn::make('user.name')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
                Action::make('download')
                    ->label('Unduh PDF')
                    ->icon('heroicon-o-document-arrow-down')
                    ->url(fn (KasLaporan $record) => $record->nama_file ? asset('storage/' . $record->nama_file) : null)
                    ->openUrlInNewTab()
                    ->disabled(fn (KasLaporan $record) => !$record->nama_file)
                    ->color('success'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ])->visible(fn () => auth()->user()->role !== 'viewer'),
            ]);
    }
}
