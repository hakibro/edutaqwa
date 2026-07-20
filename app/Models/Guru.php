<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Guru extends Model
{
    use HasFactory;

    protected $fillable = [
        'lembaga_id',
        'kode_guru_lembaga',
        'kode_guru_satminkal',
        'niy',
        'nama',
        'nip',
        'nuptk',
        'jenis_ptk_id',
        'status_satminkal',
        'tempat_lahir',
        'tanggal_lahir',
        'tmt',
        'alamat',
        'telp',
        'email',
        'foto',
        'dokumen',
        'is_approved',
        'approved_at',
        'approved_by',
        'is_active',
    ];

    protected $casts = [
        'is_approved' => 'boolean',
        'is_active' => 'boolean',
        'status_satminkal' => 'boolean',
        'tanggal_lahir' => 'date',
        'tmt' => 'date',
        'approved_at' => 'datetime',
    ];

    public function lembaga(): BelongsTo
    {
        return $this->belongsTo(Lembaga::class);
    }

    public function user(): HasOne
    {
        return $this->hasOne(User::class);
    }

    public function jenisPtk(): BelongsTo
    {
        return $this->belongsTo(JenisPtk::class);
    }

    public function tugasTambahans(): HasMany
    {
        return $this->hasMany(TugasTambahan::class);
    }

    public function pengajaranMapels(): HasMany
    {
        return $this->hasMany(PengajaranMapel::class);
    }

    public function absensiPtks(): HasMany
    {
        return $this->hasMany(AbsensiPtk::class);
    }

    public function agendaMengajars(): HasMany
    {
        return $this->hasMany(AgendaMengajar::class);
    }

    public function jadwals(): HasMany
    {
        return $this->hasMany(Jadwal::class);
    }

    /**
     * Cek apakah guru ini struktural (punya jenis_ptk_id).
     * Guru struktural wajib absen PTK harian.
     */
    public function isStruktural(): bool
    {
        return !is_null($this->jenis_ptk_id);
    }

    /**
     * Generate NIY: YYYYUUNN — tahun TMT + kode lembaga Sisda + nomor urut.
     * Dipanggil saat Admin Yayasan approve guru.
     */
    public static function generateNiy(Lembaga $lembaga, string $tmt): string
    {
        $tahun = date('Y', strtotime($tmt));
        $kodeLembaga = $lembaga->kode_sisda ?? strtoupper($lembaga->kode);

        $last = static::where('lembaga_id', $lembaga->id)
            ->whereNotNull('niy')
            ->where('niy', 'like', $tahun . $kodeLembaga . '%')
            ->orderByDesc('niy')
            ->first();

        $urut = 1;
        if ($last && preg_match('/' . $tahun . $kodeLembaga . '(\d+)$/', $last->niy, $m)) {
            $urut = (int) $m[1] + 1;
        }

        return $tahun . $kodeLembaga . str_pad($urut, 2, '0', STR_PAD_LEFT);
    }

    /**
     * Generate kode guru lembaga: [KodeLembaga].[NomorUrut]
     */
    public static function generateKodeLembaga(Lembaga $lembaga): string
    {
        $last = static::where('lembaga_id', $lembaga->id)
            ->whereNotNull('kode_guru_lembaga')
            ->orderByDesc('id')
            ->first();

        $urut = 1;
        if ($last && preg_match('/\.(\d+)$/', $last->kode_guru_lembaga, $m)) {
            $urut = (int) $m[1] + 1;
        }

        return strtoupper($lembaga->kode) . '.' . str_pad($urut, 3, '0', STR_PAD_LEFT);
    }

    /**
     * Generate kode guru satminkal: [KodeYayasan].[KodeLembaga].[NomorUrut]
     */
    public static function generateKodeSatminkal(Lembaga $lembaga): string
    {
        $last = static::where('lembaga_id', $lembaga->id)
            ->whereNotNull('kode_guru_satminkal')
            ->orderByDesc('id')
            ->first();

        $urut = 1;
        if ($last && preg_match('/\.(\d+)$/', $last->kode_guru_satminkal, $m)) {
            $urut = (int) $m[1] + 1;
        }

        return strtoupper($lembaga->yayasan->kode) . '.' . strtoupper($lembaga->kode) . '.' . str_pad($urut, 3, '0', STR_PAD_LEFT);
    }
}
