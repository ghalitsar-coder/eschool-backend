<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AdditionalEschoolsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Additional eschools for existing schools
        $eschools = [
            // Additional eschool for SMA Negeri 1 Jakarta (school_id: 1)
            [
                'school_id' => 1,
                'coordinator_id' => 4,
                'treasurer_id' => 1,
                'name' => 'Kelas XI IPA 2',
                'description' => 'Ekstrakurikuler untuk siswa kelas XI IPA 2',
                'monthly_kas_amount' => 20000,
                'schedule_days' => json_encode(['tuesday', 'thursday']),
                'total_schedule_days' => 2,
                'is_active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // Additional eschool for SMA Negeri 2 Bandung (school_id: 2)
            [
                'school_id' => 2,
                'coordinator_id' => 5,
                'treasurer_id' => 2,
                'name' => 'Kelas X IPS 1',
                'description' => 'Ekstrakurikuler untuk siswa kelas X IPS 1',
                'monthly_kas_amount' => 15000,
                'schedule_days' => json_encode(['monday', 'wednesday']),
                'total_schedule_days' => 2,
                'is_active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // Additional eschool for SMA Negeri 3 Surabaya (school_id: 3)
            [
                'school_id' => 3,
                'coordinator_id' => 4,
                'treasurer_id' => 3,
                'name' => 'Kelas XI MIPA 4',
                'description' => 'Ekstrakurikuler untuk siswa kelas XI MIPA 4',
                'monthly_kas_amount' => 20000,
                'schedule_days' => json_encode(['tuesday', 'friday']),
                'total_schedule_days' => 2,
                'is_active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // New eschool for SMA Negeri 1 Yogyakarta (school_id: 4)
            [
                'school_id' => 4,
                'coordinator_id' => 4,
                'treasurer_id' => 1,
                'name' => 'Kelas X Bahasa',
                'description' => 'Ekstrakurikuler untuk siswa kelas X Bahasa',
                'monthly_kas_amount' => 15000,
                'schedule_days' => json_encode(['monday', 'thursday']),
                'total_schedule_days' => 2,
                'is_active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // New eschool for SMA Negeri 5 Medan (school_id: 5)
            [
                'school_id' => 5,
                'coordinator_id' => 5,
                'treasurer_id' => 2,
                'name' => 'Kelas XI IPA 3',
                'description' => 'Ekstrakurikuler untuk siswa kelas XI IPA 3',
                'monthly_kas_amount' => 20000,
                'schedule_days' => json_encode(['tuesday', 'friday']),
                'total_schedule_days' => 2,
                'is_active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('eschools')->insert($eschools);
    }
}
