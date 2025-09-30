<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShopeeProduct extends Model
{
    use HasFactory;
    protected $guarded = ['id'];

      public function getDisplayPriceAttribute(): string
    {
        $data = json_decode($this->raw_data, true);

        // Mencari harga dari price_info pertama yang tersedia
        $price = $data['price_info'][0]['current_price'] ?? null;

        if (is_numeric($price)) {
            // Format sebagai Rupiah tanpa desimal
            return 'Rp ' . number_format($price, 0, ',', '.');
        }

        return 'N/A'; // Tampilkan N/A jika harga tidak ditemukan
    }
}