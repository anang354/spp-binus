<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum MetodePembayaran: string implements HasColor, HasIcon, HasLabel
{
    case Tunai = 'tunai';

    case Transfer = 'transfer';

    public function getLabel(): string
    {
        return match ($this) {
            self::Tunai => 'Tunai',
            self::Transfer => 'Transfer',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Tunai => 'success',
            self::Transfer => 'warning',
        };
    }

    public function getIcon(): string
    {
        return match ($this) {
            self::Tunai => 'heroicon-m-banknotes',
            self::Transfer => 'heroicon-m-arrow-path-rounded-square',
        };
    }
}
