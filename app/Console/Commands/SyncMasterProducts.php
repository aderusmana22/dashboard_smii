<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\MasterProductService;
use Illuminate\Support\Facades\Log;
use App\Models\EcommerceSetting; // <-- Tambahkan ini

class SyncMasterProducts extends Command
{
    protected $signature = 'sync:master-products';
    protected $description = 'Sync products from shopee and tiktok tables into the master product table';

    public function handle(MasterProductService $masterProductService)
    {
        $this->info('Memulai sinkronisasi tabel master produk...');
        Log::info('SCHEDULER: Memulai sinkronisasi tabel master produk.');

        try {
            $masterProductService->syncMasterTable();

            // === TAMBAHKAN BARIS INI ===
            // Update timestamp setelah berhasil
            EcommerceSetting::updateOrCreate(
                ['key' => 'master_products_last_sync'], // Anda mungkin perlu menambahkan key ini di DB
                ['value' => now()]
            );
            // ===========================

            $this->info('Sinkronisasi tabel master produk berhasil diselesaikan.');
            Log::info('SCHEDULER: Sinkronisasi tabel master produk berhasil.');
        } catch (\Exception $e) {
            $this->error('Terjadi kesalahan saat sinkronisasi tabel master: ' . $e->getMessage());
            Log::error('SCHEDULER: Gagal sinkronisasi tabel master.', ['error' => $e->getMessage()]);
        }
    }
}