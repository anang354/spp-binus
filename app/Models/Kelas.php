<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Kelas extends Model
{
    //
    protected $guarded = ['id'];

    use SoftDeletes;

    const JENJANG = [
        'TK'    => 'TK',
        'SD'    => 'SD',
        'SMP'   => 'SMP',
        'SMA'   => 'SMA',
    ];
    const LEVELS_TK = [1 => '1'];
    const LEVELS_SD = [1 => 'Kelas 1', 2 => 'Kelas 2', 3 => 'Kelas 3', 4 => 'Kelas 4', 5 => 'Kelas 5', 6 => 'Kelas 6'];
    const LEVELS_SMP = [7 => 'Kelas 7', 8 => 'Kelas 8', 9 => 'Kelas 9'];
    const LEVELS_SMA = [10 => 'Kelas 10', 11 => 'Kelas 11', 12 => 'Kelas 12'];

    public function siswa(): HasMany
    {
        return $this->hasMany(Siswa::class);
    }
}
