<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\Shopee\ShopeeProductSyncService;
use Illuminate\Support\Facades\Log;

class SyncShopeeProducts extends Command
{
    // Nama command yang akan dipanggil di terminal atau scheduler
    protected $signature = 'sync:shopee-products';

    // Deskripsi command
    protected $description = 'Sync products from Shopee API to the database';

    // Method utama yang akan dieksekusi
    public function handle(ShopeeProductSyncService $syncService)
    {
        $this->info('Memulai sinkronisasi produk Shopee...');
        Log::info('SCHEDULER: Memulai sinkronisasi produk Shopee.');

        try {
            $syncService->syncProductsFromApi();
            $this->info('Sinkronisasi produk Shopee berhasil diselesaikan.');
            Log::info('SCHEDULER: Sinkronisasi produk Shopee berhasil.');
        } catch (\Exception $e) {
            $this->error('Terjadi kesalahan saat sinkronisasi produk Shopee: ' . $e->getMessage());
            Log::error('SCHEDULER: Gagal sinkronisasi produk Shopee.', ['error' => $e->getMessage()]);
        }
    }
}