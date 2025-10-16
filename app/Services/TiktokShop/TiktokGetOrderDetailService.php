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

        $path = '/order/202309/orders';
        $timestamp = time();
        $body = json_encode(['order_ids' => [$orderId]]);
        $params = [
            'app_key' => $this->appKey,
            'timestamp' => $timestamp,
            'shop_cipher' => $this->getShopCipher($shop),
        ];
        $params['sign'] = $this->generateSignature($path, $params, $body);
        $fullUrl = $this->apiBaseUrl . $path . '?' . http_build_query($params);

        $response = Http::withHeaders([
            'x-tts-access-token' => $shop->access_token,
            'Content-Type' => 'application/json',
        ])->post($fullUrl, json_decode($body, true));

        if ($response->failed() || $response->json('code') !== 0) {
            $errorMsg = $response->json('message', 'Unknown TikTok API error');
            Log::error("Gagal mengambil detail pesanan TikTok {$orderId}", ['response' => $response->body()]);
            throw new TiktokApiException("Gagal mengambil detail pesanan TikTok: {$errorMsg}");
        }
        return $response->json('data.orders.0');
    }
}