<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tank extends Model
{
    use HasFactory;

    protected $guarded = ['id']; // Memudahkan mass assignment

    public function oilStockReadings(): HasMany
    {
        return $this->hasMany(OilStockReading::class);
    }
}