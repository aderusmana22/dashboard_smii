<?php

namespace Database\Seeders;

use App\Models\Tank;
use App\Models\OilStockReading;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class OilStockReadingSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('oil_stock_readings')->truncate();
        $tanks = Tank::all();
        $today = Carbon::today();

        foreach ($tanks as $tank) {
            $readings = [];
            for ($i = 0; $i < 365; $i++) {
                $date = $today->copy()->subDays($i);
                $gaugeMeter = round(rand(50, 800) / 100, 2);
                $temperature = round(rand(4500, 6500) / 100, 2);
                $currentValue = 0;

                switch ($tank->formula_type) {
                    case 'OLEIN':
                        $currentValue = $gaugeMeter * 9808.2;
                        break;
                    case 'PE_BULK':
                        $currentValue = (0.92398 - (0.0006789 * $gaugeMeter)) * 26.4208 * $temperature;
                        break;
                    default:
                        $currentValue = $tank->capacity_kg * (rand(30, 95) / 100);
                        break;
                }
                
                if ($currentValue > $tank->capacity_kg) $currentValue = $tank->capacity_kg;
                if ($currentValue < 0) $currentValue = 0;

                $readings[] = [
                    'tank_id' => $tank->id,
                    'reading_date' => $date->toDateString(),
                    'current_value_kg' => round($currentValue, 2),
                    'temperature_celsius' => $temperature,
                    'gauge_board_meter' => $gaugeMeter,
                    'created_at' => now(), 'updated_at' => now(),
                ];
            }
            OilStockReading::insert(array_reverse($readings));
        }
    }
}