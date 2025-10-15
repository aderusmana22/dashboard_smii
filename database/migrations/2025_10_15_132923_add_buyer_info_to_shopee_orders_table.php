<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('shopee_orders', function (Blueprint $table) {
            // Menambahkan kolom baru setelah 'recipient_full_address' untuk keterbacaan
            $table->unsignedBigInteger('buyer_user_id')->nullable()->after('recipient_full_address')->comment('User ID pembeli di Shopee');
            $table->string('buyer_username')->nullable()->after('buyer_user_id')->comment('Username pembeli di Shopee');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('shopee_orders', function (Blueprint $table) {
            // Hapus kolom jika migrasi di-rollback
            $table->dropColumn(['buyer_user_id', 'buyer_username']);
        });
    }
};