<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalesTransaction extends Model
{
    use HasFactory;

    protected $table = 'sales';

    public $timestamps = false;

    protected $casts = [
        'tr_effdate' => 'date',
        'tr_ton' => 'float',
        'value' => 'float',
        'margin' => 'float',
    ];

}