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
        Schema::create('tiktok_shops', function (Blueprint $table) {
            $table->id(); // Sesuai dengan: bigint(20), UNSIGNED, Primary, AUTO_INCREMENT
            $table->string('open_id')->index(); // Sesuai dengan: varchar(255), Index
            $table->string('seller_name'); // Sesuai dengan: varchar(255)
            $table->text('access_token'); // Sesuai dengan: text
            $table->text('refresh_token'); // Sesuai dengan: text
            $table->dateTime('access_token_expires_at')->nullable(); // Sesuai dengan: datetime, Null
            $table->dateTime('refresh_token_expires_at')->nullable(); // Sesuai dengan: datetime, Null
            $table->timestamps(); // Sesuai dengan: created_at dan updated_at, timestamp, Null
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tiktok_shops');
    }
};