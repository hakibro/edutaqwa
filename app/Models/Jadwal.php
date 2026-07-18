<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Jadwal extends Model
{
    use HasFactory;

    protected $fillable = [
        'lembaga_id',
        'kelas_id',
        'mapel_id',
        'guru_id',
        'tahun_ajaran_id',
        'hari',
        'jam_ke',
    ];

    public function lembaga(): BelongsTo
    {
        return $this->belongsTo(Lembaga::class);
    }

    public function kelas(): BelongsTo
    {
        return $this->belongsTo(Kelas::class);
    }

    public function mapel(): BelongsTo
    {
        return $this->belongsTo(Mapel::class);
    }

    public function guru(): BelongsTo
    {
        return $this->belongsTo(Guru::class);
    }

    public function tahunAjaran(): BelongsTo
    {
        return $this->belongsTo(TahunAjaran::class);
    }

    public function jurnalMengajars(): HasMany
    {
        return $this->hasMany(JurnalMengajar::class);
    }

    /**
     * Cek bentrok jadwal: guru sama, hari & jam_ke sama.
     */
    public static function cekBentrok(int $guruId, string $hari, int $jamKe, ?int $exceptId = null): ?string
    {
        $query = static::where('guru_id', $guruId)
            ->where('hari', $hari)
            ->where('jam_ke', $jamKe);

        if ($exceptId) {
            $query->where('id', '!=', $exceptId);
        }

        $bentrok = $query->first();

        if ($bentrok) {
            $jamLabel = 'Jam ' . $jamKe;
            return "Guru {$bentrok->guru->nama} sudah mengajar {$bentrok->mapel->nama} di kelas {$bentrok->kelas->nama} pada {$hari} {$jamLabel}";
        }

        return null;
    }
}
