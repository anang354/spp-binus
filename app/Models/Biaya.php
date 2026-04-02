<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Biaya extends Model
{
    //
    protected $guarded = ['id'];

    use SoftDeletes;
    const JENIS_BIAYA = [
        'sekali' => 'Sekali',
        'bulanan' => 'Bulanan',
        'tahunan' => 'Tahunan',
    ];
    public function kategoriBiaya(): BelongsTo
    {
        return $this->belongsTo(KategoriBiaya::class);
    }
}
