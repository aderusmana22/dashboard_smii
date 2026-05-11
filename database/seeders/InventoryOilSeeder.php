<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\OIL\Inventory\MasterOilTank;
use App\Models\OIL\Inventory\InventoryOilStock;
use App\Models\OIL\Inventory\InventoryOilInOut;
use Carbon\Carbon;

class InventoryOilSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // 1. Bersihkan Data Lama (Agar aman jika di-run berkali-kali)
        DB::connection('mysql_oil')->statement('SET FOREIGN_KEY_CHECKS=0;');
        MasterOilTank::truncate();
        InventoryOilStock::truncate();
        InventoryOilInOut::truncate();
        DB::connection('mysql_oil')->statement('SET FOREIGN_KEY_CHECKS=1;');

        $now = Carbon::now();

        // ==========================================
        // 2. SEEDER: MASTER OIL TANK
        // ==========================================
        $tanks = [
            ['tank_name' => 'TK-01', 'capacity' => 50000, 'tank_description' => 'Tangki CPO Utama'],
            ['tank_name' => 'TK-02', 'capacity' => 80000, 'tank_description' => 'Tangki Campuran'],
            ['tank_name' => 'TK-03', 'capacity' => 100000, 'tank_description' => 'Tangki Olein Premium'],
            ['tank_name' => 'TK-04', 'capacity' => 60000, 'tank_description' => 'Tangki Stearin'],
            ['tank_name' => 'TK-05', 'capacity' => 45000, 'tank_description' => 'Tangki Cadangan'],
        ];

        foreach ($tanks as $tank) {
            MasterOilTank::create($tank);
        }

        // ==========================================
        // 3. SEEDER: INVENTORY OIL STOCK (SNAPSHOT)
        // ==========================================
        // Skenario: TK-02 memiliki 2 jenis minyak (CPO-PREMIUM dan CPO-STD)
        $stocks = [
            ['ld_part' => 'CPO-RAW', 'pt_desc1' => 'Crude Palm Oil', 'ld_qty_oh' => 35000, 'ld_loc' => 'TK-01', 'pt_um' => 'KG'],
            ['ld_part' => 'CPO-PREMIUM', 'pt_desc1' => 'CPO Grade A', 'ld_qty_oh' => 40000, 'ld_loc' => 'TK-02', 'pt_um' => 'KG'],
            ['ld_part' => 'CPO-STD', 'pt_desc1' => 'CPO Grade B', 'ld_qty_oh' => 25000, 'ld_loc' => 'TK-02', 'pt_um' => 'KG'], // Tangki sama (TK-02)['ld_part' => 'RBD-OLEIN', 'pt_desc1' => 'Refined Olein', 'ld_qty_oh' => 85000, 'ld_loc' => 'TK-03', 'pt_um' => 'KG'],['ld_part' => 'RBD-STEARIN', 'pt_desc1' => 'Refined Stearin', 'ld_qty_oh' => 12000, 'ld_loc' => 'TK-04', 'pt_um' => 'KG'],['ld_part' => 'PFAD', 'pt_desc1' => 'Palm Fatty Acid', 'ld_qty_oh' => 5000, 'ld_loc' => 'TK-05', 'pt_um' => 'KG'],
        ];

        foreach ($stocks as $stock) {
            InventoryOilStock::create($stock);
        }

        // ==========================================
        // 4. SEEDER: INVENTORY OIL IN & OUT (LOG TRANSAKSI)
        // ==========================================
        $suppliers = [
            'SUP-001' => 'PT. Wilmar Nabati',
            'SUP-002' => 'PT. Sinar Mas Agro',
            'SUP-003' => 'PT. Astra Agro Lestari'
        ];

        $items = [
            'CPO-RAW' => 'Crude Palm Oil',
            'CPO-PREMIUM' => 'CPO Grade A',
            'RBD-OLEIN' => 'Refined Olein',
            'RBD-STEARIN' => 'Refined Stearin'
        ];

        // Buat 30 Data Dummy (15 IN, 15 OUT) yang tersebar di bulan ini
        for ($i = 1; $i <= 30; $i++) {
            $isIncoming = $i % 2 !== 0; // Ganjil = IN, Genap = OUT
            $randomDate = $now->copy()->subDays(rand(0, 10))->subHours(rand(1, 12));

            if ($isIncoming) {
                // LOGIKA IN: Harus ada Supplier
                $supCode = array_rand($suppliers);
                $itemCode = array_rand($items);

                InventoryOilInOut::create([
                    'tr_trnbr' => 'TRX-IN-100' . $i,
                    'tr_part' => $itemCode,
                    'tr_part_name' => $items[$itemCode],
                    'tr_addr' => $supCode,
                    'tr_addr_name' => $suppliers[$supCode],
                    'tr_date' => $randomDate->toDateString(),
                    'tr_qty_chg' => rand(5000, 20000), // 5 sampai 20 Ton
                    'tr_um' => 'KG',
                    'type' => 'IN',
                    'created_at' => $randomDate,
                    'updated_at' => $randomDate,
                ]);
            } else {
                // LOGIKA OUT: Supplier kosong (Internal Dispatch)
                $itemCode = array_rand($items);

                InventoryOilInOut::create([
                    'tr_trnbr' => 'TRX-OUT-200' . $i,
                    'tr_part' => $itemCode,
                    'tr_part_name' => $items[$itemCode],
                    'tr_addr' => null,
                    'tr_addr_name' => null,
                    'tr_date' => $randomDate->toDateString(),
                    'tr_qty_chg' => rand(1000, 8000), // 1 sampai 8 Ton
                    'tr_um' => 'KG',
                    'type' => 'OUT',
                    'created_at' => $randomDate,
                    'updated_at' => $randomDate,
                ]);
            }
        }
    }
}