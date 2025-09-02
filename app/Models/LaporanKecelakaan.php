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

        protected $fillable = [
        'nomor_form',
        'date',
        'kategori_kecelakaan',
        'kategori_dampak',
        'waktu_kecelakaan',
        'lokasi_kecelakaan',
        'tipe_kecelakaan',
        'bagian_terluka',
        'uraian_kejadian',
        'nama_korban',
        'nik',
        'tanggal_lahir',
        'usia',
        'tanggal_masuk',
        'masa_kerja',
        'jabatan',
        'departemen',
        'pertolongan',
        'p3k_oleh',
        'jam_p3k',
        'akibat_kecelakaan',
        'waktu_hilang',
        'apd_data',
        'sebab_kecelakaan',      // Kolom baru dari migrasi
        'sebab_utama',           // Kolom JSON baru
        'analisa_masalah',
        'tindakan_pencegahan',
        'rekomendasi',
        'pembuat_laporan_id',
        'manager_hse_id',
        'manager_terkait_id',
        'dept_head_id',
        'gm_id',
        'is_active',
        'revision_number',
        'revised_from_id'
    ];


protected $casts = [
        'date' => 'date',
        'waktu_kecelakaan' => 'datetime',
        'tanggal_lahir' => 'date',
        'tanggal_masuk' => 'date',
        'apd_data' => 'array',
        'sebab_utama' => 'array', // <-- INI YANG PALING PENTING UNTUK MEMPERBAIKI ERROR
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