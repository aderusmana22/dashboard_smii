<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\SafetyBoard;
use Carbon\Carbon;

class SafetyBoardSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Membuat data awal hanya jika tabel masih kosong
        if (SafetyBoard::count() == 0) {
            SafetyBoard::create([
                'last_accident_date' => Carbon::parse('2023-10-24'),
                'record_days_without_accident' => 3103,
                'marquee_text' => 'UTAMAKAN KESELAMATAN DAN KESEHATAN KERJA *** SAFETY FIRST - BE CAREFUL *** KERJA CERDAS, KERJA SELAMAT'
            ]);
        }
    }
}