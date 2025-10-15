<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ShopeeOrder extends Model
{
    use HasFactory;

    protected $table = 'shopee_orders';

    protected $fillable = [
        'order_sn',
        'order_status',
        'region',
        'currency',
        'cod',
        'total_amount',
        'estimated_shipping_fee',
        'actual_shipping_fee',
        'payment_method',
        'shipping_carrier',
        'recipient_name',
        'recipient_phone',
        'recipient_full_address',
        'pay_time',
        'ship_by_date',
        'create_time_shopee',
        'raw_data',
        'buyer_user_id',
        'buyer_username',
    ];

    protected $casts = [
        'cod' => 'boolean',
        'pay_time' => 'datetime',
        'ship_by_date' => 'datetime',
        'create_time_shopee' => 'datetime',
        'raw_data' => 'array',
    ];

    /**
     * Mendapatkan semua item yang terkait dengan pesanan ini.
     */
    public function items(): HasMany
    {
        return $this->hasMany(ShopeeOrderItem::class);
    }
}