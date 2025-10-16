<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EcommerceOrder extends Model
{
    use HasFactory;

    /**
     * Properti yang dapat diisi secara massal.
     */
    protected $fillable = [
        'platform',
        'platform_order_id',
        'platform_status',
        'stock_sync_status',
        'line_items',
        'processed_at',
    ];

    /**
     * Tipe data cast untuk atribut.
     * 'line_items' akan otomatis di-decode/encode dari/ke JSON.
     */
    protected $casts = [
        'line_items' => 'array',
        'processed_at' => 'datetime',
    ];
}