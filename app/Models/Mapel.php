<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Mapel extends Model
{
    use HasFactory;

    protected $fillable = [
        'lembaga_id',
        'kelompok_mapel_id',
        'nama',
        'kode',
        'deskripsi',
    ];

    public function lembaga(): BelongsTo
    {
        return $this->belongsTo(Lembaga::class);
    }

    public function kelompokMapel(): BelongsTo
    {
        return $this->belongsTo(KelompokMapel::class);
    }

    public function pengajaranMapels(): HasMany
    {
        return $this->hasMany(PengajaranMapel::class);
    }

    public function cps(): HasMany
    {
        return $this->hasMany(Cp::class);
    }

    public function jadwals(): HasMany
    {
        return $this->hasMany(Jadwal::class);
    }
}
