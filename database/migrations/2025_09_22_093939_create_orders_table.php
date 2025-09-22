<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('tiktok_order_id')->unique()->comment('ID pesanan unik dari TikTok API');
            $table->string('status', 50)->index()->comment('Status pesanan, e.g., COMPLETED');
            $table->decimal('total_amount', 15, 2)->comment('Total yang dibayar pelanggan');
            $table->decimal('sub_total', 15, 2)->comment('Total harga produk');
            $table->decimal('shipping_fee', 15, 2)->comment('Ongkos kirim final');
            $table->decimal('platform_discount', 15, 2)->default(0)->comment('Diskon dari platform');
            $table->string('payment_method')->nullable();
            $table->string('shipping_provider')->nullable();
            $table->string('tracking_number')->nullable()->index();
            $table->string('recipient_name')->nullable();
            $table->string('recipient_phone')->nullable();
            $table->text('recipient_full_address')->nullable();
            $table->timestamp('paid_at')->nullable()->comment('Waktu pesanan dibayar di TikTok');
            $table->timestamp('created_at_tiktok')->nullable()->comment('Waktu pesanan dibuat di TikTok');
            $table->json('raw_data')->comment('Menyimpan seluruh response JSON asli dari API');
            $table->timestamps(); // Kolom created_at dan updated_at untuk database lokal
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};