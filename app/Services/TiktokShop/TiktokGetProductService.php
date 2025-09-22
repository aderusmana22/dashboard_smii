<?php

namespace App\Services\TiktokShop;

use App\Exceptions\TiktokApiException;
use App\Http\Controllers\TiktokShop\TiktokApiTrait;
use App\Models\TiktokShop;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * TANGGUNG JAWAB: Hanya mengambil detail LENGKAP dari SATU produk
 * berdasarkan ID-nya menggunakan endpoint GET /products/{product_id}.
 */
class TiktokGetProductService
{
    use TiktokApiTrait;

    public function getProductDetail(string $productId): ?array
    {
        $shopConnection = TiktokShop::first();
        if (!$shopConnection) {
            throw new TiktokApiException('Koneksi toko TikTok tidak ditemukan di database.');
        }

        $this->initializeTiktokApi();
        if ($shopConnection->access_token_expires_at->isPast()) {
            $shopConnection = $this->refreshToken($shopConnection);
        }

        $shopCipher = $this->getShopCipher($shopConnection);

        $path = "/product/202309/products/{$productId}";
        $timestamp = time();

        $params = [
            'app_key'     => $this->appKey,
            'timestamp'   => $timestamp,
            'shop_cipher' => $shopCipher,
        ];

        $params['sign'] = $this->generateSignature($path, $params);

        $response = Http::withHeaders([
            'x-tts-access-token' => $shopConnection->access_token,
        ])->get($this->apiBaseUrl . $path, $params);

        if ($response->successful() && $response->json('code') === 0) {
            return $response->json('data');
        }

        Log::warning("Gagal mengambil detail produk TikTok (ID: {$productId})", ['body' => $response->body()]);
        // Kembalikan null agar perulangan bisa lanjut jika satu produk gagal
        return null;
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