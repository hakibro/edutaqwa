<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KalenderAkademik extends Model
{
    use HasFactory;

    protected $fillable = ['yayasan_id', 'tanggal', 'label', 'jenis', 'keterangan'];

    protected $casts = ['tanggal' => 'date'];

    public function yayasan(): BelongsTo
    {
        return $this->belongsTo(Yayasan::class);
    }

    public function scopeEfektif($query)
    {
        return $query->where('jenis', 'efektif');
    }

    public function scopeLibur($query)
    {
        return $query->where('jenis', 'libur');
    }
}
