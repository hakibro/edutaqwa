<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TugasTambahan extends Model
{
    use HasFactory;

    protected $fillable = ['guru_id', 'jenis', 'keterangan', 'permissions', 'tahun_ajaran_id', 'kelas_id', 'is_active'];

    protected $casts = ['is_active' => 'boolean', 'permissions' => 'array'];

    public function guru(): BelongsTo
    {
        return $this->belongsTo(Guru::class);
    }

    public function tahunAjaran(): BelongsTo
    {
        return $this->belongsTo(TahunAjaran::class);
    }

    public function kelas(): BelongsTo
    {
        return $this->belongsTo(Kelas::class);
    }

    /**
     * Scope: tugas tambahan yang punya permission tertentu.
     */
    public function scopeWithPermission($query, string $permission)
    {
        return $query->where('is_active', true)->whereJsonContains('permissions', $permission);
    }
}
