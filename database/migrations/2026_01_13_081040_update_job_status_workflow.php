<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE job_marsho MODIFY COLUMN status VARCHAR(255)");
        
        DB::table('job_marsho')->where('status', 'open')->update(['status' => 'to_be_scheduled']);
        DB::table('job_marsho')->where('status', 'on_process')->update(['status' => 'on_going']);

        DB::statement("ALTER TABLE job_marsho MODIFY COLUMN status ENUM('to_be_scheduled', 'scheduled', 'preparation', 'on_going', 'completed', 'closed') DEFAULT 'to_be_scheduled'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE job_marsho MODIFY COLUMN status VARCHAR(255)");
        
        DB::table('job_marsho')->where('status', 'to_be_scheduled')->update(['status' => 'open']);
        DB::table('job_marsho')->where('status', 'on_going')->update(['status' => 'on_process']);
        DB::table('job_marsho')->whereIn('status', ['scheduled', 'preparation'])->update(['status' => 'open']);

        DB::statement("ALTER TABLE job_marsho MODIFY COLUMN status ENUM('open', 'on_process', 'completed', 'closed') DEFAULT 'open'");
    }
};