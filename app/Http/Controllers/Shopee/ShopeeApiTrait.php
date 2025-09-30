<?php

namespace App\Http\Controllers\Shopee;

use App\Models\ShopeeShop;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

trait ShopeeApiTrait
{
    protected $partnerId;
    protected $partnerKey;
    protected $apiBaseUrl = 'https://partner.shopeemobile.com';

    /**
     * Inisialisasi kredensial API dari file config.
     */
    protected function initializeShopeeApi()
    {
        $this->partnerId = config('services.shopee.partner_id');
        $this->partnerKey = config('services.shopee.partner_key');
    }

    /**
     * == PERBAIKAN 1: Nama fungsi diubah agar lebih jelas ==
     * Membuat signature untuk proses otentikasi (misal: refresh token).
     * Base string: partner_id + path + timestamp
     */
    protected function generateAuthSignature(string $path, int $timestamp): string
    {
        $baseString = sprintf("%s%s%s", $this->partnerId, $path, $timestamp);
        return hash_hmac('sha256', $baseString, $this->partnerKey);
    }

    /**
     * == PERBAIKAN 2: Fungsi baru yang benar untuk panggilan API umum ==
     * Membuat signature untuk panggilan API yang memerlukan access_token dan shop_id.
     * Base string: partner_id + path + timestamp + access_token + shop_id
     */
    protected function generateApiSignature(string $path, int $timestamp, string $accessToken, int $shopId): string
    {
        $baseString = sprintf(
            "%s%s%s%s%s",
            $this->partnerId,
            $path,
            $timestamp,
            $accessToken,
            $shopId
        );
        return hash_hmac('sha256', $baseString, $this->partnerKey);
    }

    /**
     * Me-refresh access token yang sudah kadaluarsa.
     */
    protected function refreshToken(ShopeeShop $shop): ShopeeShop
    {
        $this->initializeShopeeApi();

        $path = '/api/v2/auth/access_token/get';
        $timestamp = time();
        
        // == PERBAIKAN 3: Memanggil fungsi signature yang benar untuk otentikasi ==
        $sign = $this->generateAuthSignature($path, $timestamp);

        $url = $this->apiBaseUrl . $path . '?' . http_build_query([
            'partner_id' => (int) $this->partnerId,
            'timestamp' => $timestamp,
            'sign' => $sign,
        ]);

        $response = Http::post($url, [
            'refresh_token' => $shop->refresh_token,
            'shop_id' => (int) $shop->shop_id,
            'partner_id' => (int) $this->partnerId,
        ]);

        if ($response->failed() || !empty($response->json('error'))) {
            Log::error('Shopee Token Refresh Failed', [
                'shop_id' => $shop->shop_id,
                'response' => $response->body()
            ]);
            return $shop;
        }

        $data = $response->json();

        $shop->update([
            'access_token' => $data['access_token'],
            'refresh_token' => $data['refresh_token'],
            'access_token_expires_at' => Carbon::now()->addSeconds($data['expire_in']),
            'refresh_token_expires_at' => Carbon::now()->addDays(30),
        ]);

        Log::info('Shopee token refreshed successfully for shop_id: ' . $shop->shop_id);

        return $shop->fresh();
    }
}