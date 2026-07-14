<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KelompokMapel extends Model
{
    use HasFactory;

    protected $fillable = [
        'lembaga_id',
        'nama',
    ];

    public function lembaga(): BelongsTo
    {
        return $this->belongsTo(Lembaga::class);
    }

    public function mapels(): HasMany
    {
        return $this->hasMany(Mapel::class);
    }
}
