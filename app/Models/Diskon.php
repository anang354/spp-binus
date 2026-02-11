<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Diskon extends Model
{
    //
    protected $guarded = ['id'];

    use SoftDeletes;

    public function biaya(): BelongsTo
    {
        return $this->belongsto(Biaya::class);
    }
    public function siswa(): BelongsToMany
    {
        return $this->belongsToMany(Siswa::class, 'diskon_siswa')->withTimestamps()->withTrashed();
    }
}
