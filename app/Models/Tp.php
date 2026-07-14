<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tp extends Model
{
    use HasFactory;

    protected $fillable = [
        'cp_id',
        'kode',
        'deskripsi',
    ];

    public function cp(): BelongsTo
    {
        return $this->belongsTo(Cp::class);
    }

    public function atps(): HasMany
    {
        return $this->hasMany(Atp::class);
    }
}
