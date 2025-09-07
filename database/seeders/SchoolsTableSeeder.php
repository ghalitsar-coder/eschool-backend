<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class SchoolsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('schools')->insert([
            [
                'name' => 'Sekolah A',
                'address' => 'Jl. Merdeka No. 123, Jakarta',
                'phone' => '021-1234567',
                'email' => 'sekolah.a@example.com',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Sekolah B',
                'address' => 'Jl. Sudirman No. 456, Bandung',
                'phone' => '022-2345678',
                'email' => 'sekolah.b@example.com',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Sekolah C',
                'address' => 'Jl. Thamrin No. 789, Surabaya',
                'phone' => '031-3456789',
                'email' => 'sekolah.c@example.com',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}