<?php

namespace App\Http\Controllers\TiktokShop;

use App\Models\TiktokShop;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

trait TiktokApiTrait
{
    protected $appKey;
    protected $appSecret;
    protected $apiBaseUrl = 'https://open-api.tiktokglobalshop.com';
    protected $authBaseUrl = 'https://auth.tiktok-shops.com';

    /**
     * Inisialisasi kredensial API dari file config.
     */
    protected function initializeTiktokApi()
    {
        $this->appKey = config('services.tiktok.key');
        $this->appSecret = config('services.tiktok.secret');
    }

    /**
     * Membuat signature HMAC-SHA265 untuk request API.
     */
    protected function generateSignature(string $path, array $params, string $body = ''): string
    {
        $paramStr = '';
        ksort($params);
        foreach ($params as $key => $value) {
            if ($key !== 'sign' && $key !== 'access_token') {
                $paramStr .= $key . $value;
            }
        }

        $baseString = $path . $paramStr . $body;
        $input = $this->appSecret . $baseString . $this->appSecret;

        return hash_hmac('sha256', $input, $this->appSecret);
    }

    /**
     * Me-refresh access token yang sudah kadaluarsa.
     */
    protected function refreshToken(TiktokShop $shop): TiktokShop
    {
        $this->initializeTiktokApi();

        $response = Http::get($this->authBaseUrl . '/api/v2/token/refresh', [
            'app_key' => $this->appKey,
            'app_secret' => $this->appSecret,
            'refresh_token' => $shop->refresh_token,
            'grant_type' => 'refresh_token',
        ]);

        if ($response->failed() || $response->json('code') !== 0) {
            Log::error('TikTok Token Refresh Failed', ['response' => $response->body()]);
            // Di aplikasi nyata, Anda mungkin ingin melempar exception di sini
            return $shop;
        }

        $data = $response->json('data');

        // Perbarui token di database
        $shop->update([
            'access_token' => $data['access_token'],
            'refresh_token' => $data['refresh_token'],
            'access_token_expires_at' => Carbon::createFromTimestamp($data['access_token_expire_in']),
            'refresh_token_expires_at' => Carbon::createFromTimestamp($data['refresh_token_expire_in']),
        ]);

        return $shop->fresh(); // Kembalikan model yang sudah di-refresh
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