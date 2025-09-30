<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('master_products', function (Blueprint $table) {
            $table->id();
            $table->string('title')->unique()->comment('Kunci utama untuk linking');
            $table->text('main_image_url')->nullable();
            $table->integer('total_stock')->default(0);

            // Relasi ke tabel sumber
            $table->foreignId('tiktok_product_id')->nullable()->constrained('tiktok_products')->onDelete('set null');
            $table->foreignId('shopee_product_id')->nullable()->constrained('shopee_products')->onDelete('set null');

            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('master_products'); }
};