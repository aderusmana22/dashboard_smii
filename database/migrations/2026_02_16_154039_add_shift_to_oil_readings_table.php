<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\QueryException;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Panggil fungsi helper untuk memproses tabel dengan aman
        $this->safeMigrateTable('oil_batch_refinery_readings', 'tank_id', 'oil_batch_refinery_tanks', 'obr_unique_shift');
        $this->safeMigrateTable('oil_utility_gas_readings', 'master_id', 'oil_utility_gas_masters', 'oug_unique_shift');
    }

    /**
     * Helper function agar migrasi aman dari error "Already Exists" atau "Not Found"
     */
    private function safeMigrateTable($tableName, $fkColumn, $refTable, $newIndexName)
    {
        // 1. COBA DROP FOREIGN KEY LAMA
        // Kita bungkus try-catch agar jika FK sudah hilang, tidak error.
        try {
            Schema::table($tableName, function (Blueprint $table) use ($fkColumn) {
                $table->dropForeign([$fkColumn]);
            });
        } catch (\Exception $e) {
            // Biarkan lanjut, artinya FK sudah tidak ada.
        }

        // 2. COBA DROP INDEX LAMA (Unique tank+date)
        try {
            Schema::table($tableName, function (Blueprint $table) use ($fkColumn) {
                $table->dropUnique([$fkColumn, 'reading_date']);
            });
        } catch (\Exception $e) {
            // Biarkan lanjut
        }

        // 3. TAMBAH KOLOM SHIFT (Cek dulu biar gak error duplicate column)
        if (!Schema::hasColumn($tableName, 'shift')) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->unsignedTinyInteger('shift')->after('reading_date')->default(1);
            });
        }

        // 4. BUAT INDEX BARU (Cek try-catch takutnya sudah ada)
        try {
            Schema::table($tableName, function (Blueprint $table) use ($fkColumn, $newIndexName) {
                $table->unique([$fkColumn, 'reading_date', 'shift'], $newIndexName);
            });
        } catch (\Exception $e) {
            // Index mungkin sudah ada, lanjut.
        }

        // 5. PASANG KEMBALI FOREIGN KEY
        // Bungkus try-catch untuk memastikan aman
        try {
            Schema::table($tableName, function (Blueprint $table) use ($fkColumn, $refTable) {
                $table->foreign($fkColumn)
                      ->references('id')
                      ->on($refTable)
                      ->onDelete('cascade');
            });
        } catch (\Exception $e) {
            // FK mungkin sudah terpasang, lanjut.
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Rollback Batch Refinery
        Schema::table('oil_batch_refinery_readings', function (Blueprint $table) {
            $table->dropForeign(['tank_id']); // Drop FK baru
            $table->dropUnique('obr_unique_shift'); // Drop Index baru
            
            if (Schema::hasColumn('oil_batch_refinery_readings', 'shift')) {
                $table->dropColumn('shift');
            }
            
            // Restore Index Lama
            $table->unique(['tank_id', 'reading_date']);
            
            // Restore FK Lama
            $table->foreign('tank_id')
                  ->references('id')
                  ->on('oil_batch_refinery_tanks')
                  ->onDelete('cascade');
        });

        // Rollback Utility Gas
        Schema::table('oil_utility_gas_readings', function (Blueprint $table) {
            $table->dropForeign(['master_id']);
            $table->dropUnique('oug_unique_shift');

            if (Schema::hasColumn('oil_utility_gas_readings', 'shift')) {
                $table->dropColumn('shift');
            }

            $table->unique(['master_id', 'reading_date']);

            $table->foreign('master_id')
                  ->references('id')
                  ->on('oil_utility_gas_masters')
                  ->onDelete('cascade');
        });
    }
};