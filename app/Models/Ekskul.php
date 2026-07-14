<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ekskul extends Model
{
    use HasFactory;

    protected $fillable = [
        'lembaga_id',
        'nama',
        'pembina_id',
    ];

    public function lembaga(): BelongsTo
    {
        return $this->belongsTo(Lembaga::class);
    }

    public function pembina(): BelongsTo
    {
        return $this->belongsTo(Guru::class, 'pembina_id');
    }

    public function anggotaEkskuls(): HasMany
    {
        return $this->hasMany(AnggotaEkskul::class);
    }
}
