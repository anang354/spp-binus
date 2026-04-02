<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Casts\Attribute;
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
    protected function nomorHp(): Attribute
    {
        return Attribute::make(
            set: function ($value) {
                // Hilangkan karakter non-digit (spasi, strip, dll)
                $cleanValue = preg_replace('/[^0-9]/', '', $value);
                // Jika TIDAK diawali angka 0, tambahkan 62
                if (!str_starts_with($cleanValue, '0') && !str_starts_with($cleanValue, '62')) {
                    return '62' . $cleanValue;
                }
                // 3. Jika diawali angka 0, biarkan sesuai input user (tidak ditambahkan 62)
                return $cleanValue;
            },
        );
    }
}
