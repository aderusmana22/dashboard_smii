<?php

namespace App\Services\TiktokShop;

use App\Exceptions\TiktokApiException;
use App\Http\Controllers\TiktokShop\TiktokApiTrait;
use App\Models\EcommerceOrder;
use App\Models\EcommerceSetting;
use App\Models\TiktokpedOrder;
use App\Models\TiktokpedOrderItem;
use App\Models\TiktokShop;
use App\Services\TiktokShop\TiktokGetOrderDetailService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class TiktokGetOrderListService
{
    use TiktokApiTrait;

    protected $orderDetailService;

    public function __construct(TiktokGetOrderDetailService $orderDetailService)
    {
        $this->orderDetailService = $orderDetailService;
    }

    // ... (Metode syncOrdersSinceLastUpdate dan orkestrasinya tidak berubah) ...
    public function syncOrdersSinceLastUpdate(): void
    {
        $allOrdersFromList = $this->fetchAllOrdersInRange();
        if (empty($allOrdersFromList)) {
            Log::info('SYNC-TIKTOK: Tidak ada pesanan baru atau terupdate yang ditemukan.');
        } else {
             Log::info("SYNC-TIKTOK: Ditemukan " . count($allOrdersFromList) . " pesanan untuk diproses.");
            foreach ($allOrdersFromList as $orderSummary) {
                $orderId = data_get($orderSummary, 'id');
                if (!$orderId) {
                    continue;
                }
                try {
                    Log::info("SYNC-TIKTOK: Mengambil detail untuk pesanan ID: {$orderId}");
                    $fullOrderDetails = $this->orderDetailService->getOrderDetail($orderId);
                    if ($fullOrderDetails) {
                        $this->saveToPlatformAndUnifiedTables($fullOrderDetails);
                    } else {
                        Log::warning("SYNC-TIKTOK: Tidak ditemukan detail untuk pesanan ID: {$orderId}");
                    }
                } catch (\Exception $e) {
                    Log::error("SYNC-TIKTOK: Gagal memproses pesanan ID: {$orderId}", [
                        'error' => $e->getMessage()
                    ]);
                }
            }
        }
        EcommerceSetting::updateOrCreate(
            ['key' => 'tiktok_last_sync'],
            ['value' => Carbon::now()->toDateTimeString()]
        );
        Log::info('SYNC-TIKTOK: Sinkronisasi selesai. Waktu sync terakhir telah diperbarui.');
    }

    private function saveToPlatformAndUnifiedTables(array $orderData): void
    {
        DB::transaction(function () use ($orderData) {
            $tiktokOrder = $this->saveToTiktokTables($orderData);
            $this->saveToEcommerceOrderTable($tiktokOrder, $orderData);
        });
    }

    private function saveToTiktokTables(array $orderData): TiktokpedOrder
    {
        // =================================================================
        // <-- KODE YANG DIPERBAIKI ADA DI SINI -->
        // Menambahkan field-field yang wajib diisi oleh tabel tiktokped_orders.
        $tiktokOrder = TiktokpedOrder::updateOrCreate(
            ['tiktok_order_id' => data_get($orderData, 'id')],
            [
                'status' => data_get($orderData, 'status'),
                'total_amount' => data_get($orderData, 'payment.total_amount', 0),
                'sub_total' => data_get($orderData, 'payment.sub_total', 0), // <-- DITAMBAHKAN
                'shipping_fee' => data_get($orderData, 'payment.shipping_fee', 0), // <-- DITAMBAHKAN
                'platform_discount' => data_get($orderData, 'payment.platform_discount', 0), // <-- DITAMBAHKAN
                'payment_method' => data_get($orderData, 'payment.payment_method_name'), // <-- Diubah ke payment_method_name
                'shipping_provider' => data_get($orderData, 'shipping_provider'),
                'tracking_number' => data_get($orderData, 'tracking_number'),
                'recipient_name' => data_get($orderData, 'recipient_address.name'),
                'recipient_phone' => data_get($orderData, 'recipient_address.phone_number'),
                'recipient_full_address' => data_get($orderData, 'recipient_address.full_address'),
                'created_at_tiktok' => Carbon::createFromTimestamp(data_get($orderData, 'create_time')),
                'paid_at' => data_get($orderData, 'paid_time') ? Carbon::createFromTimestamp(data_get($orderData, 'paid_time')) : null,
                'raw_data' => json_encode($orderData),
            ]
        );
        // =================================================================

        $tiktokOrder->items()->delete();

        $items = data_get($orderData, 'line_items', []);
        foreach ($items as $item) {
            TiktokpedOrderItem::create([
                'tiktokped_order_id' => $tiktokOrder->id,
                'line_item_id' => data_get($item, 'id'),
                'product_id' => data_get($item, 'product_id'),
                'product_name' => data_get($item, 'product_name'),
                'sku_id' => data_get($item, 'sku_id'),
                'sku_name' => data_get($item, 'sku_name'),
                'seller_sku' => data_get($item, 'seller_sku'),
                'quantity' => data_get($item, 'quantity', 1),
                'sale_price' => data_get($item, 'sale_price'),
                'sku_image' => data_get($item, 'sku_image'),
            ]);
        }

        return $tiktokOrder;
    }

    private function saveToEcommerceOrderTable(TiktokpedOrder $tiktokOrder, array $originalApiData): void
    {
        $order = EcommerceOrder::updateOrCreate(
            ['platform' => 'tiktok', 'platform_order_id' => $tiktokOrder->tiktok_order_id],
            [
                'platform_status' => $tiktokOrder->status,
                'recipient_name' => $tiktokOrder->recipient_name,
                'total_amount' => $tiktokOrder->total_amount,
                'currency' => data_get($originalApiData, 'payment.currency', 'IDR'),
                'order_created_at' => $tiktokOrder->created_at_tiktok,
                'line_items' => data_get($originalApiData, 'line_items', []),
                'raw_data' => $tiktokOrder->raw_data
            ]
        );

        if ($order->wasRecentlyCreated) {
            $order->stock_sync_status = 'PENDING';
        } elseif ($order->stock_sync_status === 'PROCESSED' && $tiktokOrder->status === 'CANCEL') {
            $order->stock_sync_status = 'PENDING';
        }

        $order->save();
    }

    // ... (Semua metode lain untuk mengambil data dari API tetap sama) ...
    private function fetchAllOrdersInRange(): array
    {
        $maxPullDays = 30;
        $bufferDays = 1;
        $lastSyncSetting = EcommerceSetting::where('key', 'tiktok_last_sync')->first();
        $endDate = Carbon::now();
        $currentStartDate = null;
        if (!$lastSyncSetting) {
            Log::info("SYNC-TIKTOK: Sinkronisasi pertama kali. Menarik data {$maxPullDays} hari terakhir.");
            $currentStartDate = Carbon::now()->subDays($maxPullDays);
        } else {
            $lastSyncDate = Carbon::parse($lastSyncSetting->value);
            $gapInDays = $lastSyncDate->diffInDays($endDate);
            if ($gapInDays > $maxPullDays) {
                Log::warning("SYNC-TIKTOK: Jeda sinkronisasi {$gapInDays} hari (terlalu lama). Membatasi penarikan ke {$maxPullDays} hari terakhir untuk mencegah timeout.");
                $currentStartDate = Carbon::now()->subDays($maxPullDays);
            } else {
                Log::info("SYNC-TIKTOK: Jeda sinkronisasi {$gapInDays} hari. Menarik data sejak sinkronisasi terakhir dengan buffer {$bufferDays} hari.");
                $currentStartDate = $lastSyncDate->subDays($bufferDays);
            }
        }
        Log::info("SYNC-TIKTOK: Memulai pengambilan pesanan dari {$currentStartDate->toDateTimeString()} hingga {$endDate->toDateTimeString()}.");
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
        return $allOrders;
    }
    private function fetchAllUpdatedOrders(int $timeFrom, int $timeTo): array
    {
        $allOrders = [];
        $pageToken = null;
        do {
            $queryParams = ['page_size' => 100, 'update_time_from' => $timeFrom, 'update_time_to' => $timeTo];
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
        $baseParams = ['app_key' => $this->appKey, 'timestamp' => $timestamp, 'shop_cipher' => $shopCipher];
        $params = array_merge($baseParams, $queryParams);
        $bodyJsonString = json_encode([]);
        $params['sign'] = $this->generateSignature($path, $params, $bodyJsonString);
        $fullUrl = $this->apiBaseUrl . $path . '?' . http_build_query($params);
        $response = Http::withHeaders(['x-tts-access-token' => $shopConnection->access_token, 'content-type' => 'application/json'])->post($fullUrl, []);
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
        $response = Http::withHeaders(['x-tts-access-token' => $shopConnection->access_token])->get($this->apiBaseUrl . $path, $params);
        if ($response->successful() && $response->json('code') === 0 && !empty($response->json('data.shops'))) {
            return $response->json('data.shops')[0]['cipher'];
        }
        throw new TiktokApiException('Gagal mendapatkan informasi toko (shop_cipher). Response: ' . $response->body());
    }
}