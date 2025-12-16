<?php

namespace Database\Seeders;

use App\Models\BleachedOilTank;
use App\Models\BleachedOilTankReading;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class BleachedOilTankSeeder extends Seeder
{
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();
        BleachedOilTankReading::truncate();
        BleachedOilTank::truncate();
        Schema::enableForeignKeyConstraints();

        $tanksData = [
            ['code' => '6T1', 'capacity' => 11000, 'oil_code' => '111-128', 'desc' => 'RBDHPKO42'],
            ['code' => '6T2', 'capacity' => 11000, 'oil_code' => '111-128', 'desc' => 'RBDHPKO42'],
            ['code' => '6T3', 'capacity' => 11000, 'oil_code' => '103-110', 'desc' => 'RBD MISC'],
            ['code' => '6T4', 'capacity' => 11000, 'oil_code' => '110-103', 'desc' => 'RBDHPO43'],
            ['code' => '6T5', 'capacity' => 11000, 'oil_code' => '110-103', 'desc' => 'RBDHPO43'],
            ['code' => '6T6', 'capacity' => 11000, 'oil_code' => '110-130', 'desc' => 'RBDHSBO36'],
            ['code' => '6T7', 'capacity' => 11000, 'oil_code' => '110-147', 'desc' => 'RBDHCS58'],
            ['code' => '6T8', 'capacity' => 12000, 'oil_code' => null, 'desc' => 'Available'],
            ['code' => '6T9', 'capacity' => 12000, 'oil_code' => null, 'desc' => 'Available'],
            ['code' => '6T10', 'capacity' => 12000, 'oil_code' => '110-150', 'desc' => 'RBDHCP46'],
            ['code' => '6T11', 'capacity' => 12000, 'oil_code' => '110-134', 'desc' => 'RBDHPO55'],
            ['code' => '6T12', 'capacity' => 12000, 'oil_code' => '110-134', 'desc' => 'RBDHPO55'],
            ['code' => '6T13', 'capacity' => 13500, 'oil_code' => '100-010', 'desc' => 'CRUDE MISC'],
            ['code' => '6T14', 'capacity' => 13500, 'oil_code' => '100-010', 'desc' => 'CRUDE MISC'],
            ['code' => '6T15', 'capacity' => 80000, 'oil_code' => '101-012', 'desc' => 'PE (T) - Large'],
        ];

        $today = Carbon::today();
        $allReadings = [];

        foreach ($tanksData as $data) {
            $tank = BleachedOilTank::create([
                'tank_code' => $data['code'],
                'capacity_kg' => $data['capacity'],
            ]);

            for ($i = 0; $i < 365; $i++) {
                $currentValue = $data['oil_code'] ? rand($tank->capacity_kg * 0.1, $tank->capacity_kg) : 0;
                $allReadings[] = [
                    'bleached_oil_tank_id' => $tank->id,
                    'reading_date' => $today->copy()->subDays($i)->toDateString(),
                    'oil_code' => $data['oil_code'],
                    'description' => $data['desc'],
                    'current_value_kg' => $currentValue,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }
        
        BleachedOilTankReading::insert($allReadings);
    }
}