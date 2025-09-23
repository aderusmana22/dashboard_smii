<?php

namespace App\Http\Controllers\Ecommerce;

use App\Exceptions\TiktokApiException;
use App\Http\Controllers\Controller;
use App\Models\EcommerceSetting;
use App\Models\TiktokpedOrder;      // Ganti model
use App\Models\TiktokpedOrderItem;   // Tambahkan model item
use App\Services\TiktokShop\TiktokGetOrderListService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;     // Tambahkan DB facade
use Illuminate\Support\Facades\Log;

class OrderListController extends Controller
{
    protected $orderService;

    public function __construct(TiktokGetOrderListService $orderService)
    {
        $this->orderService = $orderService;
    }

    public function syncOrders(): RedirectResponse
    {
        try {
            Log::info('SYNC-TIKTOK: Controller syncOrders dipanggil.');
            $allOrdersFromApi = $this->orderService->fetchAllOrders();
            Log::info('SYNC-TIKTOK: Controller menerima total ' . count($allOrdersFromApi) . ' pesanan dari service.');

            if (empty($allOrdersFromApi)) {
                Log::warning('SYNC-TIKTOK: Tidak ada pesanan yang diterima dari service.');
                EcommerceSetting::updateOrCreate(['key' => 'tiktok_last_sync'], ['value' => now()->toDateTimeString()]);
                return redirect()->back()->with('success', "Sinkronisasi selesai. Tidak ada pesanan baru yang ditemukan.");
            }

            $syncedCount = 0;
            
            // Gunakan transaksi database untuk memastikan integritas data
            DB::transaction(function () use ($allOrdersFromApi, &$syncedCount) {
                foreach ($allOrdersFromApi as $orderData) {
                    // 1. Simpan atau perbarui data pesanan utama
                    $order = TiktokpedOrder::updateOrCreate(
                        ['tiktok_order_id' => $orderData['id']],
                        [
                            'status' => $orderData['status'] ?? 'UNKNOWN',
                            'total_amount' => $orderData['payment']['total_amount'] ?? 0,
                            'sub_total' => $orderData['payment']['sub_total'] ?? 0,
                            'shipping_fee' => $orderData['payment']['shipping_fee'] ?? 0,
                            'platform_discount' => $orderData['payment']['platform_discount'] ?? 0,
                            'payment_method' => $orderData['payment_method_name'] ?? null,
                            'shipping_provider' => $orderData['shipping_provider'] ?? null,
                            'tracking_number' => $orderData['tracking_number'] ?? null,
                            'recipient_name' => $orderData['recipient_address']['name'] ?? null,
                            'recipient_phone' => $orderData['recipient_address']['phone_number'] ?? null,
                            'recipient_full_address' => $orderData['recipient_address']['full_address'] ?? null,
                            'paid_at' => isset($orderData['paid_time']) ? Carbon::createFromTimestamp($orderData['paid_time']) : null,
                            'created_at_tiktok' => isset($orderData['create_time']) ? Carbon::createFromTimestamp($orderData['create_time']) : now(),
                            'raw_data' => json_encode($orderData),
                        ]
                    );

                    // 2. Simpan atau perbarui setiap item produk dalam pesanan
                    if (isset($orderData['line_items']) && is_array($orderData['line_items'])) {
                        foreach ($orderData['line_items'] as $itemData) {
                            TiktokpedOrderItem::updateOrCreate(
                                ['line_item_id' => $itemData['id']], // Kunci unik untuk item
                                [
                                    'tiktokped_order_id' => $order->id, // Hubungkan dengan ID pesanan
                                    'product_id' => $itemData['product_id'],
                                    'product_name' => $itemData['product_name'],
                                    'sku_id' => $itemData['sku_id'],
                                    'sku_name' => $itemData['sku_name'],
                                    'seller_sku' => $itemData['seller_sku'] ?? null,
                                    'sku_image' => $itemData['sku_image'] ?? null,
                                    'quantity' => 1, // Asumsi setiap line item adalah 1 kuantitas
                                    'sale_price' => $itemData['sale_price'],
                                ]
                            );
                        }
                    }
                    $syncedCount++;
                }
            });

            EcommerceSetting::updateOrCreate(['key' => 'tiktok_last_sync'], ['value' => now()->toDateTimeString()]);

            Log::info("SYNC-TIKTOK: Proses penyimpanan ke DB selesai. {$syncedCount} pesanan telah diperbarui.");
            return redirect()->back()->with('success', "Sinkronisasi berhasil! {$syncedCount} pesanan telah diperbarui.");

        } catch (TiktokApiException $e) {
            Log::error('SYNC-TIKTOK: Terjadi TiktokApiException.', ['message' => $e->getMessage()]);
            return redirect()->back()->with('error', 'Gagal sinkronisasi dengan TikTok API: ' . $e->getMessage());
        } catch (\Exception $e) {
            Log::error('SYNC-TIKTOK: Terjadi kesalahan umum.', ['message' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return redirect()->back()->with('error', 'Terjadi kesalahan internal saat sinkronisasi.');
        }
    }
}