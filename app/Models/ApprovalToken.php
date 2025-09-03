<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApprovalToken extends Model
{
    use HasFactory;

    protected $fillable = [
        'laporan_kecelakaan_id',
        'user_id',
        'token',
        'action',
        'expires_at',
        'used_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'used_at' => 'datetime',
    ];

    public function laporanKecelakaan(): BelongsTo
    {
        return $this->belongsTo(LaporanKecelakaan::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}