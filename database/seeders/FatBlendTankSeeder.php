<?php

namespace Database\Seeders;

use App\Models\FatBlendTank;
use App\Models\FatBlendTankReading;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class FatBlendTankSeeder extends Seeder
{
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();
        FatBlendTankReading::truncate();
        FatBlendTank::truncate();
        Schema::enableForeignKeyConstraints();

        $tanks = [
            ['name' => 'Fat Blend 1', 'capacity_kg' => 25000, 'source_type' => 'MANUAL'],
            ['name' => 'Fat Blend 2', 'capacity_kg' => 25000, 'source_type' => 'PLC'],
            ['name' => 'Fat Blend 3', 'capacity_kg' => 25000, 'source_type' => 'PLC'],
            ['name' => 'Fat Blend 4', 'capacity_kg' => 25000, 'source_type' => 'WAITING'],
            ['name' => 'Fat Blend 5', 'capacity_kg' => 25000, 'source_type' => 'WAITING'],
            ['name' => 'Fat Blend 6', 'capacity_kg' => 25000, 'source_type' => 'WAITING'],
            ['name' => 'Fat Blend 7', 'capacity_kg' => 25000, 'source_type' => 'WAITING'],
            ['name' => 'Fat Blend 8', 'capacity_kg' => 25000, 'source_type' => 'WAITING'],
            ['name' => 'Fat Blend 9', 'capacity_kg' => 25000, 'source_type' => 'WAITING'],
        ];

        $today = Carbon::today();
        $allReadings = [];

        foreach ($tanks as $tankData) {
            $tank = FatBlendTank::create($tankData);

            for ($i = 0; $i < 365; $i++) {
                $allReadings[] = [
                    'fat_blend_tank_id' => $tank->id,
                    'reading_date' => $today->copy()->subDays($i)->toDateString(),
                    'current_value_kg' => rand(1000, $tank->capacity_kg),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }
        
        FatBlendTankReading::insert($allReadings);
    }
}