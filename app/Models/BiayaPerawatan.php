<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BiayaPerawatan extends Model
{
    use HasFactory;

    protected $fillable = ['laporan_kecelakaan_id', 'harga', 'kategori'];

    public function laporanKecelakaan(): BelongsTo
    {
        return $this->belongsTo(LaporanKecelakaan::class);
    }
}