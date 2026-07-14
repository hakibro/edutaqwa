<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Kelas extends Model
{
    use HasFactory;

    protected $fillable = ['lembaga_id', 'jurusan_id', 'nama', 'tingkat', 'external_id'];

    public function lembaga(): BelongsTo
    {
        return $this->belongsTo(Lembaga::class);
    }

    public function jurusan(): BelongsTo
    {
        return $this->belongsTo(Jurusan::class);
    }

    public function riwayatKelasSiswas(): HasMany
    {
        return $this->hasMany(RiwayatKelasSiswa::class);
    }

    public function siswas()
    {
        return $this->belongsToMany(Siswa::class, 'riwayat_kelas_siswas')
            ->withPivot('tahun_ajaran_id', 'tanggal_masuk', 'tanggal_keluar');
    }

    public function jadwals(): HasMany
    {
        return $this->hasMany(Jadwal::class);
    }
}
