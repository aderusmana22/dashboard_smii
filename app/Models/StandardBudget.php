<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StandardBudget extends Model
{
    use HasFactory;

    protected $fillable = [
        'name_region',
        'amount',
        'year',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'year' => 'integer',
    ];
}