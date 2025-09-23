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
        // Tabel untuk menyimpan data utama pesanan
        Schema::create('tiktokped_orders', function (Blueprint $table) {
            $table->id();
            $table->string('tiktok_order_id')->unique(); // ID pesanan dari platform
            $table->string('status')->index();
            $table->decimal('total_amount', 15, 2);
            $table->decimal('sub_total', 15, 2);
            $table->decimal('shipping_fee', 15, 2);
            $table->decimal('platform_discount', 15, 2)->default(0);
            $table->string('payment_method')->nullable();
            $table->string('shipping_provider')->nullable();
            $table->string('tracking_number')->nullable()->index();
            $table->string('recipient_name')->nullable();
            $table->string('recipient_phone')->nullable();
            $table->text('recipient_full_address')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('created_at_tiktok')->nullable();
            $table->json('raw_data');
            $table->timestamps();
        });

        // Tabel untuk menyimpan setiap item produk dalam sebuah pesanan
        Schema::create('tiktokped_order_items', function (Blueprint $table) {
            $table->id();
            // Kunci asing yang terhubung ke tabel tiktokped_orders
            $table->foreignId('tiktokped_order_id')->constrained('tiktokped_orders')->onDelete('cascade');
            $table->string('line_item_id')->unique(); // ID unik untuk setiap baris item
            $table->string('product_id')->index(); // ID produk dari platform
            $table->string('product_name');
            $table->string('sku_id')->index();
            $table->string('sku_name')->nullable();
            $table->string('seller_sku')->nullable()->index();
            $table->text('sku_image')->nullable();
            $table->integer('quantity')->default(1); // Kita asumsikan setiap line_item adalah 1 kuantitas
            $table->decimal('sale_price', 15, 2); // Harga per unit
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tiktokped_order_items');
        Schema::dropIfExists('tiktokped_orders');
    }
};