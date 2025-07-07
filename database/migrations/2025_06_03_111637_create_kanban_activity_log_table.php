<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Ganti 'kanban_activity_log' dengan nama tabel yang Anda inginkan
        $tableName = 'kanban_activity_log';
        $connection = config('activitylog.database_connection'); // Anda mungkin ingin mengatur koneksi database khusus juga

        Schema::connection($connection)->create($tableName, function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('log_name')->nullable()->index();
            $table->text('description');
            $table->nullableMorphs('subject', 'subject'); // subject_type, subject_id
            $table->string('event')->nullable();
            $table->nullableMorphs('causer', 'causer');   // causer_type, causer_id
            $table->json('properties')->nullable();
            $table->uuid('batch_uuid')->nullable(); // Untuk grouping log jika diperlukan
            $table->timestamps();
        });
    }

    public function down()
    {
        $tableName = 'kanban_activity_log';
        $connection = config('activitylog.database_connection');
        Schema::connection($connection)->dropIfExists($tableName);
    }
};