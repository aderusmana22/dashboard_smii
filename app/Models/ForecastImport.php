<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ForecastImport extends Model
{
    use HasFactory;

    protected $fillable = [
        'item_number',
        'description',
        'month',
        'year',
        'unit',
        'tonage',
    ];
}