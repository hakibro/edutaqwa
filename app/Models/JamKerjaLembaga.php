<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JamKerjaLembaga extends Model
{
    use HasFactory;

    protected $fillable = [
        'lembaga_id',
        'hari',
        'jam_masuk',
        'jam_pulang',
        'toleransi_keterlambatan',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function lembaga(): BelongsTo
    {
        return $this->belongsTo(Lembaga::class);
    }
}
