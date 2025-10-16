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
        $pendingOrders = EcommerceOrder::where('stock_sync_status', 'PENDING')->get();

        if ($pendingOrders->isEmpty()) {
            Log::info('SYNC-STOCK: Tidak ada pesanan yang perlu disinkronkan stoknya.');
            return;
        }

        Log::info("SYNC-STOCK: Ditemukan {$pendingOrders->count()} pesanan untuk sinkronisasi stok.");

        foreach ($pendingOrders as $order) {
            $stockReductionStatuses = ['ON_HOLD', 'AWAITING_SHIPMENT', 'READY_TO_SHIP', 'PROCESSED'];
            $stockIncreaseStatuses = ['CANCEL', 'CANCELLED'];

            $action = null;
            if (in_array($order->platform_status, $stockReductionStatuses)) $action = 'reduce';
            elseif (in_array($order->platform_status, $stockIncreaseStatuses)) $action = 'increase';

            if (!$action) {
                $order->update(['stock_sync_status' => 'SKIPPED']);
                continue;
            }

            try {
                $this->adjustStockForOrder($order, $action);
                $newStatus = ($action === 'reduce') ? 'PROCESSED' : 'REVERSED';
                $order->update(['stock_sync_status' => $newStatus, 'processed_at' => now()]);
                Log::info("SYNC-STOCK: Berhasil memproses pesanan {$order->platform_order_id}. Status sync: {$newStatus}");
            } catch (\Exception $e) {
                $order->update(['stock_sync_status' => 'FAILED']);
                Log::error("SYNC-STOCK: Gagal memproses pesanan {$order->platform_order_id}: " . $e->getMessage(), ['exception' => $e]);
            }
        }
    }

    /**
     * Melakukan penyesuaian stok untuk satu pesanan dalam sebuah transaksi database.
     */
    private function adjustStockForOrder(EcommerceOrder $order, string $action): void
    {
        DB::transaction(function () use ($order, $action) {
            foreach ($order->line_items as $item) {
                $productSku = $item['item_sku'] ?? $item['seller_sku']; // Shopee: item_sku, TikTok: seller_sku
                $quantity = $item['model_quantity_purchased'] ?? $item['quantity'];

                if (!$productSku || !$quantity) continue;

                $masterProduct = MasterProduct::whereHas('shopeeProduct', fn($q) => $q->where('item_sku', $productSku))
                                    ->orWhereHas('tiktokProduct', fn($q) => $q->where('seller_sku', $productSku))
                                    ->first();

                if (!$masterProduct) {
                    Log::warning("SYNC-STOCK: MasterProduct tidak ditemukan untuk SKU: {$productSku} pada pesanan {$order->platform_order_id}");
                    continue;
                }

                $masterProduct = MasterProduct::where('id', $masterProduct->id)->lockForUpdate()->first();
                $newMasterStock = ($action === 'reduce')
                    ? $masterProduct->total_stock - $quantity
                    : $masterProduct->total_stock + $quantity;

                if ($order->platform === 'tiktok' && $masterProduct->shopeeProduct) {
                    $this->shopeeInventoryService->updateInventory($masterProduct->shopeeProduct, $newMasterStock);
                } elseif ($order->platform === 'shopee' && $masterProduct->tiktokProduct) {
                    $this->tiktokInventoryService->updateInventory($masterProduct->tiktokProduct, $newMasterStock);
                }

                $masterProduct->update(['total_stock' => $newMasterStock]);
            }
        });
    }
}