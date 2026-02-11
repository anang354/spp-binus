<?php

namespace App\Filament\Pages;

use BackedEnum;
use Filament\Pages\Page;
use Filament\Tables\Table;
use Filament\Actions\EditAction;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\Radio;
use Filament\Support\Icons\Heroicon;
use Filament\Actions\BulkActionGroup;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Contracts\HasTable;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Concerns\InteractsWithTable;
use UnitEnum;

class KategoriBiaya extends Page implements HasTable
{
    use InteractsWithTable;
    protected string $view = 'filament.pages.kategori-biaya';
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTag;
    protected static string | UnitEnum | null $navigationGroup = 'Biaya';
    protected static ?int $navigationSort = 4;

    public static function getKategoriBiayaForm(): array
    {
        return [
            TextInput::make('nama_kategori')
                ->label('Nama Kategori Biaya')
                ->required()
                ->maxLength(255)
        ];
    }
    public function table(Table $table): Table
    {
        return $table
            ->query(\App\Models\KategoriBiaya::query())
            ->columns([
                TextColumn::make('No')
                    ->label('No')
                    ->rowIndex(),
                TextColumn::make('nama_kategori')
                    ->label('Nama Kategori'),
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->actions([
                EditAction::make()
                    ->label('Edit')
                    ->slideOver()
                    ->color('primary')
                    ->form(static::getKategoriBiayaForm()),
                \Filament\Actions\DeleteAction::make(),
                \Filament\Actions\RestoreAction::make(),     // Tombol untuk memulihkan data
                \Filament\Actions\ForceDeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    \Filament\Actions\DeleteBulkAction::make(),
                    \Filament\Actions\DeleteBulkAction::make(),
                    \Filament\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
            ->label('Buat Kategori Biaya')
            ->model(\App\Models\KategoriBiaya::class)
            ->slideOver()
            ->icon(Heroicon::OutlinedPlus)
            ->form(static::getKategoriBiayaForm()),
        ];
    }
}
