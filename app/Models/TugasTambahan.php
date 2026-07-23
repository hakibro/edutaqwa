<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TugasTambahan extends Model
{
    use HasFactory;

    protected $fillable = ['guru_id', 'jenis', 'keterangan', 'tahun_ajaran_id', 'kelas_id', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

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
     * Scope: tugas tambahan yang punya permission tertentu
     * (sekarang berdasarkan field `jenis`, bukan `permissions` JSON).
     */
    public function scopeWithPermission($query, string $permission)
    {
        $jenisName = \App\Models\Guru::PERMISSION_JENIS_MAP[$permission] ?? null;
        if (!$jenisName) {
            return $query->whereRaw('1 = 0'); // no match
        }
        return $query->where('is_active', true)->where('jenis', $jenisName);
    }
}
