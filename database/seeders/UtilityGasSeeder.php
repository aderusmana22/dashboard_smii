<?php

namespace Database\Seeders;

use App\Models\UtilityGasReading;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class UtilityGasSeeder extends Seeder
{
    public function run(): void
    {
        UtilityGasReading::truncate();

        $today = Carbon::today();
        $allReadings = [];

        // Data statis untuk setiap hari
        $dailyData = [
            // Hydrogen
            ['gas_type' => 'HYDROGEN', 'unit_name' => 'Torpedo #04', 'value' => 140, 'unit' => 'Bar'],
            ['gas_type' => 'HYDROGEN', 'unit_name' => 'Torpedo #05', 'value' => 0, 'unit' => 'Bar'], // Empty
            // Nitrogen
            ['gas_type' => 'NITROGEN', 'unit_name' => 'Liquid Tank', 'value' => 78, 'unit' => 'Inch Water'],
            // Ammonia
            ['gas_type' => 'AMMONIA', 'unit_name' => 'Full Cylinders', 'value' => 7, 'unit' => 'units'],
            ['gas_type' => 'AMMONIA', 'unit_name' => 'Empty Cylinders', 'value' => 5, 'unit' => 'units'],
        ];
        
        // Buat data historis 1 tahun
        for ($i = 0; $i < 365; $i++) {
            $date = $today->copy()->subDays($i);
            foreach ($dailyData as $data) {
                // Tambahkan sedikit fluktuasi acak untuk data historis
                $value = $data['value'];
                if ($value > 0 && $i > 0) {
                    $value *= (rand(95, 105) / 100);
                }

                $allReadings[] = [
                    'reading_date' => $date->toDateString(),
                    'gas_type' => $data['gas_type'],
                    'unit_name' => $data['unit_name'],
                    'value' => round($value, 2),
                    'unit_measure' => $data['unit'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }
        
        UtilityGasReading::insert($allReadings);
    }
}