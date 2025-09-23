<?php

namespace App\Services\TiktokShop;

use App\Exceptions\TiktokApiException;
use App\Http\Controllers\TiktokShop\TiktokApiTrait;
use App\Models\TiktokProduct;
use App\Models\TiktokShop;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TiktokUpdateInventoryService
{
    use TiktokApiTrait;

    public function updateInventory(TiktokProduct $product, int $newTotalStock): bool
    {
        $shopConnection = TiktokShop::firstOrFail();
        $this->initializeTiktokApi();
        if ($shopConnection->access_token_expires_at->isPast()) {
            $shopConnection = $this->refreshToken($shopConnection);
        }

        $shopCipher = $this->getShopCipher($shopConnection);
        $productId = $product->tiktok_product_id;

        // ==================================================================
        // --- PERBAIKAN UTAMA ADA DI SINI ---
        // Secara eksplisit decode JSON string menjadi array PHP.
        // ==================================================================
        $productDataArray = json_decode($product->raw_data, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            // Tambahkan penanganan jika JSON tidak valid
            throw new TiktokApiException("Data mentah (raw_data) untuk produk ID {$productId} bukan JSON yang valid.");
        }
        
        // Sekarang gunakan data_get pada array yang sudah pasti.
        $skus = data_get($productDataArray, 'skus', []);

        if (empty($skus)) {
            throw new TiktokApiException("Tidak ada data SKU ditemukan untuk produk ID: {$productId}");
        }

        $firstSku = $skus[0];
        $firstWarehouseId = data_get($firstSku, 'inventory.0.warehouse_id');

        if (!$firstWarehouseId) {
            throw new TiktokApiException("Tidak ada ID gudang ditemukan untuk SKU pertama produk ID: {$productId}");
        }

        $body = [
            'skus' => [
                [
                    'id' => $firstSku['id'],
                    'inventory' => [
                        [
                            'warehouse_id' => $firstWarehouseId,
                            'quantity' => $newTotalStock,
                        ]
                    ]
                ]
            ]
        ];

        $path = "/product/202309/products/{$productId}/inventory/update";
        $timestamp = time();
        $bodyJsonString = json_encode($body);

        $params = [
            'app_key'     => $this->appKey,
            'timestamp'   => $timestamp,
            'shop_cipher' => $shopCipher,
        ];

        $params['sign'] = $this->generateSignature($path, $params, $bodyJsonString);
        $fullUrl = $this->apiBaseUrl . $path . '?' . http_build_query($params);

        $response = Http::withHeaders([
            'x-tts-access-token' => $shopConnection->access_token,
            'Content-Type'       => 'application/json',
        ])->post($fullUrl, $body);

        if ($response->successful() && $response->json('code') === 0) {
            Log::info("Stok berhasil diperbarui untuk produk TikTok ID: {$productId}");
            return true;
        }

        Log::error("Gagal memperbarui stok produk TikTok ID: {$productId}", ['body' => $response->body()]);
        $errorMessage = $response->json('message', 'Terjadi kesalahan saat menghubungi API TikTok.');
        throw new TiktokApiException("Gagal memperbarui stok: {$errorMessage}");
    }

    private function getShopCipher(TiktokShop $shopConnection): string
    {
        $path = '/authorization/202309/shops';
        $timestamp = time();
        $params = ['app_key' => $this->appKey, 'timestamp' => $timestamp];
        $params['sign'] = $this->generateSignature($path, $params);

        $response = Http::withHeaders(['x-tts-access-token' => $shopConnection->access_token])
                        ->get($this->apiBaseUrl . $path, $params);

        if ($response->successful() && $response->json('code') === 0 && !empty($response->json('data.shops'))) {
            return $response->json('data.shops')[0]['cipher'];
        }
        throw new TiktokApiException('Gagal mendapatkan informasi toko (shop_cipher).');
    }
}