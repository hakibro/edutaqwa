<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Atp extends Model
{
    use HasFactory;

    protected $fillable = [
        'tp_id',
        'minggu_ke',
        'materi',
    ];

    public function tp(): BelongsTo
    {
        return $this->belongsTo(Tp::class);
    }

    public function jurnalMengajars(): HasMany
    {
        return $this->hasMany(JurnalMengajar::class);
    }
}
