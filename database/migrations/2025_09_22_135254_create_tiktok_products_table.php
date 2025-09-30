<?php

// database/migrations/xxxx_xx_xx_xxxxxx_create_tiktok_products_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tiktok_products', function (Blueprint $table) {
            $table->id();
            $table->string('tiktok_product_id')->unique()->comment('ID produk dari TikTok API');
            $table->string('title');
            $table->string('sku')->nullable()->index();
            $table->string('status');
            $table->text('main_image_url')->nullable();
            $table->unsignedInteger('total_stock')->default(0);
            $table->string('price_range')->nullable();
            $table->json('raw_data')->comment('Data mentah lengkap dari API TikTok');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tiktok_products');
    }
};