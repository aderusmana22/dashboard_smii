<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShopeeOrderItem extends Model
{
    use HasFactory;

    protected $table = 'shopee_order_items';

    public $timestamps = true;

    protected $fillable = [
        'shopee_order_id',
        'order_item_id',
        'item_id',
        'item_name',
        'item_sku',
        'model_id',
        'model_name',
        'model_sku',
        'model_quantity_purchased',
        'model_original_price',
        'model_discounted_price',
        'image_url',
    ];

    /**
     * Mendapatkan pesanan induk dari item ini.
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(ShopeeOrder::class, 'shopee_order_id');
    }
}