<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MasterProduct extends Model
{
    use HasFactory;
    protected $guarded = ['id'];

    public function tiktok_product()
    {
        return $this->belongsTo(TiktokProduct::class);
    }

    public function shopee_product()
    {
        return $this->belongsTo(ShopeeProduct::class);
    }
}