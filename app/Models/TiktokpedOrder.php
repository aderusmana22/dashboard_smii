<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TiktokpedOrder extends Model
{
    use HasFactory;

    protected $table = 'tiktokped_orders';

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

    protected $casts = [
        'paid_at' => 'datetime',
        'created_at_tiktok' => 'datetime',
        'raw_data' => 'array',
    ];

    /**
     * Mendefinisikan relasi bahwa satu pesanan memiliki banyak item.
     */
    public function items(): HasMany
    {
        return $this->hasMany(TiktokpedOrderItem::class);
    }
}