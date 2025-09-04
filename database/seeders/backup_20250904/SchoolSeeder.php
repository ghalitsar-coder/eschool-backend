<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\School;

class SchoolSeeder extends Seeder
{
    public function run(): void
    {
        $schools = [
            [
                'name' => 'SMA Negeri 1 Jakarta',
                'address' => 'Jl. Merdeka No. 123, Jakarta Pusat',
                'phone' => '021-12345678',
                'email' => 'info@sman1jakarta.sch.id',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'SMA Negeri 2 Jakarta', 
                'address' => 'Jl. Kemerdekaan No. 456, Jakarta Selatan',
                'phone' => '021-87654321',
                'email' => 'info@sman2jakarta.sch.id',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'SMA Negeri 3 Jakarta',
                'address' => 'Jl. Pemuda No. 789, Jakarta Timur',
                'phone' => '021-11223344',
                'email' => 'info@sman3jakarta.sch.id',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($schools as $school) {
            School::create($school);
        }
    }
}