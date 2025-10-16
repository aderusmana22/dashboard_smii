<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Menjalankan migrasi.
     * Tabel ini akan menjadi "buku catatan" untuk semua pesanan dari semua platform,
     * melacak status sinkronisasi stok untuk setiap pesanan.
     */
    public function up(): void
    {
        Schema::create('ecommerce_orders', function (Blueprint $table) {
            $table->id();
            $table->string('platform'); // 'tiktok', 'shopee'
            $table->string('platform_order_id')->unique(); // ID unik dari platform asal
            $table->string('platform_status'); // Status terakhir yang diambil dari API
            $table->enum('stock_sync_status', ['PENDING', 'PROCESSED', 'REVERSED', 'SKIPPED', 'FAILED'])->default('PENDING');
            $table->json('line_items'); // Detail produk (SKU, quantity)
            $table->timestamp('processed_at')->nullable(); // Kapan stok disinkronkan
            $table->timestamps();
        });
    }

    /**
     * Membatalkan migrasi.
     */
    public function down(): void
    {
        Schema::dropIfExists('ecommerce_orders');
    }
};