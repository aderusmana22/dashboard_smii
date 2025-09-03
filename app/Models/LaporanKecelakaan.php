<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class LaporanKecelakaan extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'waktu_kecelakaan' => 'datetime',
        'sebab_utama' => 'array', 
        'apd_data' => 'array',
        'date' => 'date',
        'tanggal_lahir' => 'date',
        'tanggal_masuk' => 'date',
        'is_active' => 'boolean', // PERUBAHAN: Casting untuk kolom baru
    ];

    // --- RELASI APPROVAL ---
    public function approvalStatus(): HasOne
    {
        return $this->hasOne(LaporanApprovalStatus::class);
    }

    public function approvalHistories(): HasMany
    {
        return $this->hasMany(LaporanApprovalHistory::class);
    }
    // --- AKHIR RELASI APPROVAL ---

    public function biayaPerawatan(): HasMany
    {
        return $this->hasMany(BiayaPerawatan::class);
    }

    public function saranPerbaikan(): HasMany
    {
        return $this->hasMany(SaranPerbaikan::class);
    }

    public function pembuatLaporan(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pembuat_laporan_id');
    }

    public function managerHse(): BelongsTo
    {
        return $this->belongsTo(User::class, 'manager_hse_id');
    }

    public function managerTerkait(): BelongsTo
    {
        return $this->belongsTo(User::class, 'manager_terkait_id');
    }

    public function deptHead(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dept_head_id');
    }

    public function generalManager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'gm_id');
    }

     public function revisedFrom(): BelongsTo
    {
        return $this->belongsTo(LaporanKecelakaan::class, 'revised_from_id');
    }
}