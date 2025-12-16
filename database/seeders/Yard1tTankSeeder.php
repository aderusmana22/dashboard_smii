<?php

namespace Database\Seeders;

use App\Models\Yard1tTank;
use App\Models\Yard1tTankReading;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class Yard1tTankSeeder extends Seeder
{
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();
        Yard1tTankReading::truncate();
        Yard1tTank::truncate();
        Schema::enableForeignKeyConstraints();

        // Data master tangki beserta info minyaknya
        $tanksData = [
            ['code' => '1T1', 'capacity' => 100000, 'oil_code' => '101-005', 'desc' => 'PSS'],
            ['code' => '1T2', 'capacity' => 100000, 'oil_code' => '101-005', 'desc' => 'PSS'],
            ['code' => '1T3', 'capacity' => 50000, 'oil_code' => '101-070', 'desc' => 'PO /SG'],
            ['code' => '1T4', 'capacity' => 50000, 'oil_code' => null, 'desc' => 'Available'], // Tangki kosong
            ['code' => '1T5', 'capacity' => 50000, 'oil_code' => '101-071', 'desc' => 'PSS /SG'],
            ['code' => '1T6', 'capacity' => 50000, 'oil_code' => '101-070', 'desc' => 'PO /SG'],
            ['code' => '1T7', 'capacity' => 50000, 'oil_code' => '101-036', 'desc' => 'PKO'],
            ['code' => '1T8', 'capacity' => 50000, 'oil_code' => '111-101', 'desc' => 'HCNO'],
            ['code' => '1T9', 'capacity' => 25000, 'oil_code' => '101-010', 'desc' => 'SBO'],
            ['code' => '1T10', 'capacity' => 25000, 'oil_code' => '101-010', 'desc' => 'SBO'],
            ['code' => '1T11', 'capacity' => 25000, 'oil_code' => null, 'desc' => 'Available'], // Tangki kosong
            ['code' => '1T12', 'capacity' => 25000, 'oil_code' => '101-038', 'desc' => 'RBD PKS'],
            ['code' => '1T13', 'capacity' => 200000, 'oil_code' => '101-007', 'desc' => 'PO (T)'],
            ['code' => '1T14', 'capacity' => 200000, 'oil_code' => '101-007', 'desc' => 'PO (T)'],
            ['code' => '1T17', 'capacity' => 25000, 'oil_code' => '101-001', 'desc' => 'CNO'],
            ['code' => '1T18', 'capacity' => 25000, 'oil_code' => '101-001', 'desc' => 'CNO'],
            ['code' => '1T19', 'capacity' => 25000, 'oil_code' => '101-001', 'desc' => 'PKO'],
            ['code' => '1T20', 'capacity' => 25000, 'oil_code' => '101-036', 'desc' => 'PKO'],
            ['code' => '1T21', 'capacity' => 25000, 'oil_code' => '101-012', 'desc' => 'PE (T)'],
            ['code' => '1T22', 'capacity' => 25000, 'oil_code' => null, 'desc' => 'Available'], // Tangki kosong
        ];

        $today = Carbon::today();
        $allReadings = [];

        foreach ($tanksData as $data) {
            $tank = Yard1tTank::create([
                'tank_code' => $data['code'],
                'capacity_kg' => $data['capacity'],
            ]);

            // Buat data historis 1 tahun
            for ($i = 0; $i < 365; $i++) {
                $currentValue = $data['oil_code'] ? rand($tank->capacity_kg * 0.2, $tank->capacity_kg) : 0;
                $allReadings[] = [
                    'yard1t_tank_id' => $tank->id,
                    'reading_date' => $today->copy()->subDays($i)->toDateString(),
                    'oil_code' => $data['oil_code'],
                    'description' => $data['desc'],
                    'current_value_kg' => $currentValue,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }
        
        Yard1tTankReading::insert($allReadings);
    }
}