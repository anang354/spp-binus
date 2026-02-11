<?php

namespace App\Filament\Resources\Siswas\RelationManagers;

use Carbon\Carbon;
use App\Models\Tagihan;
use Filament\Tables\Table;
use Filament\Schemas\Schema;
use Filament\Actions\EditAction;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\AssociateAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\DissociateAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Actions\DissociateBulkAction;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Resources\RelationManagers\RelationManager;

class TagihansRelationManager extends RelationManager
{
    protected static string $relationship = 'tagihans';

    public function form(Schema $schema): Schema
    {
        return \App\Filament\Resources\Tagihans\Schemas\TagihanForm::configure($schema);
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                    Tagihan::query()
                        ->with('pembayaran')
                        ->selectRaw('
                    tagihans.*,
                    (SELECT COALESCE(SUM(jumlah_dibayar), 0) FROM pembayarans WHERE pembayarans.tagihan_id = tagihans.id) as total_dibayar,
                    (tagihan_netto - (SELECT COALESCE(SUM(jumlah_dibayar), 0) FROM pembayarans WHERE pembayarans.tagihan_id = tagihans.id)) as sisa_tagihan
                ')
                        ->orderByDesc('created_at')
                )
            ->recordTitleAttribute('nama_tagihan')
            ->columns([
                TextColumn::make('nama_tagihan')
                    ->searchable(),
                TextColumn::make('periode')
                    ->getStateUsing(function (Tagihan $record): string {
                    $date = Carbon::createFromDate($record->periode_tahun, $record->periode_bulan, 1);
                    return $date->translatedFormat('F Y');
                }),
                TextColumn::make('kategoriBiaya.nama_kategori')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),
                TextColumn::make('jatuh_tempo')
                    ->date()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),
                TextColumn::make('jumlah_tagihan')
                    ->numeric()
                    ->summarize(Sum::make()),
                TextColumn::make('jumlah_diskon')
                    ->numeric()
                    ->color('danger')
                    ->summarize(Sum::make()),
                TextColumn::make('tagihan_netto')
                    ->numeric()
                    ->summarize(Sum::make()),
                TextColumn::make('total_dibayar')
                    ->label('Total Dibayar')
                    ->numeric()
                    ->summarize(Sum::make()),
                TextColumn::make('sisa_tagihan')
                    ->label('Sisa Tagihan')
                    ->numeric()
                    ->summarize(Sum::make()),
                TextColumn::make('nama_diskon')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'baru' => 'info',
                        'lunas' => 'success',
                        'angsur' => 'warning',
                    })
                    ->icons([
                        'heroicon-m-check-badge' => 'lunas',
                        'heroicon-m-arrow-path' => 'angsur',
                        'heroicon-m-clock' => 'baru',
                    ]),
                TextColumn::make('jenis_tagihan')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->searchable(),
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
            ->headerActions([
                // CreateAction::make(),
                \App\Filament\Actions\Tagihans\CreateIndividualAction::make(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DissociateBulkAction::make(),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
