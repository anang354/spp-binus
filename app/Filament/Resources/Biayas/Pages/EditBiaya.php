<?php

namespace App\Filament\Resources\Biayas\Pages;

use App\Filament\Resources\Biayas\BiayaResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditBiaya extends EditRecord
{
    protected static string $resource = BiayaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
