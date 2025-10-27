<?php

namespace App\Services;

use App\Models\EcommerceOrder;
use App\Models\MasterProduct;
use App\Services\Shopee\ShopeeUpdateInventoryService;
use App\Services\TiktokShop\TiktokUpdateInventoryService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class StockSynchronizationService
{
    protected $tiktokInventoryService;
    protected $shopeeInventoryService;

    public function __construct(TiktokUpdateInventoryService $tis, ShopeeUpdateInventoryService $sis)
    {
        $this->tiktokInventoryService = $tis;
        $this->shopeeInventoryService = $sis;
    }

    /**
     * Memproses semua pesanan yang ditandai 'PENDING' untuk sinkronisasi stok.
     */
    public function processPendingOrders(): void
    {
        // Mengambil pesanan PENDING dalam batch untuk efisiensi memori.
        EcommerceOrder::where('stock_sync_status', 'PENDING')
            ->chunkById(50, function ($pendingOrders) {
                if ($pendingOrders->isEmpty()) {
                    // Log ini hanya akan muncul jika tabel kosong, jadi jarang terjadi di dalam chunk.
                    // Log::info('SYNC-STOCK: Tidak ada pesanan yang perlu disinkronkan stoknya.');
                    return;
                }

                Log::info("SYNC-STOCK: Memproses batch berisi {$pendingOrders->count()} pesanan untuk sinkronisasi stok.");

                foreach ($pendingOrders as $order) {
                    // Status pesanan yang valid untuk MENGURANGI stok
                    $shopeeReduceStatuses = ['READY_TO_SHIP', 'PROCESSED', 'SHIPPED'];
                    $tiktokReduceStatuses = ['AWAITING_SHIPMENT', 'AWAITING_COLLECTION', 'IN_TRANSIT'];
                    
                    // Status pesanan yang valid untuk MENAMBAH (mengembalikan) stok
                    $increaseStatuses = ['CANCELLED', 'CANCEL'];

                    $action = null;
                    if (
                        ($order->platform === 'shopee' && in_array($order->platform_status, $shopeeReduceStatuses)) ||
                        ($order->platform === 'tiktok' && in_array($order->platform_status, $tiktokReduceStatuses))
                    ) {
                        $action = 'reduce';
                    } elseif (in_array($order->platform_status, $increaseStatuses)) {
                        $action = 'increase';
                    }

                    // Jika status pesanan tidak relevan (misal: COMPLETED, UNPAID), lewati.
                    if (!$action) {
                        $order->update(['stock_sync_status' => 'SKIPPED']);
                        Log::info("SYNC-STOCK: Melewati pesanan {$order->platform_order_id} dengan status '{$order->platform_status}'.");
                        continue;
                    }

                    try {
                        $this->adjustStockForOrder($order, $action);
                        $newStatus = ($action === 'reduce') ? 'PROCESSED' : 'REVERSED';
                        $order->update(['stock_sync_status' => $newStatus, 'processed_at' => now()]);
                        Log::info("SYNC-STOCK: Berhasil memproses pesanan {$order->platform_order_id}. Status sync: {$newStatus}");
                    } catch (\Exception $e) {
                        $order->update(['stock_sync_status' => 'FAILED']);
                        Log::error("SYNC-STOCK: Gagal memproses pesanan {$order->platform_order_id}: " . $e->getMessage(), [
                            'exception' => $e->getTraceAsString() // Sertakan trace untuk debug
                        ]);
                    }
                }
            });
    }

    /**
     * Melakukan penyesuaian stok untuk satu pesanan berdasarkan pencocokan ID produk.
     */
    private function adjustStockForOrder(EcommerceOrder $order, string $action): void
    {
        DB::transaction(function () use ($order, $action) {
            foreach ($order->line_items as $item) {
                $masterProduct = null;
                $quantity = 0;
                $platformProductId = null;

                // Langkah 1: Ekstrak ID Produk dan Kuantitas berdasarkan platform
                if ($order->platform === 'shopee') {
                    $platformProductId = data_get($item, 'item_id');
                    $quantity = data_get($item, 'model_quantity_purchased', 0);

                    if ($platformProductId) {
                        // Langkah 2: Cari MasterProduct yang terhubung dengan Shopee Product ID ini
                        $masterProduct = MasterProduct::whereHas('shopeeProduct', function ($query) use ($platformProductId) {
                            $query->where('shopee_item_id', $platformProductId);
                        })->first();
                    }

                } elseif ($order->platform === 'tiktok') {
                    $platformProductId = data_get($item, 'product_id');
                    $quantity = data_get($item, 'quantity', 1); // Asumsikan 1 jika tidak ada

                    if ($platformProductId) {
                        // Langkah 2: Cari MasterProduct yang terhubung dengan TikTok Product ID ini
                        $masterProduct = MasterProduct::whereHas('tiktokProduct', function ($query) use ($platformProductId) {
                            $query->where('tiktok_product_id', $platformProductId);
                        })->first();
                    }
                }

                // Langkah 3: Validasi sebelum melanjutkan
                if (!$masterProduct) {
                    $productIdentifier = $platformProductId ?? data_get($item, 'item_name', 'N/A');
                    Log::warning("SYNC-STOCK: MasterProduct tidak ditemukan untuk Platform Product ID: '{$productIdentifier}' pada pesanan {$order->platform_order_id}. Item dilewati.");
                    continue; // Lanjutkan ke item berikutnya dalam pesanan
                }

                if ($quantity <= 0) {
                    Log::warning("SYNC-STOCK: Kuantitas tidak valid (<= 0) untuk produk '{$masterProduct->title}' pada pesanan {$order->platform_order_id}. Item dilewati.");
                    continue;
                }

                // Langkah 4: Kunci, Hitung, dan Update Stok Master
                $masterProduct = MasterProduct::where('id', $masterProduct->id)->lockForUpdate()->first();
                $currentStock = $masterProduct->total_stock;
                $newMasterStock = ($action === 'reduce')
                    ? $currentStock - $quantity
                    : $currentStock + $quantity;

                $masterProduct->update(['total_stock' => $newMasterStock]);
                Log::info("SYNC-STOCK: Stok master untuk '{$masterProduct->title}' (ID: {$masterProduct->id}) diubah dari {$currentStock} menjadi {$newMasterStock} (Aksi: {$action}, Qty: {$quantity}).");

                // Langkah 5: Lakukan Sinkronisasi Silang ke Platform Lain
                if ($order->platform === 'tiktok' && $masterProduct->shopeeProduct) {
                    Log::info("SYNC-STOCK: Memicu update stok ke Shopee untuk produk '{$masterProduct->title}'.");
                    $this->shopeeInventoryService->updateInventory($masterProduct->shopeeProduct, $newMasterStock);
                } elseif ($order->platform === 'shopee' && $masterProduct->tiktokProduct) {
                    Log::info("SYNC-STOCK: Memicu update stok ke TikTok untuk produk '{$masterProduct->title}'.");
                    $this->tiktokInventoryService->updateInventory($masterProduct->tiktokProduct, $newMasterStock);
                }
            }
        });
    }
}