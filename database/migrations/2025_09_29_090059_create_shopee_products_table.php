<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shopee_products', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('shopee_item_id')->unique();
            $table->string('item_name');
            $table->string('item_sku')->nullable()->index();
            $table->string('item_status')->nullable();
            $table->text('main_image_url')->nullable();
            $table->integer('total_stock')->default(0);
            $table->string('price_info')->nullable();
            $table->json('raw_data')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shopee_products');
    }
};