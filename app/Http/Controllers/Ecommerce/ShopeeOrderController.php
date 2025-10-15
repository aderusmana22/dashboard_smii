<?php

namespace App\Http\Controllers\Ecommerce;

use App\Exceptions\ShopeeApiException;
use App\Http\Controllers\Controller;
use App\Models\EcommerceSetting;
use App\Models\ShopeeOrder;
use App\Models\ShopeeOrderItem;
use App\Services\Shopee\ShopeeGetOrderListService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ShopeeOrderController extends Controller
{
    public function __construct(protected ShopeeGetOrderListService $orderService)
    {
    }

    public function syncOrders(): RedirectResponse
    {
        try {
            Log::info('SYNC-SHOPEE: Controller syncOrders dipanggil.');
            $allOrdersFromApi = $this->orderService->fetchAllOrders();
            Log::info('SYNC-SHOPEE: Controller menerima total ' . count($allOrdersFromApi) . ' pesanan dari service.');

            if (empty($allOrdersFromApi)) {
                EcommerceSetting::updateOrCreate(['key' => 'shopee_orders_last_sync'], ['value' => now()]);
                return redirect()->back()->with('success', "Sinkronisasi Shopee selesai. Tidak ada pesanan baru ditemukan dalam 15 hari terakhir.");
            }

            $syncedCount = 0;

            DB::transaction(function () use ($allOrdersFromApi, &$syncedCount) {
                foreach ($allOrdersFromApi as $orderData) {
                    $recipient = $orderData['recipient_address'] ?? [];
                    
                    $order = ShopeeOrder::updateOrCreate(
                        ['order_sn' => $orderData['order_sn']],
                        [
                            'order_status' => $orderData['order_status'],
                            'region' => $orderData['region'] ?? null,
                            'currency' => $orderData['currency'] ?? null,
                            'cod' => $orderData['cod'] ?? false,
                            'total_amount' => $orderData['total_amount'] ?? 0,
                            'estimated_shipping_fee' => $orderData['estimated_shipping_fee'] ?? null,
                            'actual_shipping_fee' => $orderData['actual_shipping_fee'] ?? null,
                            'payment_method' => $orderData['payment_method'] ?? null,
                            'shipping_carrier' => $orderData['shipping_carrier'] ?? null,
                            'recipient_name' => $recipient['name'] ?? null,
                            'recipient_phone' => $recipient['phone'] ?? null,
                            'recipient_full_address' => $recipient['full_address'] ?? null,
                            
                            // === PERUBAHAN UTAMA DI SINI ===
                            // Menyimpan data baru ke kolom yang sudah dibuat
                            'buyer_user_id' => $orderData['buyer_user_id'] ?? null,
                            'buyer_username' => $orderData['buyer_username'] ?? null,
                            // =================================
                            
                            'pay_time' => !empty($orderData['pay_time']) ? Carbon::createFromTimestamp($orderData['pay_time']) : null,
                            'ship_by_date' => !empty($orderData['ship_by_date']) ? Carbon::createFromTimestamp($orderData['ship_by_date']) : null,
                            'create_time_shopee' => !empty($orderData['create_time']) ? Carbon::createFromTimestamp($orderData['create_time']) : null,
                            
                            'raw_data' => json_encode($orderData),
                        ]
                    );

                    if (isset($orderData['item_list']) && is_array($orderData['item_list'])) {
                        foreach ($orderData['item_list'] as $itemData) {
                            ShopeeOrderItem::updateOrCreate(
                                [
                                    'shopee_order_id' => $order->id,
                                    'order_item_id' => $itemData['order_item_id']
                                ],
                                [
                                    'item_id' => $itemData['item_id'],
                                    'item_name' => $itemData['item_name'],
                                    'item_sku' => $itemData['item_sku'] ?? null,
                                    'model_id' => $itemData['model_id'],
                                    'model_name' => $itemData['model_name'] ?? null,
                                    'model_sku' => $itemData['model_sku'] ?? null,
                                    'model_quantity_purchased' => $itemData['model_quantity_purchased'],
                                    'model_original_price' => $itemData['model_original_price'],
                                    'model_discounted_price' => $itemData['model_discounted_price'],
                                    'image_url' => $itemData['image_info']['image_url'] ?? null,
                                ]
                            );
                        }
                    }
                    $syncedCount++;
                }
            });

            EcommerceSetting::updateOrCreate(['key' => 'shopee_orders_last_sync'], ['value' => now()]);

            Log::info("SYNC-SHOPEE: Proses penyimpanan ke DB selesai. {$syncedCount} pesanan telah diperbarui.");
            return redirect()->back()->with('success', "Sinkronisasi Shopee berhasil! {$syncedCount} pesanan telah diperbarui.");

        } catch (ShopeeApiException $e) {
            Log::error('SYNC-SHOPEE: Terjadi ShopeeApiException.', ['message' => $e->getMessage()]);
            return redirect()->back()->with('error', 'Gagal sinkronisasi dengan API Shopee: ' . $e->getMessage());
        } catch (\Exception $e) {
            Log::error('SYNC-SHOPEE: Terjadi kesalahan umum.', ['message' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return redirect()->back()->with('error', 'Terjadi kesalahan internal saat sinkronisasi Shopee.');
        }
    }
}