<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Yard1tTankReading extends Model
{
    use HasFactory;
    protected $guarded = ['id'];
    protected $casts = ['reading_date' => 'datetime'];

    public function yard1tTank(): BelongsTo
    {
        return $this->belongsTo(Yard1tTank::class);
    }
}