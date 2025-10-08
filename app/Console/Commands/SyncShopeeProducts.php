<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\Shopee\ShopeeProductSyncService;
use Illuminate\Support\Facades\Log;
use App\Models\EcommerceSetting; // <-- Tambahkan ini

class SyncShopeeProducts extends Command
{
    protected $signature = 'sync:shopee-products';
    protected $description = 'Sync products from Shopee API to the database';

    public function handle(ShopeeProductSyncService $syncService)
    {
        $this->info('Memulai sinkronisasi produk Shopee...');
        Log::info('SCHEDULER: Memulai sinkronisasi produk Shopee.');

        try {
            $syncService->syncProductsFromApi();

            // === TAMBAHKAN BARIS INI ===
            // Update timestamp setelah berhasil
            EcommerceSetting::updateOrCreate(
                ['key' => 'shopee_products_last_sync'],
                ['value' => now()]
            );
            // ===========================

            $this->info('Sinkronisasi produk Shopee berhasil diselesaikan.');
            Log::info('SCHEDULER: Sinkronisasi produk Shopee berhasil.');
        } catch (\Exception $e) {
            $this->error('Terjadi kesalahan saat sinkronisasi produk Shopee: ' . $e->getMessage());
            Log::error('SCHEDULER: Gagal sinkronisasi produk Shopee.', ['error' => $e->getMessage()]);
        }
    }
}