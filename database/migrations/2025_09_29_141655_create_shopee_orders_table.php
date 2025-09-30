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
        // Tabel untuk menyimpan data utama pesanan Shopee
        Schema::create('shopee_orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_sn')->unique()->comment('Shopee unique identifier for an order');
            $table->string('order_status')->index();
            $table->string('region', 10)->nullable();
            $table->string('currency', 10)->nullable();
            $table->boolean('cod')->default(false);
            $table->decimal('total_amount', 15, 2);
            $table->decimal('estimated_shipping_fee', 15, 2)->nullable();
            $table->decimal('actual_shipping_fee', 15, 2)->nullable();
            $table->string('payment_method')->nullable();
            $table->string('shipping_carrier')->nullable();
            $table->string('recipient_name')->nullable();
            $table->string('recipient_phone')->nullable();
            $table->text('recipient_full_address')->nullable();
            $table->timestamp('pay_time')->nullable()->comment('Waktu pesanan dibayar');
            $table->timestamp('ship_by_date')->nullable()->comment('Batas waktu pengiriman');
            $table->timestamp('create_time_shopee')->nullable()->comment('Waktu pesanan dibuat di Shopee');
            $table->json('raw_data');
            $table->timestamps();
        });

        // Tabel untuk menyimpan setiap item produk dalam sebuah pesanan Shopee
        Schema::create('shopee_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shopee_order_id')->constrained('shopee_orders')->onDelete('cascade');
            $table->unsignedBigInteger('order_item_id')->comment('Identifier unik untuk item dalam konteks pesanan');
            $table->unsignedBigInteger('item_id')->index();
            $table->string('item_name');
            $table->string('item_sku')->nullable()->index();
            $table->unsignedBigInteger('model_id')->index();
            $table->string('model_name')->nullable();
            $table->string('model_sku')->nullable()->index();
            $table->integer('model_quantity_purchased');
            $table->decimal('model_original_price', 15, 2);
            $table->decimal('model_discounted_price', 15, 2);
            $table->text('image_url')->nullable();
            $table->timestamps();

            // Kunci unik untuk mencegah duplikasi item per pesanan
            $table->unique(['shopee_order_id', 'order_item_id'], 'shopee_order_item_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shopee_order_items');
        Schema::dropIfExists('shopee_orders');
    }
};