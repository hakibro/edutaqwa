<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DetailPresensi extends Model
{
    use HasFactory;

    protected $fillable = [
        'presensi_id',
        'siswa_id',
        'status',
        'keterangan',
    ];

    public $timestamps = false;

    public function presensi(): BelongsTo
    {
        return $this->belongsTo(Presensi::class);
    }

    public function siswa(): BelongsTo
    {
        return $this->belongsTo(Siswa::class);
    }
}
