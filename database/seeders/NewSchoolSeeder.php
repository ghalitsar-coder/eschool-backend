<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\School;

class NewSchoolSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Seeder untuk data sekolah berdasarkan multi-role system
     */
    public function run(): void
    {
        $schools = [
            [
                'name' => 'SMA Negeri 1 Jakarta',
                'address' => 'Jl. Sudirman No. 1, Jakarta Pusat',
                'phone' => '021-1234567',
                'email' => 'info@sman1jakarta.sch.id',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'SMA Negeri 2 Bandung',
                'address' => 'Jl. Braga No. 45, Bandung',
                'phone' => '022-7654321',
                'email' => 'info@sman2bandung.sch.id',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'SMA Negeri 3 Surabaya',
                'address' => 'Jl. Pemuda No. 88, Surabaya',
                'phone' => '031-9876543',
                'email' => 'info@sman3surabaya.sch.id',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($schools as $school) {
            School::create($school);
        }

        $this->command->info('✅ Schools seeded successfully! Created ' . count($schools) . ' schools.');
    }
}
