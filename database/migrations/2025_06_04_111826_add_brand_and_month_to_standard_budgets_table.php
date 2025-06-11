<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('standard_budgets', function (Blueprint $table) {
            if (!Schema::hasColumn('standard_budgets', 'brand_name')) {
                $table->string('brand_name')->after('id');
            }
            if (!Schema::hasColumn('standard_budgets', 'month')) {
                $table->tinyInteger('month')->after('amount');
            }


            $tableName = 'standard_budgets';
            $connection = Schema::getConnection()->getDoctrineSchemaManager();
            $indexes = $connection->listTableIndexes($tableName);

            $constraintName = 'brand_region_month_year_unique';
            if (!isset($indexes[$constraintName])) {
                 $table->unique(['brand_name', 'name_region', 'month', 'year'], $constraintName);
            }
        });
    }

    public function down(): void
    {
        Schema::table('standard_budgets', function (Blueprint $table) {
            $constraintName = 'brand_region_month_year_unique';
            $tableName = 'standard_budgets';
            $connection = Schema::getConnection()->getDoctrineSchemaManager();
            $indexes = $connection->listTableIndexes($tableName);

            if (isset($indexes[$constraintName])) {
                $table->dropUnique($constraintName);
            }

            if (Schema::hasColumn('standard_budgets', 'brand_name')) {
                $table->dropColumn('brand_name');
            }
            if (Schema::hasColumn('standard_budgets', 'month')) {
                $table->dropColumn('month');
            }
        });
    }
};