<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\StorageArea;

class InwardDashboardSeeder extends Seeder
{
    public function run()
    {
        // Data untuk 3 lingkaran di atas
        StorageArea::create([
            'name' => 'Packaging Ambient',
            'temp_range' => '',
            'total_pp' => 2824,
            'occupancy_percent' => 25,
            'actual_temp' => 29,
            'color' => '#1d4ed8' // Biru
        ]);

        StorageArea::create([
            'name' => 'Ingredient Coolroom',
            'temp_range' => '20 - 25 C',
            'total_pp' => 408,
            'occupancy_percent' => 73,
            'actual_temp' => 22,
            'color' => '#065f46' // Hijau
        ]);

        StorageArea::create([
            'name' => 'Ingredient Coolroom',
            'temp_range' => '5 - 10 C',
            'total_pp' => 51,
            'occupancy_percent' => 93,
            'actual_temp' => 9,
            'color' => '#ea580c' // Oranye
        ]);
    }
}