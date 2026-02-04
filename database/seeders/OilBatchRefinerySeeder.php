<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\OilBatchRefineryTank;

class OilBatchRefinerySeeder extends Seeder
{
    public function run()
    {
        $tanks = [
            // 1. Hydro Group
            [
                'name' => 'Hydro',
                'code' => 'HYDRO',
                'group_name' => 'Hydro',
                'capacity_kg' => 10000,
            ],
            
            // 2. N.W.B Group
            [
                'name' => 'N.W.B.',
                'code' => 'NWB',
                'group_name' => 'N.W.B',
                'capacity_kg' => 10000,
            ],

            // 3. Deodorizer Group
            [
                'name' => 'Deodorizer 1',
                'code' => 'DEO1',
                'group_name' => 'Deodorizer',
                'capacity_kg' => 10000,
            ],
            [
                'name' => 'Deodorizer 2',
                'code' => 'DEO2',
                'group_name' => 'Deodorizer',
                'capacity_kg' => 10000,
            ],

            // 4. Drop Tank Group
            [
                'name' => 'Drop Tank 1',
                'code' => 'DT1',
                'group_name' => 'Drop Tank',
                'capacity_kg' => 10000,
            ],
            [
                'name' => 'Drop Tank 2',
                'code' => 'DT2',
                'group_name' => 'Drop Tank',
                'capacity_kg' => 10000,
            ],
            [
                'name' => 'Drop Tank 3',
                'code' => 'DT3',
                'group_name' => 'Drop Tank',
                'capacity_kg' => 10000,
            ],
            [
                'name' => 'Drop Tank 4',
                'code' => 'DT4',
                'group_name' => 'Drop Tank',
                'capacity_kg' => 10000,
            ],

            // 5. Wead Tank Group
            [
                'name' => 'Wead Tank',
                'code' => 'WEAD',
                'group_name' => 'Wead Tank',
                'capacity_kg' => 10000,
            ],

            // 6. Crystalizer Group
            [
                'name' => 'Crystalizer 1',
                'code' => 'CRY1',
                'group_name' => 'Crystalizer',
                'capacity_kg' => 40000,
            ],
            [
                'name' => 'Crystalizer 2',
                'code' => 'CRY2',
                'group_name' => 'Crystalizer',
                'capacity_kg' => 40000,
            ],
            [
                'name' => 'Crystalizer 3',
                'code' => 'CRY3',
                'group_name' => 'Crystalizer',
                'capacity_kg' => 40000,
            ],
            [
                'name' => 'Crystalizer 4',
                'code' => 'CRY4',
                'group_name' => 'Crystalizer',
                'capacity_kg' => 40000,
            ],

            // 7. SX Tank Group (Storage S12, S13, S14)
            [
                'name' => 'S12 Tank',
                'code' => 'S12',
                'group_name' => 'SX Tank',
                'capacity_kg' => 5000,
            ],
            [
                'name' => 'S13 Tank',
                'code' => 'S13',
                'group_name' => 'SX Tank',
                'capacity_kg' => 13000,
            ],
            [
                'name' => 'S14 Tank',
                'code' => 'S14',
                'group_name' => 'SX Tank',
                'capacity_kg' => 5000,
            ],
        ];

        foreach ($tanks as $index => $tankData) {
            OilBatchRefineryTank::updateOrCreate(
                ['code' => $tankData['code']], // Kunci unik
                [
                    'name' => $tankData['name'],
                    'group_name' => $tankData['group_name'],
                    'capacity_kg' => $tankData['capacity_kg'],
                    'sort_order' => $index + 1, // Urutkan sesuai array
                    'is_active' => true
                ]
            );
        }
    }
}