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
        Schema::create('product_tonnages', function (Blueprint $table) {
            $table->id();
            // Relasi ke tabel master_products
            $table->foreignId('master_product_id')
                  ->constrained('master_products')
                  ->onDelete('cascade'); // Hapus tonage jika produk master dihapus
            
            // Kolom untuk menyimpan nilai tonase
            $table->decimal('tonnage', 10, 6)->default(0.000000); // 8 digit total, 3 di belakang koma

            $table->timestamps();

            // Pastikan setiap produk hanya punya satu entri tonase
            $table->unique('master_product_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_tonnages');
    }
};