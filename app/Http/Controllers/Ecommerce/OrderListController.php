<?php

namespace App\Http\Controllers\Ecommerce;

use App\Exceptions\TiktokApiException;
use App\Http\Controllers\Controller;
use App\Models\EcommerceSetting;
use App\Models\Order;
use App\Services\TiktokShop\TiktokGetOrderListService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;

class OrderListController extends Controller
{
    protected $orderService;

    public function __construct(TiktokGetOrderListService $orderService)
    {
        $this->orderService = $orderService;
    }

    public function index(): JsonResponse
    {
        $orders = Order::latest('created_at_tiktok')->get();
        return response()->json(['data' => $orders]);
    }

    public function syncOrders(): RedirectResponse
    {
        try {
            Log::info('SYNC-TIKTOK: Controller syncOrders dipanggil.');
            $allOrdersFromApi = $this->orderService->fetchAllOrders();
            Log::info('SYNC-TIKTOK: Controller menerima total ' . count($allOrdersFromApi) . ' pesanan dari service.');

            if (empty($allOrdersFromApi)) {
                Log::warning('SYNC-TIKTOK: Tidak ada pesanan yang diterima dari service, proses penyimpanan dilewati.');
                EcommerceSetting::updateOrCreate(
                    ['key' => 'tiktok_last_sync'],
                    ['value' => now()->toDateTimeString()]
                );
                return redirect()->back()->with('success', "Sinkronisasi selesai. Tidak ada pesanan baru yang ditemukan.");
            }

            $syncedCount = 0;
            foreach ($allOrdersFromApi as $orderData) {
                Order::updateOrCreate(
                    ['tiktok_order_id' => $orderData['id']],
                    [
                        // PERBAIKAN: Menambahkan pengecekan untuk data yang mungkin tidak ada
                        'status' => $orderData['status'] ?? 'UNKNOWN',
                        
                        // Cek jika array 'payment' ada sebelum mengakses isinya
                        'total_amount' => $orderData['payment']['total_amount'] ?? 0,
                        'sub_total' => $orderData['payment']['sub_total'] ?? 0,
                        'shipping_fee' => $orderData['payment']['shipping_fee'] ?? 0,
                        'platform_discount' => $orderData['payment']['platform_discount'] ?? 0,
                        
                        'payment_method' => $orderData['payment_method_name'] ?? null,
                        'shipping_provider' => $orderData['shipping_provider'] ?? null,
                        'tracking_number' => $orderData['tracking_number'] ?? null,
                        
                        // Cek jika array 'recipient_address' ada sebelum mengakses isinya
                        'recipient_name' => $orderData['recipient_address']['name'] ?? null,
                        'recipient_phone' => $orderData['recipient_address']['phone_number'] ?? null,
                        'recipient_full_address' => $orderData['recipient_address']['full_address'] ?? null,
                        
                        // INI PERBAIKAN UTAMA: Cek 'paid_time' sebelum digunakan
                        'paid_at' => isset($orderData['paid_time']) ? Carbon::createFromTimestamp($orderData['paid_time']) : null,
                        
                        'created_at_tiktok' => isset($orderData['create_time']) ? Carbon::createFromTimestamp($orderData['create_time']) : now(),
                        'raw_data' => json_encode($orderData),
                    ]
                );
                $syncedCount++;
            }

            EcommerceSetting::updateOrCreate(
                ['key' => 'tiktok_last_sync'],
                ['value' => now()->toDateTimeString()]
            );

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