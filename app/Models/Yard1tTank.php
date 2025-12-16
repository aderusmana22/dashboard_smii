<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Yard1tTank extends Model
{
    use HasFactory;
    protected $guarded = ['id'];

    public function readings(): HasMany
    {
        return $this->hasMany(Yard1tTankReading::class);
    }
}