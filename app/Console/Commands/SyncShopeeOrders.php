<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\Shopee\ShopeeGetOrderListService;
use Illuminate\Support\Facades\Log;

class SyncShopeeOrders extends Command
{
    protected $signature = 'sync:shopee-orders';
    protected $description = 'Fetch recent orders from Shopee API';

    public function handle(ShopeeGetOrderListService $orderService)
    {
        $this->info('Memulai pengambilan pesanan dari Shopee...');
        Log::info('SCHEDULER: Memulai pengambilan pesanan Shopee.');

        try {
            // Anda mungkin perlu menyimpan data ini ke database
            $orders = $orderService->fetchAllOrders();
            // Log::info('Pesanan Shopee yang didapat:', $orders); // Hati-hati, bisa sangat besar
            $this->info('Berhasil mengambil ' . count($orders) . ' pesanan dari Shopee.');
            Log::info('SCHEDULER: Berhasil mengambil ' . count($orders) . ' pesanan dari Shopee.');
        } catch (\Exception $e) {
            $this->error('Gagal mengambil pesanan Shopee: ' . $e->getMessage());
            Log::error('SCHEDULER: Gagal mengambil pesanan Shopee.', ['error' => $e->getMessage()]);
        }
    }
}