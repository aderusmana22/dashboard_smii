<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. MASTER DATA (Konfigurasi Gas)
        Schema::create('oil_stock_utility_gas_masters', function (Blueprint $table) {
            $table->id();
            $table->string('gas_type'); // HYDROGEN, NITROGEN, AMMONIA
            $table->string('name');     // Torpedo #04, Full Cylinders
            $table->string('unit');     // Bar, Inch Water, Cyl
            $table->string('input_type')->default('number'); // 'number' atau 'stepper'
            $table->decimal('min_limit', 8, 2)->nullable(); 
            $table->decimal('max_limit', 8, 2)->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 2. READINGS (Data Harian)
        Schema::create('oil_stock_utility_gas_readings', function (Blueprint $table) {
            $table->id();
            // Relasi ke tabel master di atas
            $table->foreignId('master_id')
                  ->constrained('oil_stock_utility_gas_masters')
                  ->onDelete('cascade');
            
            $table->date('reading_date');
            $table->decimal('value', 10, 2);
            $table->string('created_by')->nullable();
            $table->timestamps();

            // Mencegah duplikat input item yang sama di tanggal yang sama
            $table->unique(['master_id', 'reading_date']);
        });

        // 3. LOGS (History Perubahan)
        Schema::create('oil_stock_utility_gas_logs', function (Blueprint $table) {
            $table->id();
            $table->string('user_name');
            $table->string('action'); // INSERT / UPDATE
            $table->date('reading_date');
            $table->string('item_name');
            $table->string('old_value')->nullable();
            $table->string('new_value');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('oil_stock_utility_gas_logs');
        Schema::dropIfExists('oil_stock_utility_gas_readings');
        Schema::dropIfExists('oil_stock_utility_gas_masters');
    }
};