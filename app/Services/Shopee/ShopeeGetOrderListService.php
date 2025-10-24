<?php

namespace App\Services\Shopee;

use App\Exceptions\ShopeeApiException;
use App\Http\Controllers\Shopee\ShopeeApiTrait;
use App\Models\EcommerceOrder;
use App\Models\EcommerceSetting;
use App\Models\ShopeeShop;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ShopeeGetOrderListService
{
    use ShopeeApiTrait;

    public function syncOrdersSinceLastUpdate(): void
    {
        $orderSnList = $this->fetchUpdatedOrderSnList();

        if (empty($orderSnList)) {
            return;
        }

        $allOrderDetails = $this->fetchOrderDetailsInBatches($orderSnList);

        Log::info("SYNC-SHOPEE: Ditemukan " . count($allOrderDetails) . " detail pesanan untuk diproses.");
        foreach ($allOrderDetails as $orderData) {
            $this->saveOrUpdateOrder($orderData);
        }

        EcommerceSetting::updateOrCreate(
            ['key' => 'shopee_orders_last_sync'],
            ['value' => Carbon::now()->toDateTimeString()]
        );
        Log::info('SYNC-SHOPEE: Sinkronisasi selesai. Waktu sync terakhir telah diperbarui ke hari ini.');
    }

    /**
     * Mengambil daftar order_sn dengan memecah permintaan menjadi interval 14 hari
     * untuk mematuhi batasan API Shopee.
     */
    private function fetchUpdatedOrderSnList(): array
    {
        $lastSyncSetting = EcommerceSetting::find('shopee_orders_last_sync');
        // <-- PERUBAHAN LOGIKA DI SINI
        // Cek apakah sinkronisasi sudah berjalan dalam 3 hari terakhir.
        if ($lastSyncSetting && Carbon::parse($lastSyncSetting->value)->greaterThan(Carbon::now()->subDays(3))) {
            Log::info('SYNC-SHOPEE: Sinkronisasi sudah dijalankan dalam 3 hari terakhir. Proses dilewati.');
            return [];
        }

        Log::info("SYNC-SHOPEE (Full Sync): Menjalankan sinkronisasi penuh karena belum ada sinkronisasi dalam 3 hari terakhir.");

        $allOrderSn = [];
        $endDate = Carbon::now();
        $currentStartDate = Carbon::now()->subYear();

        Log::info("SYNC-SHOPEE (Step 1): Memulai pengambilan order_sn dari {$currentStartDate->toDateString()} hingga {$endDate->toDateString()}.");

        while ($currentStartDate->lessThan($endDate)) {
            $currentEndDate = $currentStartDate->copy()->addDays(14);
            if ($currentEndDate->greaterThan($endDate)) {
                $currentEndDate = $endDate;
            }

            Log::info("SYNC-SHOPEE: Mengambil potongan data antara {$currentStartDate->toDateTimeString()} dan {$currentEndDate->toDateTimeString()}.");

            $timeFrom = $currentStartDate->unix();
            $timeTo = $currentEndDate->unix();
            $cursor = "";

            do {
                $params = [
                    'time_range_field' => 'update_time',
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

            $currentStartDate = $currentEndDate->copy()->addSecond();
        }

        Log::info("SYNC-SHOPEE (Step 1 Selesai): Total " . count($allOrderSn) . " order_sn unik ditemukan.");
        return array_unique($allOrderSn);
    }

    private function fetchOrderDetailsInBatches(array $orderSnList): array
    {
        $allOrderDetails = [];
        $orderSnChunks = array_chunk($orderSnList, 50);

        Log::info('SYNC-SHOPEE (Step 2): Mengambil detail untuk ' . count($orderSnList) . ' pesanan dalam ' . count($orderSnChunks) . ' batch.');

        foreach ($orderSnChunks as $index => $chunk) {
            $params = [
                'order_sn_list' => implode(',', $chunk),
                'response_optional_fields' => 'item_list,order_status,order_sn',
            ];

            $response = $this->makeApiCall('/api/v2/order/get_order_detail', 'GET', $params);

            if (!empty($response['response']['order_list'])) {
                $allOrderDetails = array_merge($allOrderDetails, $response['response']['order_list']);
            }
        }

        return $allOrderDetails;
    }

    private function saveOrUpdateOrder(array $orderData): void
    {
        $orderSn = $orderData['order_sn'];
        $newStatus = $orderData['order_status'];

        $order = EcommerceOrder::updateOrCreate(
            ['platform' => 'shopee', 'platform_order_id' => $orderSn],
            ['platform_status' => $newStatus, 'line_items' => $orderData['item_list'] ?? []]
        );

        if ($order->wasRecentlyCreated) {
            $order->stock_sync_status = 'PENDING';
        } elseif ($order->stock_sync_status === 'PROCESSED' && $newStatus === 'CANCELLED') {
            $order->stock_sync_status = 'PENDING';
        }

        $order->save();
    }

    private function makeApiCall(string $path, string $method = 'GET', array $queryParams = []): array
    {
        $shopConnection = ShopeeShop::firstOrFail();
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

        $params = array_merge($baseParams, $queryParams);
        $params['sign'] = $this->generateApiSignature($path, $timestamp, $shopConnection->access_token, (int)$shopConnection->shop_id);
        $fullUrl = $this->apiBaseUrl . $path;

        $response = Http::get($fullUrl, $params);

        if ($response->successful() && empty($response->json('error'))) {
            return $response->json();
        }

        Log::error('SYNC-SHOPEE: Panggilan API gagal.', ['url' => $fullUrl, 'params' => $params, 'response' => $response->body()]);
        throw new ShopeeApiException('Gagal mengambil data dari API Shopee: ' . $response->json('message', 'Unknown error'));
    }
}