<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AbsensiPtk extends Model
{
    use HasFactory;

    protected $fillable = [
        'guru_id',
        'lembaga_id',
        'tanggal',
        'check_in',
        'check_out',
        'jam_masuk_set',
        'jam_pulang_set',
        'status',
        'keterlambatan_menit',
        'lokasi_check_in',
        'lokasi_check_out',
        'foto_check_in',
        'foto_check_out',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'check_in' => 'datetime',
        'check_out' => 'datetime',
    ];

    public function guru(): BelongsTo
    {
        return $this->belongsTo(Guru::class);
    }

    public function lembaga(): BelongsTo
    {
        return $this->belongsTo(Lembaga::class);
    }
}
