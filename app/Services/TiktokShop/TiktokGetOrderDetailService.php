<?php

namespace App\Services\TiktokShop;

use App\Exceptions\TiktokApiException;
use App\Http\Controllers\TiktokShop\TiktokApiTrait;
use App\Models\TiktokShop;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TiktokGetOrderDetailService
{
    use TiktokApiTrait;

    public function getOrderDetail(string $orderId): ?array
    {
        $shop = TiktokShop::firstOrFail();
        $this->initializeTiktokApi();

        if ($shop->access_token_expires_at->isPast()) {
            $shop = $this->refreshToken($shop);
        }

        $path = '/order/202507/orders'; // Menggunakan path yang Anda berikan
        $timestamp = time();

        $params = [
            'app_key' => $this->appKey,
            'timestamp' => $timestamp,
            'shop_cipher' => $this->getShopCipher($shop),
            // =================================================================
            // <-- KODE YANG DIPERBAIKI ADA DI SINI -->
            // Nama parameter diubah dari 'order_ids' menjadi 'ids'.
            'ids' => $orderId,
            // =================================================================
        ];

        $params['sign'] = $this->generateSignature($path, $params);
        
        $fullUrl = $this->apiBaseUrl . $path . '?' . http_build_query($params);

        $response = Http::withHeaders([
            'x-tts-access-token' => $shop->access_token,
            'Content-Type' => 'application/json', 
        ])->get($fullUrl);

        if ($response->failed() || $response->json('code') !== 0) {
            $errorMsg = $response->json('message', 'Unknown TikTok API error');
            Log::error("Gagal mengambil detail pesanan TikTok {$orderId}", ['response' => $response->body()]);
            throw new TiktokApiException("Gagal mengambil detail pesanan TikTok: {$errorMsg}");
        }
        
        return $response->json('data.orders.0');
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

        throw new TiktokApiException('Gagal mendapatkan informasi toko (shop_cipher). Response: ' . $response->body());
    }
}