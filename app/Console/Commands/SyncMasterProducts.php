<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\MasterProductService;
use Illuminate\Support\Facades\Log;

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
            $this->info('Sinkronisasi tabel master produk berhasil diselesaikan.');
            Log::info('SCHEDULER: Sinkronisasi tabel master produk berhasil.');
        } catch (\Exception $e) {
            $this->error('Terjadi kesalahan saat sinkronisasi tabel master: ' . $e->getMessage());
            Log::error('SCHEDULER: Gagal sinkronisasi tabel master.', ['error' => $e->getMessage()]);
        }
    }
}