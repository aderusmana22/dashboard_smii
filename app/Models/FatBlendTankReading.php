<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FatBlendTankReading extends Model
{
    use HasFactory;
    protected $guarded = ['id'];
    protected $casts = ['reading_date' => 'datetime'];

    public function fatBlendTank(): BelongsTo
    {
        return $this->belongsTo(FatBlendTank::class);
    }
}