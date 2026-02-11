<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Siswa extends Model
{
    //
    protected $guarded = ['id'];

    use SoftDeletes;

    public function kelas(): BelongsTo
    {
        return $this->belongsTo(Kelas::class)->withTrashed();
    }
    public function diskon(): BelongsToMany
    {
        return $this->belongsToMany(Diskon::class, 'diskon_siswa')->withTimestamps()->withTrashed();
    }
    public function tagihans(): HasMany
    {
        return $this->hasMany(Tagihan::class);
    }
    public function pembayarans(): HasMany
    {
        return $this->hasMany(Pembayaran::class);
    }
}
