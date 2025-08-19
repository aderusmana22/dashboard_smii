<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LaporanKecelakaan extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    /**
     * Casts untuk menangani kolom JSON secara otomatis.
     */
    protected $casts = [
        'waktu_kecelakaan' => 'datetime',
        'apd_data' => 'array',
    ];

    /**
     * Relasi ke BiayaPerawatan.
     */
    public function biayaPerawatan(): HasMany
    {
        return $this->hasMany(BiayaPerawatan::class);
    }

    /**
     * Relasi ke SaranPerbaikan.
     */
    public function saranPerbaikan(): HasMany
    {
        return $this->hasMany(SaranPerbaikan::class);
    }
}