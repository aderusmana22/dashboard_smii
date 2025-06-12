<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('standard_budgets', function (Blueprint $table) {
            $table->id();
            $table->string('brand_name');
            $table->string('name_region');
            $table->decimal('amount', 15, 4);
            $table->integer('month');
            $table->integer('year');
            $table->timestamps();

            $table->unique(['brand_name', 'name_region', 'month', 'year'], 'standard_budgets_unique_entry');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('standard_budgets');
    }
};