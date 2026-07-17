<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Siswa extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'lembaga_id',
        'idperson',
        'nis',
        'nisn',
        'nama',
        'tempat_lahir',
        'tanggal_lahir',
        'jenis_kelamin',
        'alamat',
        'telp',
        'email',
        'foto',
        'agama',
        'nama_ayah',
        'nama_ibu',
        'pekerjaan_ayah',
        'pekerjaan_ibu',
        'telp_orang_tua',
        'status',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'tanggal_lahir' => 'date',
    ];

    public function lembaga(): BelongsTo
    {
        return $this->belongsTo(Lembaga::class);
    }

    public function riwayatKelasSiswas(): HasMany
    {
        return $this->hasMany(RiwayatKelasSiswa::class);
    }

    public function kelasAktif()
    {
        return $this->belongsToMany(Kelas::class, 'riwayat_kelas_siswas')
            ->withPivot('tahun_ajaran_id', 'tanggal_masuk', 'tanggal_keluar')
            ->wherePivotNull('tanggal_keluar');
    }
}
