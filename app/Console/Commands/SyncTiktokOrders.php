<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\TiktokShop\TiktokGetOrderListService;
use Illuminate\Support\Facades\Log;

class SyncTiktokOrders extends Command
{
    protected $signature = 'sync:tiktok-orders';
    protected $description = 'Fetch recent orders from TikTok Shop API';

    public function handle(TiktokGetOrderListService $orderService)
    {
        $this->info('Memulai pengambilan pesanan dari TikTok...');
        Log::info('SCHEDULER: Memulai pengambilan pesanan TikTok.');

        try {
            $orders = $orderService->fetchAllOrders();
            $this->info('Berhasil mengambil ' . count($orders) . ' pesanan dari TikTok.');
            Log::info('SCHEDULER: Berhasil mengambil ' . count($orders) . ' pesanan dari TikTok.');
        } catch (\Exception $e) {
            $this->error('Gagal mengambil pesanan TikTok: ' . $e->getMessage());
            Log::error('SCHEDULER: Gagal mengambil pesanan TikTok.', ['error' => $e->getMessage()]);
        }
    }
}