<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('oil_batch_refinery_tanks', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->string('group_name'); 
            $table->decimal('capacity_kg', 15, 2)->default(0);
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('oil_batch_refinery_readings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tank_id')->constrained('oil_batch_refinery_tanks')->cascadeOnDelete();
            $table->date('reading_date');
            $table->decimal('current_value_kg', 15, 2)->nullable();
            $table->decimal('temperature', 8, 2)->nullable();
            $table->decimal('gauge_board', 8, 2)->nullable();
            $table->string('oil_code')->nullable();
            $table->string('description')->nullable();
            $table->string('status')->default('Process');
            $table->string('created_by')->nullable();
            $table->timestamps();
            $table->unique(['tank_id', 'reading_date']);
        });

        Schema::create('oil_batch_refinery_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('action');
            $table->text('details');
            $table->ipAddress('ip_address')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('oil_batch_refinery_logs');
        Schema::dropIfExists('oil_batch_refinery_readings');
        Schema::dropIfExists('oil_batch_refinery_tanks');
    }
};