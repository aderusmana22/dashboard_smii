<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\TiktokShop\TiktokGetOrderListService;
use App\Services\Shopee\ShopeeGetOrderListService;
use App\Services\StockSynchronizationService;
use App\Models\EcommerceSetting;
use Illuminate\Support\Facades\Log;

class RunFullSync extends Command
{
    protected $signature = 'sync:run-all';
    protected $description = 'Menjalankan siklus sinkronisasi pesanan dan stok secara berurutan dan aman.';

    public function handle(
        TiktokGetOrderListService $tiktokOrderService,
        ShopeeGetOrderListService $shopeeOrderService,
        StockSynchronizationService $stockSyncService
    ) {
        Log::info('SCHEDULER: Memulai siklus sinkronisasi pesanan dan stok.');
        $this->info('Memulai siklus sinkronisasi penuh...');
        $startTime = now();

        try {
            $this->info('-> Mengambil pesanan terupdate dari Shopee...');
            $shopeeOrderService->syncOrdersSinceLastUpdate();
            
            $this->info('-> Mengambil pesanan terupdate dari TikTok...');
            $tiktokOrderService->syncOrdersSinceLastUpdate();

            $this->info('-> Memproses sinkronisasi stok untuk pesanan yang pending...');
            $stockSyncService->processPendingOrders();

            $this->info('-> Memperbarui timestamp sinkronisasi...');
            EcommerceSetting::updateOrCreate(
                ['key' => 'shopee_orders_last_sync'],
                ['value' => $startTime->toDateTimeString()]
            );
            EcommerceSetting::updateOrCreate(
                ['key' => 'tiktok_last_sync'],
                ['value' => $startTime->toDateTimeString()]
            );

            Log::info('SCHEDULER: Siklus sinkronisasi pesanan dan stok selesai dengan sukses.');
            $this->info('Sinkronisasi selesai dengan sukses.');

        } catch (\Exception $e) {
            Log::critical('SCHEDULER: Siklus sinkronisasi pesanan dan stok GAGAL TOTAL.', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            $this->error('Sinkronisasi gagal total: ' . $e->getMessage());
        }
        
        return Command::SUCCESS;
    }
}