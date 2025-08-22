<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LaporanApprovalStatus extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function laporanKecelakaan(): BelongsTo
    {
        return $this->belongsTo(LaporanKecelakaan::class);
    }

    public function currentApprover(): BelongsTo
    {
        return $this->belongsTo(User::class, 'current_approver_id');
    }
}