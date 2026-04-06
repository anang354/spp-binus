<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum Gender: string implements HasLabel, HasColor
{
  case LAKI_LAKI = 'laki-laki';
  case PEREMPUAN = 'perempuan';

  public function getLabel(): ?string
  {
    return match ($this) {
      self::LAKI_LAKI => 'Laki-laki',
      self::PEREMPUAN => 'Perempuan',
    };
  }

  public function getColor(): string|array|null
  {
    return match ($this) {
      self::LAKI_LAKI => 'info',    // Biru
      self::PEREMPUAN => 'danger',  // Pink/Merah
    };
  }
}
