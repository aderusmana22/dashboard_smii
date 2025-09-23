<?php
// app/Models/TiktokProduct.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TiktokProduct extends Model
{
    use HasFactory;

    protected $fillable = [
        'tiktok_product_id',
        'title',
        'status',
        'main_image_url',
        'total_stock',
        'price_range',
        'raw_data',
    ];

    protected $casts = [
        'raw_data' => 'array', // Otomatis konversi JSON ke/dari array
    ];
}