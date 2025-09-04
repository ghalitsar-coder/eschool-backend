<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Eschool;

class NewEschoolSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Seeder untuk Eschool berdasarkan multi-role system
     */
    public function run(): void
    {
        $eschools = [
            // === ESCHOOLS DI JAKARTA (SCHOOL_ID = 1) ===
            [
                'school_id' => 1,
                'coordinator_id' => 4, // Koordinator Karate Jakarta
                'treasurer_id' => 7,   // Bendahara Karate Jakarta
                'name' => 'Karate Jakarta',
                'description' => 'Ekstrakurikuler karate untuk mengembangkan disiplin dan karakter siswa',
                'monthly_kas_amount' => 25000,
                'schedule_days' => ['Senin', 'Rabu', 'Jumat'],
                'total_schedule_days' => 3,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'school_id' => 1,
                'coordinator_id' => 5, // Koordinator Paskibra Jakarta
                'treasurer_id' => 8,   // Bendahara Paskibra Jakarta
                'name' => 'Paskibra Jakarta',
                'description' => 'Pasukan pengibar bendera untuk meningkatkan rasa nasionalisme',
                'monthly_kas_amount' => 20000,
                'schedule_days' => ['Selasa', 'Kamis'],
                'total_schedule_days' => 2,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'school_id' => 1,
                'coordinator_id' => 4, // Koordinator Karate Jakarta (multi-role example)
                'treasurer_id' => 7,   // Bendahara Karate Jakarta (multi-role example)
                'name' => 'Taekwondo Jakarta',
                'description' => 'Seni bela diri taekwondo untuk pengembangan fisik dan mental',
                'monthly_kas_amount' => 30000,
                'schedule_days' => ['Rabu', 'Sabtu'],
                'total_schedule_days' => 2,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // === ESCHOOLS DI BANDUNG (SCHOOL_ID = 2) ===
            [
                'school_id' => 2,
                'coordinator_id' => 6, // Koordinator Basket Bandung
                'treasurer_id' => 9,   // Bendahara Basket Bandung
                'name' => 'Basket Bandung',
                'description' => 'Tim basket sekolah untuk kompetisi dan pengembangan olahraga',
                'monthly_kas_amount' => 35000,
                'schedule_days' => ['Senin', 'Rabu', 'Jumat', 'Sabtu'],
                'total_schedule_days' => 4,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'school_id' => 2,
                'coordinator_id' => 6, // Koordinator Basket Bandung (multi-role)
                'treasurer_id' => 9,   // Bendahara Basket Bandung (multi-role)
                'name' => 'Futsal Bandung',
                'description' => 'Tim futsal sekolah untuk kompetisi antar sekolah',
                'monthly_kas_amount' => 28000,
                'schedule_days' => ['Selasa', 'Kamis', 'Sabtu'],
                'total_schedule_days' => 3,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // === ESCHOOL NON-AKTIF UNTUK TESTING ===
            [
                'school_id' => 1,
                'coordinator_id' => 5, // Koordinator Paskibra Jakarta
                'treasurer_id' => 8,   // Bendahara Paskibra Jakarta
                'name' => 'Drama Club Jakarta (Non-Aktif)',
                'description' => 'Klub drama yang sedang tidak aktif',
                'monthly_kas_amount' => 15000,
                'schedule_days' => ['Jumat'],
                'total_schedule_days' => 1,
                'is_active' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($eschools as $eschoolData) {
            Eschool::create($eschoolData);
        }

        $this->command->info('✅ Eschools seeded successfully! Created ' . count($eschools) . ' eschools.');
        $this->command->line('');
        $this->command->info('📋 Eschools created:');
        $this->command->line('🥋 Karate Jakarta (ID: 1) - Monthly: Rp25,000');
        $this->command->line('🇮🇩 Paskibra Jakarta (ID: 2) - Monthly: Rp20,000');
        $this->command->line('🥋 Taekwondo Jakarta (ID: 3) - Monthly: Rp30,000');
        $this->command->line('🏀 Basket Bandung (ID: 4) - Monthly: Rp35,000');
        $this->command->line('⚽ Futsal Bandung (ID: 5) - Monthly: Rp28,000');
        $this->command->line('🎭 Drama Club Jakarta (ID: 6) - INACTIVE');
    }
}
