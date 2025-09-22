<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'tiktok_order_id',
        'status',
        'total_amount',
        'sub_total',
        'shipping_fee',
        'platform_discount',
        'payment_method',
        'shipping_provider',
        'tracking_number',
        'recipient_name',
        'recipient_phone',
        'recipient_full_address',
        'paid_at',
        'created_at_tiktok',
        'raw_data',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'paid_at' => 'datetime',
        'created_at_tiktok' => 'datetime',
        'raw_data' => 'array', // Otomatis konversi JSON ke array dan sebaliknya
    ];
}