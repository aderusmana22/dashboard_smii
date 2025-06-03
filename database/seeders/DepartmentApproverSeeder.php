<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class DepartmentApproverSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
                DB::table('department_approvers')->insert([
            [
                'department_id' => 1,
                'user_nik' => 'AG6969', // Approver NIK
                'status' => 'active',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            // Tambahkan entri tambahan jika diperlukan
            // [
            //     'department_id' => 2,
            //     'user_nik' => 'AG3333',
            //     'status' => 'inactive',
            //     'created_at' => Carbon::now(),
            //     'updated_at' => Carbon::now(),
            // ],
        ]);
    }
}
