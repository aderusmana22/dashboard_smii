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
        'sku',
        'status',
        'main_image_url',
        'total_stock',
        'price_range',
        'raw_data',
    ];

    // Kita tetap biarkan casts ini, karena ini adalah praktik yang baik.
    // Kode di bawah akan berfungsi bahkan jika casts ini gagal karena suatu alasan.
    protected $casts = [
        'raw_data' => 'array',
    ];

    /**
     * == ACCESSOR FINAL (ROBUST VERSION) ==
     *
     * Kode ini akan berfungsi baik jika $casts berhasil maupun gagal.
     */
    public function getDisplayPriceAttribute(): string
    {
        $rawData = $this->raw_data;

        // 1. Periksa jika data mentah kosong.
        if (empty($rawData)) {
            return 'N/A';
        }

        // 2. Cek tipe data. Jika masih string, decode dulu.
        //    Ini adalah langkah pengaman jika $casts tidak bekerja.
        if (is_string($rawData)) {
            $data = json_decode($rawData, true);
        } else {
            $data = $rawData; // Jika sudah array, langsung gunakan.
        }

        // 3. Periksa keamanan setelah decode: pastikan data, skus, dan price ada.
        if (empty($data) || !isset($data['skus']) || empty($data['skus'])) {
            return 'N/A';
        }

        // 4. Ambil harga dari kunci 'sale_price'.
        $price = $data['skus'][0]['price']['sale_price'] ?? null;

        // 5. Periksa apakah harga yang didapat adalah angka.
        if (is_numeric($price)) {
            // 6. Format sebagai Rupiah.
            return 'Rp ' . number_format($price, 0, ',', '.');
        }

        // 7. Jika semua gagal, kembalikan 'N/A'.
        return 'N/A';
    }
}