<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UtilityGasReading extends Model
{
    use HasFactory;
    protected $guarded = ['id'];
    protected $casts = ['reading_date' => 'datetime'];
}