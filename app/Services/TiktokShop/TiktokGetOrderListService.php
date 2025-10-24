<?php

namespace App\Services\TiktokShop;

use App\Exceptions\TiktokApiException;
use App\Http\Controllers\TiktokShop\TiktokApiTrait;
use App\Models\EcommerceOrder;
use App\Models\EcommerceSetting;
use App\Models\TiktokShop;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TiktokGetOrderListService
{
    use TiktokApiTrait;

    public function syncOrdersSinceLastUpdate(): void
    {
        $lastSyncSetting = EcommerceSetting::find('tiktok_last_sync');
        // <-- PERUBAHAN LOGIKA DI SINI
        // Cek apakah sinkronisasi sudah berjalan dalam 3 hari terakhir.
        if ($lastSyncSetting && Carbon::parse($lastSyncSetting->value)->greaterThan(Carbon::now()->subDays(3))) {
            Log::info('SYNC-TIKTOK: Sinkronisasi sudah dijalankan dalam 3 hari terakhir. Proses dilewati.');
            return;
        }

        Log::info("SYNC-TIKTOK (Full Sync): Menjalankan sinkronisasi penuh karena belum ada sinkronisasi dalam 3 hari terakhir.");
        
        $endDate = Carbon::now();
        $currentStartDate = Carbon::now()->subYear();
        
        Log::info("SYNC-TIKTOK: Memulai pengambilan pesanan dari {$currentStartDate->toDateString()} hingga {$endDate->toDateString()}.");

        $allOrders = [];
        while($currentStartDate->lessThan($endDate)) {
            $currentEndDate = $currentStartDate->copy()->addDays(89);
            if ($currentEndDate->greaterThan($endDate)) {
                $currentEndDate = $endDate;
            }

            Log::info("SYNC-TIKTOK: Mengambil potongan data antara {$currentStartDate->toDateTimeString()} dan {$currentEndDate->toDateTimeString()}.");

            $ordersInChunk = $this->fetchAllUpdatedOrders($currentStartDate->unix(), $currentEndDate->unix());
            if (!empty($ordersInChunk)) {
                $allOrders = array_merge($allOrders, $ordersInChunk);
            }

            $currentStartDate = $currentEndDate->copy()->addSecond();
        }

        if (empty($allOrders)) {
            Log::info('SYNC-TIKTOK: Tidak ada pesanan baru atau terupdate yang ditemukan dalam rentang waktu penuh.');
        } else {
             Log::info("SYNC-TIKTOK: Ditemukan " . count($allOrders) . " pesanan untuk diproses.");
            foreach ($allOrders as $orderData) {
                $this->saveOrUpdateOrder($orderData);
            }
        }
        
        EcommerceSetting::updateOrCreate(
            ['key' => 'tiktok_last_sync'],
            ['value' => Carbon::now()->toDateTimeString()]
        );
        Log::info('SYNC-TIKTOK: Sinkronisasi selesai. Waktu sync terakhir telah diperbarui ke hari ini.');
    }

    private function fetchAllUpdatedOrders(int $timeFrom, int $timeTo): array
    {
        $allOrders = [];
        $pageToken = null;

        do {
            $queryParams = [
                'page_size' => 100,
                'update_time_from' => $timeFrom,
                'update_time_to' => $timeTo,
            ];

            if ($pageToken) {
                $queryParams['page_token'] = $pageToken;
            }
            
            $response = $this->getOrderListPage($queryParams);

            if (!empty($response['orders'])) {
                $allOrders = array_merge($allOrders, $response['orders']);
            }

            $pageToken = $response['next_page_token'] ?? null;

        } while ($pageToken && !empty($response['orders']));

        return $allOrders;
    }

    private function saveOrUpdateOrder(array $orderData): void
    {
        $orderId = $orderData['id'];
        $newStatus = $orderData['status'];

        $order = EcommerceOrder::updateOrCreate(
            ['platform' => 'tiktok', 'platform_order_id' => $orderId],
            ['platform_status' => $newStatus, 'line_items' => $orderData['line_items'] ?? []]
        );

        if ($order->wasRecentlyCreated) {
            $order->stock_sync_status = 'PENDING';
        } elseif ($order->stock_sync_status === 'PROCESSED' && $newStatus === 'CANCEL') {
            $order->stock_sync_status = 'PENDING';
        }

        $order->save();
    }

    public function getOrderListPage(array $queryParams = []): ?array
    {
        $shopConnection = TiktokShop::firstOrFail();
        $this->initializeTiktokApi();
        if ($shopConnection->access_token_expires_at->isPast()) {
            $shopConnection = $this->refreshToken($shopConnection);
        }

        $shopCipher = $this->getShopCipher($shopConnection);
        $path = '/order/202309/orders/search';
        $timestamp = time();
        $baseParams = [
            'app_key'     => $this->appKey,
            'timestamp'   => $timestamp,
            'shop_cipher' => $shopCipher,
        ];

        $params = array_merge($baseParams, $queryParams);
        $bodyJsonString = json_encode([]);
        $params['sign'] = $this->generateSignature($path, $params, $bodyJsonString);
        $fullUrl = $this->apiBaseUrl . $path . '?' . http_build_query($params);
        
        $response = Http::withHeaders([
            'x-tts-access-token' => $shopConnection->access_token,
            'content-type'       => 'application/json',
        ])->post($fullUrl, []);

        if ($response->successful() && $response->json('code') === 0) {
            return $response->json('data');
        }
        
        Log::warning("SYNC-TIKTOK: Gagal mengambil halaman daftar pesanan", ['request_url' => $fullUrl, 'response_body' => $response->body()]);
        throw new TiktokApiException('Gagal mengambil halaman daftar pesanan dari TikTok API: ' . $response->json('message'));
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