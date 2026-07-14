<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AgendaMengajar extends Model
{
    use HasFactory;

    protected $fillable = [
        'jadwal_id',
        'guru_id',
        'kelas_id',
        'pertemuan_ke',
        'tanggal',
        'jam_mulai',
        'jam_selesai',
        'foto_path',
        'latitude',
        'longitude',
        'metadata',
        'is_verified',
        'verified_at',
        'verified_by',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'is_verified' => 'boolean',
        'verified_at' => 'datetime',
    ];

    public function jadwal(): BelongsTo
    {
        return $this->belongsTo(Jadwal::class);
    }

    public function guru(): BelongsTo
    {
        return $this->belongsTo(Guru::class);
    }

    public function kelas(): BelongsTo
    {
        return $this->belongsTo(Kelas::class);
    }

    public function verifikator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }
}
