<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PerizinanSiswa extends Model
{
    use HasFactory;

    protected $fillable = [
        'lembaga_id',
        'siswa_id',
        'kelas_id',
        'validator_id',
        'tanggal',
        'jenis',
        'keterangan',
        'lampiran',
        'is_applied',
        'applied_at',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'is_applied' => 'boolean',
        'applied_at' => 'datetime',
    ];

    public function lembaga(): BelongsTo
    {
        return $this->belongsTo(Lembaga::class);
    }

    public function siswa(): BelongsTo
    {
        return $this->belongsTo(Siswa::class);
    }

    public function kelas(): BelongsTo
    {
        return $this->belongsTo(Kelas::class);
    }

    public function validator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'validator_id');
    }

    /**
     * Apply perizinan ini ke detail_jurnal_siswas untuk tanggal terkait.
     * Dipanggil setelah perizinan disimpan/diupdate.
     */
    public function applyToJurnal(): void
    {
        $detailJurnals = DetailJurnalSiswa::where('siswa_id', $this->siswa_id)
            ->whereHas('jurnalMengajar', fn($q) => $q->where('tanggal', $this->tanggal))
            ->get();

        foreach ($detailJurnals as $detail) {
            $detail->update([
                'status' => $this->jenis,
                'keterangan' => $this->keterangan,
            ]);
        }

        if ($detailJurnals->isNotEmpty()) {
            $this->update([
                'is_applied' => true,
                'applied_at' => now(),
            ]);
        }
    }
}
