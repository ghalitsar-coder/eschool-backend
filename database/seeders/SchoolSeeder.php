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
                'address' => 'Jl. Budi Kemuliaan No. 6, Jakarta Pusat',
                'phone' => '021-3441621',
                'email' => 'info@sman1jakarta.sch.id',
            ],
            [
                'name' => 'SMA Negeri 2 Bandung',
                'address' => 'Jl. Cihampelas No. 149, Bandung',
                'phone' => '022-2033553',
                'email' => 'admin@sman2bandung.sch.id',
            ],
            [
                'name' => 'SMA Negeri 3 Surabaya',
                'address' => 'Jl. Wijaya Kusuma No. 48, Surabaya',
                'phone' => '031-5677890',
                'email' => 'contact@sman3surabaya.sch.id',
            ],
            [
                'name' => 'SMA Negeri 1 Yogyakarta',
                'address' => 'Jl. HOS Cokroaminoto No. 10, Yogyakarta',
                'phone' => '0274-512038',
                'email' => 'humas@sman1yogya.sch.id',
            ],
            [
                'name' => 'SMA Negeri 5 Medan',
                'address' => 'Jl. T. Imum Lueng Bata, Medan',
                'phone' => '061-7366543',
                'email' => 'info@sman5medan.sch.id',
            ],
        ];

        foreach ($schools as $school) {
            School::create($school);
        }
    }
}