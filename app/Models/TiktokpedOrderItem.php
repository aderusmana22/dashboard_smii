<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TiktokpedOrderItem extends Model
{
    use HasFactory;

    protected $table = 'tiktokped_order_items';

    protected $fillable = [
        'tiktokped_order_id',
        'line_item_id',
        'product_id',
        'product_name',
        'sku_id',
        'sku_name',
        'seller_sku',
        'sku_image',
        'quantity',
        'sale_price',
    ];

    /**
     * Mendefinisikan relasi bahwa satu item dimiliki oleh satu pesanan.
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(TiktokpedOrder::class, 'tiktokped_order_id');
    }
}