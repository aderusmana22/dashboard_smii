<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class NamaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $staffUser = User::firstOrCreate([
            'email' => 'kinarimono@gmail.com',
        ], [
            'name' => 'yoga',
            'username' => 'yoga',
            'nik' => 'AG6969',
            'email' => 'kinarimono@gmail.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
            'position_id' => 3,
            'department_id' => 1,
        ]);
    }
}
