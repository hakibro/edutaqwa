<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LogAktivita extends Model
{
    protected $table = 'log_aktivitas';

    protected $fillable = [
        'user_id',
        'role',
        'aksi',
        'deskripsi',
        'model_type',
        'model_id',
        'yayasan_id',
        'lembaga_id',
        'ip_address',
        'user_agent',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function yayasan(): BelongsTo
    {
        return $this->belongsTo(Yayasan::class);
    }

    public function lembaga(): BelongsTo
    {
        return $this->belongsTo(Lembaga::class);
    }

    public static function log(string $aksi, string $deskripsi, $model = null): void
    {
        $user = auth()->user();
        if (!$user)
            return;

        static::create([
            'user_id' => $user->id,
            'role' => $user->role,
            'aksi' => $aksi,
            'deskripsi' => $deskripsi,
            'model_type' => $model ? get_class($model) : null,
            'model_id' => $model?->id,
            'yayasan_id' => $user->yayasan_id,
            'lembaga_id' => $user->lembaga_id,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }
}
