<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Nilai extends Model
{
    use HasFactory;

    protected $fillable = [
        'siswa_id',
        'mapel_id',
        'guru_id',
        'kelas_id',
        'tahun_ajaran_id',
        'jenis_nilai_id',
        'tp_id',
        'nilai',
        'keterangan',
        'is_finalized',
    ];

    protected $casts = [
        'nilai' => 'decimal:2',
        'is_finalized' => 'boolean',
    ];

    public function siswa(): BelongsTo
    {
        return $this->belongsTo(Siswa::class);
    }

    public function mapel(): BelongsTo
    {
        return $this->belongsTo(Mapel::class);
    }

    public function guru(): BelongsTo
    {
        return $this->belongsTo(Guru::class);
    }

    public function kelas(): BelongsTo
    {
        return $this->belongsTo(Kelas::class);
    }

    public function tahunAjaran(): BelongsTo
    {
        return $this->belongsTo(TahunAjaran::class);
    }

    public function jenisNilai(): BelongsTo
    {
        return $this->belongsTo(JenisNilai::class);
    }

    public function tp(): BelongsTo
    {
        return $this->belongsTo(Tp::class);
    }
}
