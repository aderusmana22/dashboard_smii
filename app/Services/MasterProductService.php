<?php
namespace App\Services;

use App\Models\MasterProduct;
use App\Models\ShopeeProduct;
use App\Models\TiktokProduct;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MasterProductService
{
    public function syncMasterTable(): void
    {
        Log::info('Memulai sinkronisasi tabel master produk berdasarkan NAMA.');
        DB::transaction(function () {
            $tiktokProducts = TiktokProduct::all()->keyBy('title');
            $shopeeProducts = ShopeeProduct::all()->keyBy('item_name');

            $allProductTitles = $tiktokProducts->keys()->merge($shopeeProducts->keys())->unique();

            foreach ($allProductTitles as $title) {
                $tiktokProduct = $tiktokProducts->get($title);
                $shopeeProduct = $shopeeProducts->get($title);

                // Tentukan data utama
                $mainImageUrl = optional($tiktokProduct)->main_image_url ?? optional($shopeeProduct)->main_image_url;
                
                // === LOGIKA STOK BARU ===
                // Prioritaskan stok dari TikTok. Jika tidak ada, ambil dari Shopee.
                // Ini menjadi satu-satunya nilai stok yang benar.
                $masterStock = optional($tiktokProduct)->total_stock ?? optional($shopeeProduct)->total_stock ?? 0;

                MasterProduct::updateOrCreate(
                    ['title' => $title],
                    [
                        'main_image_url'    => $mainImageUrl,
                        'total_stock'       => $masterStock, // Simpan stok dari sumber prioritas
                        'tiktok_product_id' => optional($tiktokProduct)->id,
                        'shopee_product_id' => optional($shopeeProduct)->id,
                    ]
                );
            }
        });
        Log::info('Sinkronisasi tabel master produk selesai.');
    }
}