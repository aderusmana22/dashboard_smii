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
        Schema::create('marsho_users', function (Blueprint $table) {
            $table->id();
            
            // Foreign key ke tabel users, memastikan setiap user hanya punya satu profil Marsho
            $table->foreignId('user_id')
                  ->unique()
                  ->constrained('users')
                  ->onDelete('cascade');

            // Foreign key ke tabel marsho_departments
            $table->foreignId('marsho_department_id')
                  ->constrained('marsho_departments')
                  ->onDelete('cascade');
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('marsho_users');
    }
};  