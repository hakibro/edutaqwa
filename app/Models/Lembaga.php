<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Lembaga extends Model
{
    use HasFactory;

    protected $fillable = [
        'yayasan_id',
        'nama',
        'kode',
        'kode_sisda',
        'sisda_mode',
        'npsn',
        'alamat',
        'telp',
        'email',
        'tingkat',
        'unit_formal',
        'is_active',
        'lokasi_absen',
        'latitude_absen',
        'longitude_absen',
        'radius_absen_meter',
        'wajib_selfie',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sisda_mode' => 'boolean',
        'wajib_selfie' => 'boolean',
        'latitude_absen' => 'decimal:7',
        'longitude_absen' => 'decimal:7',
    ];

    public function yayasan(): BelongsTo
    {
        return $this->belongsTo(Yayasan::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function gurus(): HasMany
    {
        return $this->hasMany(Guru::class);
    }

    public function siswas(): HasMany
    {
        return $this->hasMany(Siswa::class);
    }

    public function kelas(): HasMany
    {
        return $this->hasMany(Kelas::class);
    }

    public function jurusans(): HasMany
    {
        return $this->hasMany(Jurusan::class);
    }

    public function mapels(): HasMany
    {
        return $this->hasMany(Mapel::class);
    }

    public function jamKerjaLembagas(): HasMany
    {
        return $this->hasMany(JamKerjaLembaga::class);
    }
}
