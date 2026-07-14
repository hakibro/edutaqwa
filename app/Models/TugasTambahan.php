<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TugasTambahan extends Model
{
    use HasFactory;

    protected $fillable = ['guru_id', 'jenis', 'keterangan', 'tahun_ajaran_id', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function guru(): BelongsTo
    {
        return $this->belongsTo(Guru::class);
    }

    public function tahunAjaran(): BelongsTo
    {
        return $this->belongsTo(TahunAjaran::class);
    }
}
