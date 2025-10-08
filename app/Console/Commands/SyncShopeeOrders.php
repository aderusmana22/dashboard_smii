<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\Shopee\ShopeeGetOrderListService;
use Illuminate\Support\Facades\Log;
use App\Models\EcommerceSetting; // <-- Tambahkan ini

class SyncShopeeOrders extends Command
{
    protected $signature = 'sync:shopee-orders';
    protected $description = 'Fetch recent orders from Shopee API';

    public function handle(ShopeeGetOrderListService $orderService)
    {
        $this->info('Memulai pengambilan pesanan dari Shopee...');
        Log::info('SCHEDULER: Memulai pengambilan pesanan Shopee.');

        try {
            $orders = $orderService->fetchAllOrders();

            // === TAMBAHKAN BARIS INI ===
            // Update timestamp setelah berhasil
            EcommerceSetting::updateOrCreate(
                ['key' => 'shopee_orders_last_sync'],
                ['value' => now()]
            );
            // ===========================

            $this->info('Berhasil mengambil ' . count($orders) . ' pesanan dari Shopee.');
            Log::info('SCHEDULER: Berhasil mengambil ' . count($orders) . ' pesanan dari Shopee.');
        } catch (\Exception $e) {
            $this->error('Gagal mengambil pesanan Shopee: ' . $e->getMessage());
            Log::error('SCHEDULER: Gagal mengambil pesanan Shopee.', ['error' => $e->getMessage()]);
        }
    }
}