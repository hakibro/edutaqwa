<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Yayasan extends Model
{
    use HasFactory;

    protected $fillable = ['nama', 'kode', 'alamat', 'telp', 'email', 'logo', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function lembagas(): HasMany
    {
        return $this->hasMany(Lembaga::class);
    }

    public function tahunAjarans(): HasMany
    {
        return $this->hasMany(TahunAjaran::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
}
