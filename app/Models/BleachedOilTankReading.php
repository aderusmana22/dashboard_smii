<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BleachedOilTankReading extends Model
{
    use HasFactory;
    protected $guarded = ['id'];
    protected $casts = ['reading_date' => 'datetime'];

    public function bleachedOilTank(): BelongsTo
    {
        return $this->belongsTo(BleachedOilTank::class);
    }
}