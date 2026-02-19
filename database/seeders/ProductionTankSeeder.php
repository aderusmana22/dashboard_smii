<?php

namespace Database\Seeders;

use App\Models\ProductionTank;
use App\Models\ProductionTankReading;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ProductionTankSeeder extends Seeder
{
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();

        // Bersihkan data lama untuk menghindari duplikasi
        DB::table('production_tank_readings')->truncate();
        DB::table('production_tanks')->truncate();

        Schema::enableForeignKeyConstraints();

        $tanks = [
            // Drop Tanks
            ['name' => 'Drop Tank 1', 'capacity_kg' => 10000, 'group_name' => 'DROPTANK', 'status' => 'Holding'],
            ['name' => 'Drop Tank 2', 'capacity_kg' => 10000, 'group_name' => 'DROPTANK', 'status' => 'Holding'],
            ['name' => 'Drop Tank 3', 'capacity_kg' => 10000, 'group_name' => 'DROPTANK', 'status' => 'Holding'],
            ['name' => 'Drop Tank 4', 'capacity_kg' => 10000, 'group_name' => 'DROPTANK', 'status' => 'Holding'],
            // Process Tanks
            ['name' => 'N.W.B.', 'capacity_kg' => 10000, 'group_name' => 'PROCESS', 'status' => 'Process'],
            ['name' => 'Hydro', 'capacity_kg' => 10000, 'group_name' => 'PROCESS', 'status' => 'Process'],
            // Crystalizers
            ['name' => 'Crystalizer 1', 'capacity_kg' => 40000, 'group_name' => 'CRYSTALIZER', 'status' => 'Cooling'],
            ['name' => 'Crystalizer 2', 'capacity_kg' => 40000, 'group_name' => 'CRYSTALIZER', 'status' => 'Cooling'],
            ['name' => 'Crystalizer 3', 'capacity_kg' => 40000, 'group_name' => 'CRYSTALIZER', 'status' => 'Cooling'],
            ['name' => 'Crystalizer 4', 'capacity_kg' => 40000, 'group_name' => 'CRYSTALIZER', 'status' => 'Cooling'],
            // S Tanks
            ['name' => 'S12 Tank', 'capacity_kg' => 5000, 'group_name' => 'STANK', 'status' => 'Storage'],
            ['name' => 'S13 Tank', 'capacity_kg' => 13000, 'group_name' => 'STANK', 'status' => 'Storage'],
            ['name' => 'S14 Tank', 'capacity_kg' => 5000, 'group_name' => 'STANK', 'status' => 'Storage'],
            // Deodorizers
            ['name' => 'Deodorizer 1', 'capacity_kg' => 10000, 'group_name' => 'DEODORIZER', 'status' => 'Heating'],
            ['name' => 'Deodorizer 2', 'capacity_kg' => 10000, 'group_name' => 'DEODORIZER', 'status' => 'Heating'],
            ['name' => 'Head Tank', 'capacity_kg' => 10000, 'group_name' => 'DEODORIZER', 'status' => 'Other'], // Masuk grup Deodorizer
        ];

        $today = Carbon::today();
        $allReadings = [];

        foreach ($tanks as $tankData) {
            $tank = ProductionTank::create([
                'name' => $tankData['name'],
                'capacity_kg' => $tankData['capacity_kg'],
                'group_name' => $tankData['group_name'],
            ]);

            // Buat data historis 1 tahun
            for ($i = 0; $i < 365; $i++) {
                $allReadings[] = [
                    'production_tank_id' => $tank->id,
                    'reading_date' => $today->copy()->subDays($i)->toDateString(),
                    'current_value_kg' => rand($tank->capacity_kg * 0.3, $tank->capacity_kg * 0.95),
                    'status' => $tankData['status'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        ProductionTankReading::insert($allReadings);
    }
}