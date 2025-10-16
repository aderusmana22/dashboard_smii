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

    /**
     * Tugas utama: Mengambil pesanan yang diperbarui sejak sync terakhir dan menyimpannya ke DB.
     */
    public function syncOrdersSinceLastUpdate(): void
    {
        // Langkah 1: Dapatkan daftar ID pesanan (order_sn) yang telah diperbarui.
        $orderSnList = $this->fetchUpdatedOrderSnList();

        if (empty($orderSnList)) {
            Log::info('SYNC-SHOPEE: Tidak ada pesanan baru atau terupdate yang ditemukan.');
            return;
        }

        // Langkah 2: Dapatkan detail lengkap untuk ID pesanan tersebut.
        $allOrderDetails = $this->fetchOrderDetailsInBatches($orderSnList);

        Log::info("SYNC-SHOPEE: Ditemukan " . count($allOrderDetails) . " detail pesanan untuk diproses.");
        foreach ($allOrderDetails as $orderData) {
            $this->saveOrUpdateOrder($orderData);
        }
    }

    /**
     * HANYA mengambil daftar order_sn dari endpoint get_order_list berdasarkan waktu update.
     */
    private function fetchUpdatedOrderSnList(): array
    {
        $lastSyncSetting = EcommerceSetting::find('shopee_orders_last_sync');
        $timeFrom = $lastSyncSetting ? Carbon::parse($lastSyncSetting->value)->unix() : Carbon::now()->subDays(3)->unix();
        $timeTo = Carbon::now()->unix();

        Log::info("SYNC-SHOPEE (Step 1): Mengambil daftar order_sn yang diupdate antara " . date('Y-m-d H:i:s', $timeFrom) . " dan " . date('Y-m-d H:i:s', $timeTo));

        $allOrderSn = [];
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
                // Ambil hanya kolom 'order_sn'
                $orderSnFromPage = array_column($response['response']['order_list'], 'order_sn');
                $allOrderSn = array_merge($allOrderSn, $orderSnFromPage);
            }

            $cursor = $response['response']['next_cursor'] ?? '';
            $more = $response['response']['more'] ?? false;

        } while ($more && !empty($cursor));

        return array_unique($allOrderSn);
    }

    /**
     * Mengambil detail lengkap pesanan dari endpoint get_order_detail dalam batch.
     */
    private function fetchOrderDetailsInBatches(array $orderSnList): array
    {
        $allOrderDetails = [];
        // API Shopee merekomendasikan batch maksimal 50
        $orderSnChunks = array_chunk($orderSnList, 50);

        Log::info('SYNC-SHOPEE (Step 2): Mengambil detail untuk ' . count($orderSnList) . ' pesanan dalam ' . count($orderSnChunks) . ' batch.');

        foreach ($orderSnChunks as $index => $chunk) {
            $params = [
                'order_sn_list' => implode(',', $chunk),
                // Di sini kita BISA dan HARUS meminta item_list
                'response_optional_fields' => 'item_list,order_status,order_sn',
            ];

            $response = $this->makeApiCall('/api/v2/order/get_order_detail', 'GET', $params);

            if (!empty($response['response']['order_list'])) {
                $allOrderDetails = array_merge($allOrderDetails, $response['response']['order_list']);
            }
        }

        return $allOrderDetails;
    }

    /**
     * Menyimpan atau memperbarui pesanan di tabel ecommerce_orders dan menandainya untuk diproses.
     */
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

    /**
     * Fungsi pembungkus untuk melakukan panggilan API dengan otentikasi.
     */
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