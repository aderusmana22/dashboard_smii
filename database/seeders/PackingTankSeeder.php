<?php

namespace Database\Seeders;

use App\Models\PackingTank;
use App\Models\PackingTankReading;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class PackingTankSeeder extends Seeder
{
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();
        PackingTankReading::truncate();
        PackingTank::truncate();
        Schema::enableForeignKeyConstraints();

        $tanksData = [
            ['code' => '10T1', 'status' => 'READY'],
            ['code' => '10T2', 'status' => 'READY'],
            ['code' => '10T3', 'status' => 'FILLING'],
            ['code' => '10T4', 'status' => 'READY'],
            ['code' => '10T5', 'status' => 'READY'],
            ['code' => '10T6', 'status' => 'STANDBY'],
            ['code' => '10T7', 'status' => 'STANDBY'],
            ['code' => '10T8', 'status' => 'MAINTENANCE'],
            ['code' => '10T9', 'status' => 'FILLING'],
        ];

        $today = Carbon::today();
        $allReadings = [];

        foreach ($tanksData as $data) {
            $tank = PackingTank::create([
                'tank_code' => $data['code'],
                'capacity_kg' => 10000,
            ]);

            for ($i = 0; $i < 365; $i++) {
                $currentValue = 0;
                // Buat data acak yang lebih logis berdasarkan status
                if ($data['status'] === 'READY') {
                    $currentValue = rand(8000, 10000);
                } elseif ($data['status'] === 'FILLING') {
                    $currentValue = rand(2000, 8000);
                } elseif ($data['status'] === 'STANDBY') {
                    $currentValue = rand(500, 2000);
                } // MAINTENANCE akan tetap 0

                $allReadings[] = [
                    'packing_tank_id' => $tank->id,
                    'reading_date' => $today->copy()->subDays($i)->toDateString(),
                    'current_value_kg' => $currentValue,
                    'status' => $data['status'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }
        
        PackingTankReading::insert($allReadings);
    }
}