<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\TiktokShop\TiktokProductSyncService;
use Illuminate\Support\Facades\Log;
use App\Models\EcommerceSetting; // <-- Tambahkan ini

class SyncTiktokProducts extends Command
{
    protected $signature = 'sync:tiktok-products';
    protected $description = 'Sync products from TikTok Shop API to the database';

    public function handle(TiktokProductSyncService $syncService)
    {
        $this->info('Memulai sinkronisasi produk TikTok...');
        Log::info('SCHEDULER: Memulai sinkronisasi produk TikTok.');

        try {
            $syncService->syncProductsFromApi();

            // === TAMBAHKAN BARIS INI ===
            // Update timestamp setelah berhasil
            EcommerceSetting::updateOrCreate(
                ['key' => 'tiktok_products_last_sync'],
                ['value' => now()]
            );
            // ===========================

            $this->info('Sinkronisasi produk TikTok berhasil diselesaikan.');
            Log::info('SCHEDULER: Sinkronisasi produk TikTok berhasil.');
        } catch (\Exception $e) {
            $this->error('Terjadi kesalahan saat sinkronisasi produk TikTok: ' . $e->getMessage());
            Log::error('SCHEDULER: Gagal sinkronisasi produk TikTok.', ['error' => $e->getMessage()]);
        }
    }
}