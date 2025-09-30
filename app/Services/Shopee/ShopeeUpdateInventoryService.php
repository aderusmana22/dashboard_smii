<?php

namespace App\Services\Shopee;

use App\Exceptions\ShopeeApiException;
use App\Http\Controllers\Shopee\ShopeeApiTrait;
use App\Models\ShopeeProduct;
use App\Models\ShopeeShop;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ShopeeUpdateInventoryService
{
    use ShopeeApiTrait;

    /**
     * Memperbarui stok satu produk di Shopee.
     *
     * @param ShopeeProduct $product Model produk dari tabel shopee_products.
     * @param int $newStock Jumlah stok baru.
     * @return bool True jika berhasil, throws ShopeeApiException jika gagal.
     */
    public function updateInventory(ShopeeProduct $product, int $newStock): bool
    {
        $shop = ShopeeShop::first();
        if (!$shop) {
            throw new ShopeeApiException('Koneksi toko Shopee tidak ditemukan di database.');
        }

        $this->initializeShopeeApi();

        if ($shop->access_token_expires_at->isPast()) {
            $shop = $this->refreshToken($shop);
        }

        $path = '/api/v2/product/update_stock';
        $timestamp = time();

        // <-- PERBAIKAN UTAMA DI SINI -->
        // Panggil generateApiSignature dengan urutan dan argumen yang benar.
        $sign = $this->generateApiSignature(
            $path,
            $timestamp,
            $shop->access_token,
            (int)$shop->shop_id
        );

        // Bangun URL lengkap dengan query parameters
        $url = $this->apiBaseUrl . $path . '?' . http_build_query([
            'partner_id'   => (int)$this->partnerId, // Pastikan partner_id juga integer
            'timestamp'    => $timestamp,
            'access_token' => $shop->access_token,
            'shop_id'      => (int)$shop->shop_id,
            'sign'         => $sign,
        ]);

        // Siapkan body request sesuai dokumentasi API
        $body = [
            'item_id' => (int) $product->shopee_item_id,
            'stock_list' => [
                [
                    // Asumsi produk sederhana tanpa varian (model)
                    'model_id' => 0,
                    'seller_stock' => [
                        [
                            // API tidak memerlukan location_id jika seller tidak punya multi-gudang
                            'stock' => $newStock
                        ]
                    ]
                ]
            ]
        ];

        // Kirim request ke API Shopee
        $response = Http::post($url, $body);

        // Periksa dan tangani respons dari API
        if ($response->successful() && empty($response->json('error')) && empty($response->json('response.failure_list'))) {
            Log::info("Stok berhasil diperbarui untuk produk Shopee ID: {$product->shopee_item_id} menjadi {$newStock}");
            return true;
        }

        // Jika gagal, log error dan lemparkan exception
        $errorMessage = $response->json('message', 'Terjadi kesalahan saat menghubungi API Shopee.');
        Log::error("Gagal memperbarui stok produk Shopee ID: {$product->shopee_item_id}", [
            'url' => $url,
            'body' => $body,
            'response' => $response->body()
        ]);

        throw new ShopeeApiException("Gagal memperbarui stok Shopee: {$errorMessage}");
    }
}