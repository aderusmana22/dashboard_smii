<?php

namespace App\Services\Shopee;

use App\Exceptions\ShopeeApiException;
use App\Http\Controllers\Shopee\ShopeeApiTrait;
use App\Models\ShopeeShop;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ShopeeGetOrderListService
{
    use ShopeeApiTrait;

    /**
     * Mengambil semua detail pesanan dari API Shopee dalam 15 hari terakhir.
     */
    public function fetchAllOrders(): array
    {
        $orderSnList = $this->fetchOrderSnList();

        if (empty($orderSnList)) {
            Log::info('SYNC-SHOPEE: Tidak ada order_sn yang ditemukan.');
            return [];
        }

        return $this->fetchOrderDetails($orderSnList);
    }

    /**
     * Langkah 1: Mengambil daftar order_sn dari endpoint get_order_list.
     */
    private function fetchOrderSnList(): array
    {
        $allOrderSn = [];
        $cursor = "";
        $timeTo = Carbon::now()->unix();
        $timeFrom = Carbon::now()->subDays(15)->unix();

        Log::info('SYNC-SHOPEE: Memulai pengambilan daftar order_sn.');

        do {
            $params = [
                'time_range_field' => 'create_time',
                'time_from' => $timeFrom,
                'time_to' => $timeTo,
                'page_size' => 100,
                'cursor' => $cursor,
            ];

            $response = $this->makeApiCall('/api/v2/order/get_order_list', 'GET', $params);

            if (!empty($response['response']['order_list'])) {
                $orderSnFromPage = array_column($response['response']['order_list'], 'order_sn');
                $allOrderSn = array_merge($allOrderSn, $orderSnFromPage);
            }

            $cursor = $response['response']['next_cursor'] ?? '';
            $more = $response['response']['more'] ?? false;

        } while ($more && !empty($cursor));

        Log::info('SYNC-SHOPEE: Selesai mengambil daftar order_sn. Total: ' . count($allOrderSn));
        return array_unique($allOrderSn);
    }

    /**
     * Langkah 2: Mengambil detail lengkap pesanan dari endpoint get_order_detail.
     */
    private function fetchOrderDetails(array $orderSnList): array
    {
        $allOrderDetails = [];
        $orderSnChunks = array_chunk($orderSnList, 50);

        Log::info('SYNC-SHOPEE: Memulai pengambilan detail untuk ' . count($orderSnList) . ' pesanan dalam ' . count($orderSnChunks) . ' batch.');

        foreach ($orderSnChunks as $index => $chunk) {
            Log::info('SYNC-SHOPEE: Mengambil detail batch ke-' . ($index + 1));
            $params = [
                'order_sn_list' => implode(',', $chunk),
                'response_optional_fields' => 'item_list,total_amount,recipient_address,payment_method,shipping_carrier,pay_time,ship_by_date,create_time,cod,region,currency,estimated_shipping_fee,actual_shipping_fee',
            ];

            $response = $this->makeApiCall('/api/v2/order/get_order_detail', 'GET', $params);

            if (!empty($response['response']['order_list'])) {
                $allOrderDetails = array_merge($allOrderDetails, $response['response']['order_list']);
            }
        }

        Log::info('SYNC-SHOPEE: Pengambilan detail pesanan selesai. Total detail didapat: ' . count($allOrderDetails));
        return $allOrderDetails;
    }

    /**
     * Fungsi pembungkus untuk melakukan panggilan API dengan otentikasi.
     */
    private function makeApiCall(string $path, string $method = 'GET', array $queryParams = []): array
    {
        $shopConnection = ShopeeShop::first();
        if (!$shopConnection) {
            throw new ShopeeApiException('Koneksi toko Shopee tidak ditemukan.');
        }

        $this->initializeShopeeApi();
        
        if ($shopConnection->access_token_expires_at->isPast()) {
            Log::info('SYNC-SHOPEE: Access token kadaluarsa, mencoba untuk refresh.');
            $shopConnection = $this->refreshToken($shopConnection);
        }

        $timestamp = time();
        $baseParams = [
            'partner_id' => (int)$this->partnerId,
            'shop_id' => (int)$shopConnection->shop_id,
            'access_token' => $shopConnection->access_token,
            'timestamp' => $timestamp,
        ];

        $params = array_merge($baseParams, $queryParams);

        $params['sign'] = $this->generateApiSignature(
            $path,
            $timestamp,
            $shopConnection->access_token,
            (int)$shopConnection->shop_id
        );

        $fullUrl = $this->apiBaseUrl . $path;

        $response = Http::withHeaders(['Content-Type' => 'application/json']);

        if (strtoupper($method) === 'GET') {
            // == PERBAIKAN UTAMA DI SINI: Mengganti `params` menjadi $params ==
            $response = $response->get($fullUrl, $params);
        } else {
            $response = $response->post($fullUrl, $params);
        }

        if ($response->successful() && empty($response->json('error'))) {
            return $response->json();
        }

        Log::error('SYNC-SHOPEE: Panggilan API gagal.', [
            'url' => $fullUrl,
            'params' => $params,
            'response' => $response->body(),
        ]);

        throw new ShopeeApiException('Gagal mengambil data dari API Shopee: ' . $response->json('message', 'Unknown error'));
    }
}