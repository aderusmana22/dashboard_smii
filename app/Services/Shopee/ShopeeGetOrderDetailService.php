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
        $sign = $this->generateApiSignature($path, $timestamp, $shop->access_token, (int) $shop->shop_id);
        $optionalFields = 'item_list,buyer_user_id';

        $response = Http::get($this->apiBaseUrl . $path, [
            'partner_id' => (int) $this->partnerId,
            'timestamp' => $timestamp,
            'access_token' => $shop->access_token,
            'shop_id' => (int) $shop->shop_id,
            'sign' => $sign,
            'order_sn_list' => $orderSn,
            'response_optional_fields' => $optionalFields,
        ]);

        if ($response->failed() || !empty($response->json('error'))) {
            $errorMsg = $response->json('message', 'Unknown Shopee API error');
            Log::error("Gagal mengambil detail pesanan Shopee {$orderSn}", ['response' => $response->body()]);
            throw new ShopeeApiException("Gagal mengambil detail pesanan Shopee: {$errorMsg}");
        }
        return $response->json('response.order_list.0');
    }
}