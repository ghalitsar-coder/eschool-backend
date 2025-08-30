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
            [
                'name' => 'SMA Negeri 7 Semarang',
                'address' => 'Jl. Pemuda No. 150, Semarang',
                'phone' => '024-3558921',
                'email' => 'admin@sman7semarang.sch.id',
            ],
            [
                'name' => 'SMA Negeri 9 Palembang',
                'address' => 'Jl. Jendral Sudirman No. 88, Palembang',
                'phone' => '0711-377215',
                'email' => 'info@sman9palembang.sch.id',
            ],
            [
                'name' => 'SMA Negeri 11 Makassar',
                'address' => 'Jl. Perintis Kemerdekaan No. 100, Makassar',
                'phone' => '0411-582347',
                'email' => 'admin@sman11makassar.sch.id',
            ],
            [
                'name' => 'SMA Negeri 4 Denpasar',
                'address' => 'Jl. WR Supratman No. 30, Denpasar',
                'phone' => '0361-234567',
                'email' => 'contact@sman4denpasar.sch.id',
            ],
            [
                'name' => 'SMA Negeri 6 Balikpapan',
                'address' => 'Jl. Jenderal Sudirman No. 35, Balikpapan',
                'phone' => '0542-765432',
                'email' => 'info@sman6balikpapan.sch.id',
            ],
        ];

        foreach ($schools as $school) {
            School::create($school);
        }
    }
}