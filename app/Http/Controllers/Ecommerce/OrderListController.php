<?php

namespace App\Http\Controllers\Ecommerce;

use App\Http\Controllers\Controller;
use App\Services\TiktokShop\TiktokGetOrderListService;
use App\Services\StockSynchronizationService; // <-- WAJIB DITAMBAHKAN
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;

class OrderListController extends Controller
{
    protected $orderService;
    protected $stockSyncService; // <-- WAJIB DITAMBAHKAN

    /**
     * Inject kedua service yang dibutuhkan untuk siklus penuh.
     */
    public function __construct(
        TiktokGetOrderListService $orderService,
        StockSynchronizationService $stockSyncService // <-- WAJIB DITAMBAHKAN
    ) {
        $this->orderService = $orderService;
        $this->stockSyncService = $stockSyncService;
    }

    /**
     * Menjalankan siklus sinkronisasi penuh (ambil pesanan & proses stok) secara manual.
     */
    public function syncOrders(): RedirectResponse
    {
        try {
            Log::info('MANUAL-SYNC-TIKTOK: Sinkronisasi manual dipicu dari controller.');

            // LANGKAH 1: Panggil method baru untuk mengambil pesanan terupdate.
            // Method ini akan mengambil data dari API dan menyimpannya ke tabel 'ecommerce_orders'.
            $this->orderService->syncOrdersSinceLastUpdate();

            // LANGKAH 2: Panggil service sinkronisasi stok untuk memproses pesanan 'PENDING'.
            // Ini adalah langkah krusial yang memastikan stok benar-benar diupdate.
            $this->stockSyncService->processPendingOrders();

            // Jika semua berhasil, kembalikan pesan sukses.
            return redirect()->back()->with('success', "Sinkronisasi TikTok berhasil dijalankan. Pesanan baru dan stok sedang diproses.");

        } catch (\Exception $e) {
            // Jika terjadi error di langkah mana pun, catat dan kembalikan pesan error.
            Log::error('MANUAL-SYNC-TIKTOK: Terjadi kesalahan saat sinkronisasi manual.', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()->with('error', "Gagal melakukan sinkronisasi TikTok: " . $e->getMessage());
        }
    }
}