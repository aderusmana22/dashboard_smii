<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LaporanApprovalHistory extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function laporanKecelakaan(): BelongsTo
    {
        return $this->belongsTo(LaporanKecelakaan::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}