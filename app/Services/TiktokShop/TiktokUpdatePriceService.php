<?php

namespace App\Services\TiktokShop;

use App\Exceptions\TiktokApiException;
use App\Http\Controllers\TiktokShop\TiktokApiTrait;
use App\Models\TiktokProduct;
use App\Models\TiktokShop;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TiktokUpdatePriceService
{
    use TiktokApiTrait;

    /**
     * Memperbarui harga untuk semua SKU dari sebuah produk di TikTok Shop.
     *
     * @param TiktokProduct $product Produk TikTok yang akan diupdate.
     * @param string $newPrice Harga baru dalam format string (misal: "50000").
     * @return array Response dari API TikTok.
     * @throws TiktokApiException Jika terjadi error saat panggilan API.
     */
    public function updatePrice(TiktokProduct $product, string $newPrice): array
    {
        $shopConnection = TiktokShop::first();
        if (!$shopConnection) {
            throw new TiktokApiException('Koneksi toko TikTok tidak ditemukan.');
        }

        $this->initializeTiktokApi();
        if ($shopConnection->access_token_expires_at->isPast()) {
            $shopConnection = $this->refreshToken($shopConnection);
        }

        // Mendekode data mentah untuk mendapatkan daftar SKU
        $productData = json_decode($product->raw_data, true);
        if (empty($productData['skus'])) {
            throw new TiktokApiException("Tidak ada SKU yang ditemukan untuk produk TikTok ID: {$product->tiktok_product_id}");
        }

        // Mempersiapkan payload untuk API
        $skusPayload = [];
        foreach ($productData['skus'] as $sku) {
            $skusPayload[] = [
                'id' => $sku['id'],
                'price' => [
                    // Asumsi mata uang adalah IDR, sesuaikan jika perlu
                    'currency' => 'IDR',
                    'amount' => $newPrice,
                ],
            ];
        }

        $body = ['skus' => $skusPayload];
        $path = '/product/202309/products/' . $product->tiktok_product_id . '/prices/update';

        return $this->makeApiCall($path, $shopConnection, $body);
    }

    /**
     * Melakukan panggilan API ke TikTok.
     */
    private function makeApiCall(string $path, TiktokShop $shopConnection, array $body): array
    {
        $shopCipher = $this->getShopCipher($shopConnection);
        $timestamp = time();
        $params = [
            'app_key'     => $this->appKey,
            'timestamp'   => $timestamp,
            'shop_cipher' => $shopCipher,
        ];

        $bodyJsonString = json_encode($body);
        $params['sign'] = $this->generateSignature($path, $params, $bodyJsonString);
        $fullUrl = $this->apiBaseUrl . $path . '?' . http_build_query($params);

        $response = Http::withHeaders([
            'x-tts-access-token' => $shopConnection->access_token,
            'content-type'       => 'application/json',
        ])->post($fullUrl, $body);

        if ($response->successful() && $response->json('code') === 0) {
            Log::info("TIKTOK-PRICE-UPDATE: Berhasil update harga untuk produk.", ['response' => $response->json()]);
            return $response->json();
        }

        Log::error("TIKTOK-PRICE-UPDATE: Gagal update harga.", [
            'url' => $fullUrl,
            'body' => $body,
            'response' => $response->body(),
        ]);

        throw new TiktokApiException('Gagal update harga di TikTok: ' . $response->json('message', 'Unknown error'));
    }
}