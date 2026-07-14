<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cp extends Model
{
    use HasFactory;

    protected $fillable = [
        'mapel_id',
        'guru_id',
        'fase',
        'kode',
        'deskripsi',
    ];

    public function mapel(): BelongsTo
    {
        return $this->belongsTo(Mapel::class);
    }

    public function guru(): BelongsTo
    {
        return $this->belongsTo(Guru::class);
    }

    public function tps(): HasMany
    {
        return $this->hasMany(Tp::class);
    }
}
