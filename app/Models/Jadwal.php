<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\AkademikSetting;

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

    /**
     * Status sesi jadwal berdasarkan jam sekarang.
     *
     * Return: 'belum_mulai' | 'sedang_berlangsung' | 'selesai'
     *
     * Butuh: AkademikSetting::getKbmItems($lembaga_id, $hari)
     * untuk tau jam_mulai & jam_selesai tiap jam_ke.
     */
    public function statusSesi(): string
    {
        $kbmItems = AkademikSetting::getKbmItems($this->lembaga_id, $this->hari);
        $slot = $kbmItems[$this->jam_ke] ?? null;

        if (!$slot) {
            return 'selesai';
        }

        $now = now();
        $mulai = \Carbon\Carbon::createFromFormat('H:i', $slot['jam_mulai']);
        $selesai = \Carbon\Carbon::createFromFormat('H:i', $slot['jam_selesai']);

        if ($now->lt($mulai)) {
            return 'belum_mulai';
        }

        if ($now->between($mulai, $selesai)) {
            return 'sedang_berlangsung';
        }

        return 'selesai';
    }

    /**
     * Label status sesi yang siap ditampilkan.
     */
    public function labelStatusSesi(): string
    {
        return match ($this->statusSesi()) {
            'belum_mulai' => 'Belum Mulai',
            'sedang_berlangsung' => 'Sedang Berlangsung',
            'selesai' => 'Selesai',
        };
    }
}
