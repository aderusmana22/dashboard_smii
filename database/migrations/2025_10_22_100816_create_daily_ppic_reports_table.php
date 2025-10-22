<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mps_data', function (Blueprint $table) {
            $table->id();
            $table->string('item_number');
            $table->string('description')->nullable();
            $table->string('uom')->nullable();
            $table->decimal('net_weight', 15, 4)->default(0);
            $table->integer('month');
            $table->integer('year');
            $table->decimal('inventory_qty', 15, 2)->default(0);
            $table->decimal('dispatch_qty', 15, 2)->default(0);
            $table->decimal('allocated_qty', 15, 2)->default(0);
            $table->decimal('so_outstanding', 15, 2)->default(0);
            $table->decimal('mps_qty', 15, 2)->default(0);
            $table->timestamps();

            // Data unik berdasarkan item, bulan, dan tahun
            $table->unique(['item_number', 'month', 'year']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mps_data');
    }
};