<?php

namespace App\Services\Shopee;

use App\Exceptions\ShopeeApiException;
use App\Http\Controllers\Shopee\ShopeeApiTrait;
use App\Models\ShopeeProduct;
use App\Models\ShopeeShop;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ShopeeUpdatePriceService
{
    use ShopeeApiTrait;

    /**
     * Memperbarui harga untuk sebuah item di Shopee.
     *
     * @param ShopeeProduct $product Produk Shopee yang akan diupdate.
     * @param float $newPrice Harga baru.
     * @return array Response dari API Shopee.
     * @throws ShopeeApiException Jika terjadi error saat panggilan API.
     */
    public function updatePrice(ShopeeProduct $product, float $newPrice): array
    {
        // Mendekode data mentah untuk mendapatkan info model/varian
        $productData = json_decode($product->raw_data, true);
        $priceList = [];

        // Jika produk memiliki model (varian)
        if (!empty($productData['model'])) {
            foreach ($productData['model'] as $model) {
                $priceList[] = [
                    'model_id' => $model['model_id'],
                    'original_price' => $newPrice,
                ];
            }
        } else {
            // Jika produk tidak memiliki model (produk tunggal)
            $priceList[] = [
                'model_id' => 0,
                'original_price' => $newPrice,
            ];
        }

        $body = [
            'item_id' => (int)$product->shopee_item_id,
            'price_list' => $priceList,
        ];

        return $this->makeApiCall('/api/v2/product/update_price', 'POST', [], $body);
    }

    /**
     * Fungsi pembungkus untuk melakukan panggilan API dengan otentikasi.
     */
    private function makeApiCall(string $path, string $method = 'POST', array $queryParams = [], array $body = []): array
    {
        $shopConnection = ShopeeShop::first();
        if (!$shopConnection) {
            throw new ShopeeApiException('Koneksi toko Shopee tidak ditemukan.');
        }

        $this->initializeShopeeApi();
        if ($shopConnection->access_token_expires_at->isPast()) {
            $shopConnection = $this->refreshToken($shopConnection);
        }

        $timestamp = time();
        $baseParams = [
            'partner_id' => (int)$this->partnerId,
            'shop_id' => (int)$shopConnection->shop_id,
            'access_token' => $shopConnection->access_token,
            'timestamp' => $timestamp,
        ];

        $paramsForSignature = array_merge($baseParams, $queryParams);
        $signature = $this->generateApiSignature(
            $path,
            $timestamp,
            $shopConnection->access_token,
            (int)$shopConnection->shop_id
        );

        $fullUrl = $this->apiBaseUrl . $path . '?' . http_build_query(array_merge($paramsForSignature, ['sign' => $signature]));

        $response = Http::withHeaders(['Content-Type' => 'application/json'])->post($fullUrl, $body);

        if ($response->successful() && empty($response->json('error'))) {
            Log::info("SHOPEE-PRICE-UPDATE: Berhasil update harga.", ['response' => $response->json()]);
            return $response->json();
        }

        Log::error('SHOPEE-PRICE-UPDATE: Panggilan API gagal.', [
            'url' => $fullUrl,
            'body' => $body,
            'response' => $response->body(),
        ]);

        throw new ShopeeApiException('Gagal update harga di Shopee: ' . $response->json('message', 'Unknown error'));
    }
}