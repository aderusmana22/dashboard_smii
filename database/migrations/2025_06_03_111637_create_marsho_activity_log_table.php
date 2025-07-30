<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
{
    Schema::connection(config('activitylog.database_connection'))->create(
        // UBAH BARIS INI
        'marsho_activity_logs', // <--- dari 'activity_log' menjadi 'marsho_activity_logs'
        function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('log_name')->nullable()->index();
            $table->text('description');
            $table->nullableMorphs('subject', 'subject');
            $table->string('event')->nullable();
            $table->nullableMorphs('causer', 'causer');
            $table->json('properties')->nullable();
            $table->uuid('batch_uuid')->nullable();
            $table->timestamps();
        }
    );
}

public function down()
{
    // UBAH BARIS INI JUGA
    Schema::connection(config('activitylog.database_connection'))->dropIfExists('marsho_activity_logs');
}
};