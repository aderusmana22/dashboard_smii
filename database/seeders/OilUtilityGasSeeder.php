<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\OilUtilityGasMaster;
use App\Models\OilUtilityGasReading;
use Carbon\Carbon;

class OilUtilityGasSeeder extends Seeder
{
    public function run(): void
    {
        // // Bersihkan tabel sebelum isi (Truncate)
        // OilUtilityGasReading::truncate();
        // OilUtilityGasMaster::truncate();
        // DB::table('oil_stock_utility_gas_logs')->truncate();

        // 1. SETUP MASTER DATA
        // Perhatikan 'input_type' => 'stepper' untuk Ammonia
        $mastersData = [
            // HYDROGEN
            ['gas_type' => 'HYDROGEN', 'name' => 'Torpedo #04', 'unit' => 'Bar', 'input_type' => 'number', 'min_limit' => 10, 'max_limit' => 200, 'sort_order' => 1, 'base' => 140],
            ['gas_type' => 'HYDROGEN', 'name' => 'Torpedo #05', 'unit' => 'Bar', 'input_type' => 'number', 'min_limit' => 10, 'max_limit' => 200, 'sort_order' => 2, 'base' => 120],
            // NITROGEN
            ['gas_type' => 'NITROGEN', 'name' => 'Liquid Tank', 'unit' => 'Inch Water', 'input_type' => 'number', 'min_limit' => 65, 'max_limit' => 100, 'sort_order' => 3, 'base' => 78],
            // AMMONIA (Stepper Mode)
            ['gas_type' => 'AMMONIA', 'name' => 'Full Cylinders', 'unit' => 'Cyl', 'input_type' => 'stepper', 'min_limit' => 0, 'max_limit' => 50, 'sort_order' => 4, 'base' => 7],
            ['gas_type' => 'AMMONIA', 'name' => 'Empty Cylinders', 'unit' => 'Cyl', 'input_type' => 'stepper', 'min_limit' => 0, 'max_limit' => 50, 'sort_order' => 5, 'base' => 5],
        ];

        $createdMasters = [];
        foreach($mastersData as $m) {
            $base = $m['base'];
            unset($m['base']); // Hapus key base agar tidak error saat create
            $newMaster = OilUtilityGasMaster::create($m);
            // Simpan ID dan Base value untuk generate dummy
            $createdMasters[] = ['id' => $newMaster->id, 'type' => $m['gas_type'], 'base' => $base];
        }

        // 2. GENERATE DUMMY DATA (1 Tahun Kebelakang)
        $readings = [];
        $today = Carbon::today();

        for ($i = 0; $i < 365; $i++) {
            $date = $today->copy()->subDays($i)->format('Y-m-d');
            
            foreach ($createdMasters as $cm) {
                $val = $cm['base'];
                
                // Algoritma acak sederhana agar grafik terlihat hidup
                if ($cm['type'] == 'HYDROGEN') {
                    $val *= (rand(90, 110) / 100); 
                } elseif ($cm['type'] == 'NITROGEN') {
                    // Simulasi level turun lalu diisi
                    $val = max(50, 100 - ($i % 30)); 
                } elseif ($cm['type'] == 'AMMONIA') {
                    // Integer acak (+- 1)
                    $val = max(0, $val + rand(-1, 1));
                }

                $readings[] = [
                    'master_id' => $cm['id'],
                    'reading_date' => $date,
                    'value' => round($val, 2),
                    'created_by' => 'System Seeder',
                    'created_at' => now(), 
                    'updated_at' => now()
                ];
            }
        }
        
        // Insert massal per 500 baris
        foreach(array_chunk($readings, 500) as $chunk) {
            OilUtilityGasReading::insert($chunk);
        }
    }
}