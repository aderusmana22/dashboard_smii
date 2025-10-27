<?php

namespace App\Services\Shopee;

use App\Exceptions\ShopeeApiException;
use App\Http\Controllers\Shopee\ShopeeApiTrait;
use App\Models\EcommerceOrder;
use App\Models\EcommerceSetting;
use App\Models\ShopeeOrder;
use App\Models\ShopeeOrderItem;
use App\Models\ShopeeShop;
use App\Services\Shopee\ShopeeGetOrderDetailService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class ShopeeGetOrderListService
{
    use ShopeeApiTrait;

    protected $orderDetailService;

    public function __construct(ShopeeGetOrderDetailService $orderDetailService)
    {
        $this->orderDetailService = $orderDetailService;
    }

    public function syncOrdersSinceLastUpdate(): void
    {
        $orderSnList = $this->fetchUpdatedOrderSnList();
        if (empty($orderSnList)) {
            Log::info("SYNC-SHOPEE: Tidak ada pesanan baru untuk disinkronkan.");
        } else {
            Log::info("SYNC-SHOPEE: Ditemukan " . count($orderSnList) . " pesanan untuk diproses.");
            foreach ($orderSnList as $orderSn) {
                try {
                    Log::info("SYNC-SHOPEE: Mengambil detail untuk pesanan SN: {$orderSn}");
                    $fullOrderDetails = $this->orderDetailService->getOrderDetail($orderSn);
                    if ($fullOrderDetails) {
                        $this->saveToPlatformAndUnifiedTables($fullOrderDetails);
                    } else {
                        Log::warning("SYNC-SHOPEE: Tidak ditemukan detail untuk pesanan SN: {$orderSn}");
                    }
                } catch (\Exception $e) {
                    Log::error("SYNC-SHOPEE: Gagal memproses pesanan SN: {$orderSn}", [
                        'error' => $e->getMessage()
                    ]);
                }
            }
        }
        EcommerceSetting::updateOrCreate(
            ['key' => 'shopee_orders_last_sync'],
            ['value' => Carbon::now()->toDateTimeString()]
        );
        Log::info('SYNC-SHOPEE: Sinkronisasi selesai. Waktu sync terakhir telah diperbarui.');
    }

    private function saveToPlatformAndUnifiedTables(array $orderData): void
    {
        DB::transaction(function () use ($orderData) {
            $shopeeOrder = $this->saveToShopeeTables($orderData);
            $this->saveToEcommerceOrderTable($shopeeOrder, $orderData);
        });
    }

    private function saveToShopeeTables(array $orderData): ShopeeOrder
    {
        $shopeeOrder = ShopeeOrder::updateOrCreate(
            ['order_sn' => data_get($orderData, 'order_sn')],
            [
                'order_status' => data_get($orderData, 'order_status'),
                'total_amount' => data_get($orderData, 'total_amount', 0),
                'payment_method' => data_get($orderData, 'payment_method'),
                'shipping_carrier' => data_get($orderData, 'shipping_carrier'),
                'recipient_name' => data_get($orderData, 'recipient_address.name'),
                'recipient_phone' => data_get($orderData, 'recipient_address.phone'),
                'recipient_full_address' => data_get($orderData, 'recipient_address.full_address'),
                'create_time_shopee' => Carbon::createFromTimestamp(data_get($orderData, 'create_time')),
                'pay_time' => data_get($orderData, 'pay_time') ? Carbon::createFromTimestamp(data_get($orderData, 'pay_time')) : null,
                'raw_data' => json_encode($orderData),
            ]
        );

        $shopeeOrder->items()->delete();

        $items = data_get($orderData, 'item_list', []);
        foreach ($items as $item) {
            // =================================================================
            // <-- INI ADALAH PERBAIKAN UNTUK ERROR TERAKHIR ANDA -->
            $originalPrice = data_get($item, 'model_original_price', data_get($item, 'original_price'));
            $discountedPrice = data_get($item, 'model_discounted_price', data_get($item, 'discounted_price'));

            ShopeeOrderItem::create([
                'shopee_order_id' => $shopeeOrder->id,
                'order_item_id' => data_get($item, 'order_item_id'),
                'item_id' => data_get($item, 'item_id'),
                'item_name' => data_get($item, 'item_name'),
                'item_sku' => data_get($item, 'item_sku'),
                'model_id' => data_get($item, 'model_id', data_get($item, 'item_id')),
                'model_name' => data_get($item, 'model_name'),
                'model_sku' => data_get($item, 'model_sku', data_get($item, 'item_sku')),
                'model_quantity_purchased' => data_get($item, 'model_quantity_purchased'),
                'model_original_price' => $originalPrice ?? $discountedPrice ?? 0,
                'model_discounted_price' => $discountedPrice ?? 0,
                'image_url' => data_get($item, 'image_info.image_url'), // Path yang benar sesuai dokumentasi
            ]);
            // =================================================================
        }

        return $shopeeOrder;
    }

    private function saveToEcommerceOrderTable(ShopeeOrder $shopeeOrder, array $originalApiData): void
    {
        $order = EcommerceOrder::updateOrCreate(
            ['platform' => 'shopee', 'platform_order_id' => $shopeeOrder->order_sn],
            [
                'platform_status' => $shopeeOrder->order_status,
                'recipient_name' => $shopeeOrder->recipient_name,
                'total_amount' => $shopeeOrder->total_amount,
                'currency' => data_get($originalApiData, 'currency', 'IDR'),
                'order_created_at' => $shopeeOrder->create_time_shopee,
                'line_items' => data_get($originalApiData, 'item_list', []),
                'raw_data' => $shopeeOrder->raw_data
            ]
        );

        if ($order->wasRecentlyCreated) {
            $order->stock_sync_status = 'PENDING';
        } elseif ($order->stock_sync_status === 'PROCESSED' && $shopeeOrder->order_status === 'CANCELLED') {
            $order->stock_sync_status = 'PENDING';
        }

        $order->save();
    }

    private function fetchUpdatedOrderSnList(): array
    {
        $maxPullDays = 30;
        $bufferDays = 1;
        $lastSyncSetting = EcommerceSetting::where('key', 'shopee_orders_last_sync')->first();
        $endDate = Carbon::now();
        $currentStartDate = null;
        if (!$lastSyncSetting) {
            Log::info("SYNC-SHOPEE: Sinkronisasi pertama kali. Menarik data {$maxPullDays} hari terakhir.");
            $currentStartDate = Carbon::now()->subDays($maxPullDays);
        } else {
            $lastSyncDate = Carbon::parse($lastSyncSetting->value);
            $gapInDays = $lastSyncDate->diffInDays($endDate);
            if ($gapInDays > $maxPullDays) {
                Log::warning("SYNC-SHOPEE: Jeda sinkronisasi {$gapInDays} hari (terlalu lama). Membatasi penarikan ke {$maxPullDays} hari terakhir untuk mencegah timeout.");
                $currentStartDate = Carbon::now()->subDays($maxPullDays);
            } else {
                Log::info("SYNC-SHOPEE: Jeda sinkronisasi {$gapInDays} hari. Menarik data sejak sinkronisasi terakhir dengan buffer {$bufferDays} hari.");
                $currentStartDate = $lastSyncDate->subDays($bufferDays);
            }
        }
        Log::info("SYNC-SHOPEE (Step 1): Memulai pengambilan order_sn dari {$currentStartDate->toDateTimeString()} hingga {$endDate->toDateTimeString()}.");
        $allOrderSn = [];
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
                $params = ['time_range_field' => 'update_time', 'time_from' => $timeFrom, 'time_to' => $timeTo, 'page_size' => 100, 'cursor' => $cursor];
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
        Log::info("SYNC-SHOPEE (Step 1 Selesai): Total " . count(array_unique($allOrderSn)) . " order_sn unik ditemukan.");
        return array_unique($allOrderSn);
    }
    private function makeApiCall(string $path, string $method = 'GET', array $queryParams = []): array
    {
        $shopConnection = ShopeeShop::firstOrFail();
        $this->initializeShopeeApi();
        if ($shopConnection->access_token_expires_at->isPast()) {
            $shopConnection = $this->refreshToken($shopConnection);
        }
        $timestamp = time();
        $baseParams = ['partner_id' => (int)$this->partnerId, 'shop_id' => (int)$shopConnection->shop_id, 'access_token' => $shopConnection->access_token, 'timestamp' => $timestamp];
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