<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PackingTankReading extends Model
{
    use HasFactory;
    protected $guarded = ['id'];
    protected $casts = ['reading_date' => 'datetime'];

    public function packingTank(): BelongsTo
    {
        return $this->belongsTo(PackingTank::class);
    }
}