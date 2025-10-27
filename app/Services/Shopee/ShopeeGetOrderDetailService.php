<?php

namespace App\Services\Shopee;

use App\Exceptions\ShopeeApiException;
use App\Http\Controllers\Shopee\ShopeeApiTrait;
use App\Models\ShopeeShop;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ShopeeGetOrderDetailService
{
    use ShopeeApiTrait;

    public function getOrderDetail(string $orderSn): ?array
    {
        $shop = ShopeeShop::firstOrFail();
        $this->initializeShopeeApi();

        if ($shop->access_token_expires_at->isPast()) {
            $shop = $this->refreshToken($shop);
        }

        $path = '/api/v2/order/get_order_detail';
        $timestamp = time();
        
        // =================================================================
        // <-- KODE YANG DIPERBAIKI ADA DI SINI -->
        // Meminta semua field yang dibutuhkan untuk penyimpanan data.
        $optionalFields = 'item_list,recipient_address,total_amount,payment_method,shipping_carrier,create_time,pay_time,currency';
        // =================================================================

        $params = [
            'partner_id' => (int) $this->partnerId,
            'timestamp' => $timestamp,
            'access_token' => $shop->access_token,
            'shop_id' => (int) $shop->shop_id,
            'order_sn_list' => $orderSn,
            'response_optional_fields' => $optionalFields,
        ];
        
        $params['sign'] = $this->generateApiSignature($path, $timestamp, $shop->access_token, (int) $shop->shop_id);

        $response = Http::get($this->apiBaseUrl . $path, $params);

        if ($response->failed() || !empty($response->json('error'))) {
            $errorMsg = $response->json('message', 'Unknown Shopee API error');
            Log::error("Gagal mengambil detail pesanan Shopee {$orderSn}", ['response' => $response->body()]);
            throw new ShopeeApiException("Gagal mengambil detail pesanan Shopee: {$errorMsg}");
        }
        
        return $response->json('response.order_list.0');
    }
}