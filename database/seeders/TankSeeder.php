<?php

namespace Database\Seeders;

use App\Models\Tank;
use Illuminate\Database\Seeder;

class TankSeeder extends Seeder
{
    public function run(): void
    {
        $tanks = [
            ['tank_code' => '80T9', 'oil_code' => 'TQA829', 'description' => 'CFAD', 'capacity_kg' => 40000, 'color_hex' => '#ef4444', 'formula_type' => null],
            ['tank_code' => '80T10', 'oil_code' => 'ZYA801', 'description' => 'PFAD', 'capacity_kg' => 77000, 'color_hex' => '#f97316', 'formula_type' => null],
            ['tank_code' => '80T13', 'oil_code' => '101-007', 'description' => 'RBDPO ( T )', 'capacity_kg' => 150000, 'color_hex' => '#22c55e', 'formula_type' => null],
            ['tank_code' => '80T20', 'oil_code' => '101-007', 'description' => 'RBDPO ( T )', 'capacity_kg' => 185000, 'color_hex' => '#16a34a', 'formula_type' => null],
            ['tank_code' => '80T21', 'oil_code' => '101-007', 'description' => 'RBDPO ( T )', 'capacity_kg' => 190000, 'color_hex' => '#15803d', 'formula_type' => null],
            ['tank_code' => '80T22', 'oil_code' => '101-007', 'description' => 'RBDPO ( T )', 'capacity_kg' => 190000, 'color_hex' => '#14532d', 'formula_type' => null],
            
            ['tank_code' => '80T12', 'oil_code' => '102-013', 'description' => 'OLEIN', 'capacity_kg' => 70000, 'color_hex' => '#eab308', 'formula_type' => 'OLEIN'],
            ['tank_code' => '80T16', 'oil_code' => '102-013', 'description' => 'PE BULK', 'capacity_kg' => 172000, 'color_hex' => '#3b82f6', 'formula_type' => 'PE_BULK'],
            ['tank_code' => '80T17', 'oil_code' => '101-012', 'description' => 'PE (T)', 'capacity_kg' => 172000, 'color_hex' => '#a855f7', 'formula_type' => 'PE_BULK'],
        ];

        foreach ($tanks as $tankData) {
            Tank::updateOrCreate(['tank_code' => $tankData['tank_code']], $tankData);
        }
    }
}