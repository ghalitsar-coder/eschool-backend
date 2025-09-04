<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\KasRecord;
use Carbon\Carbon;

class NewKasRecordSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Seeder untuk Kas Records berdasarkan multi-role system
     */
    public function run(): void
    {
        $kasRecords = [
            // === KAS RECORDS UNTUK KARATE JAKARTA (ESCHOOL_ID = 1) ===
            [
                'eschool_id' => 1,
                'recorder_id' => 7, // Bendahara Karate Jakarta
                'type' => 'income',
                'amount' => 200000, // 8 members x 25000
                'description' => 'Kas bulanan Agustus 2025 - Karate Jakarta',
                'category' => 'monthly_kas',
                'date' => Carbon::parse('2025-08-01'),
                'created_at' => Carbon::parse('2025-08-01'),
                'updated_at' => Carbon::parse('2025-08-01'),
            ],
            [
                'eschool_id' => 1,
                'recorder_id' => 7, // Bendahara Karate Jakarta
                'type' => 'expense',
                'amount' => 50000,
                'description' => 'Pembelian peralatan karate (sabuk)',
                'category' => 'equipment',
                'date' => Carbon::parse('2025-08-05'),
                'created_at' => Carbon::parse('2025-08-05'),
                'updated_at' => Carbon::parse('2025-08-05'),
            ],
            [
                'eschool_id' => 1,
                'recorder_id' => 7, // Bendahara Karate Jakarta
                'type' => 'expense',
                'amount' => 30000,
                'description' => 'Konsumsi latihan karate bulan Agustus',
                'category' => 'consumption',
                'date' => Carbon::parse('2025-08-15'),
                'created_at' => Carbon::parse('2025-08-15'),
                'updated_at' => Carbon::parse('2025-08-15'),
            ],
            [
                'eschool_id' => 1,
                'recorder_id' => 7, // Bendahara Karate Jakarta
                'type' => 'income',
                'amount' => 225000, // 9 members x 25000 (new member joined)
                'description' => 'Kas bulanan September 2025 - Karate Jakarta',
                'category' => 'monthly_kas',
                'date' => Carbon::parse('2025-09-01'),
                'created_at' => Carbon::parse('2025-09-01'),
                'updated_at' => Carbon::parse('2025-09-01'),
            ],

            // === KAS RECORDS UNTUK PASKIBRA JAKARTA (ESCHOOL_ID = 2) ===
            [
                'eschool_id' => 2,
                'recorder_id' => 8, // Bendahara Paskibra Jakarta
                'type' => 'income',
                'amount' => 140000, // 7 members x 20000
                'description' => 'Kas bulanan Agustus 2025 - Paskibra Jakarta',
                'category' => 'monthly_kas',
                'date' => Carbon::parse('2025-08-01'),
                'created_at' => Carbon::parse('2025-08-01'),
                'updated_at' => Carbon::parse('2025-08-01'),
            ],
            [
                'eschool_id' => 2,
                'recorder_id' => 8, // Bendahara Paskibra Jakarta
                'type' => 'expense',
                'amount' => 75000,
                'description' => 'Pembelian seragam paskibra baru',
                'category' => 'uniform',
                'date' => Carbon::parse('2025-08-10'),
                'created_at' => Carbon::parse('2025-08-10'),
                'updated_at' => Carbon::parse('2025-08-10'),
            ],
            [
                'eschool_id' => 2,
                'recorder_id' => 17, // Multi Role User 2 (sebagai bendahara)
                'type' => 'income',
                'amount' => 160000, // 8 members x 20000
                'description' => 'Kas bulanan September 2025 - Paskibra Jakarta',
                'category' => 'monthly_kas',
                'date' => Carbon::parse('2025-09-01'),
                'created_at' => Carbon::parse('2025-09-01'),
                'updated_at' => Carbon::parse('2025-09-01'),
            ],

            // === KAS RECORDS UNTUK TAEKWONDO JAKARTA (ESCHOOL_ID = 3) ===
            [
                'eschool_id' => 3,
                'recorder_id' => 7, // Bendahara Karate Jakarta (multi-role)
                'type' => 'income',
                'amount' => 120000, // 4 members x 30000
                'description' => 'Kas bulanan Agustus 2025 - Taekwondo Jakarta',
                'category' => 'monthly_kas',
                'date' => Carbon::parse('2025-08-01'),
                'created_at' => Carbon::parse('2025-08-01'),
                'updated_at' => Carbon::parse('2025-08-01'),
            ],
            [
                'eschool_id' => 3,
                'recorder_id' => 7, // Bendahara Karate Jakarta (multi-role)
                'type' => 'expense',
                'amount' => 45000,
                'description' => 'Pembelian matras taekwondo',
                'category' => 'equipment',
                'date' => Carbon::parse('2025-08-12'),
                'created_at' => Carbon::parse('2025-08-12'),
                'updated_at' => Carbon::parse('2025-08-12'),
            ],

            // === KAS RECORDS UNTUK BASKET BANDUNG (ESCHOOL_ID = 4) ===
            [
                'eschool_id' => 4,
                'recorder_id' => 9, // Bendahara Basket Bandung
                'type' => 'income',
                'amount' => 210000, // 6 members x 35000
                'description' => 'Kas bulanan Agustus 2025 - Basket Bandung',
                'category' => 'monthly_kas',
                'date' => Carbon::parse('2025-08-01'),
                'created_at' => Carbon::parse('2025-08-01'),
                'updated_at' => Carbon::parse('2025-08-01'),
            ],
            [
                'eschool_id' => 4,
                'recorder_id' => 9, // Bendahara Basket Bandung
                'type' => 'expense',
                'amount' => 80000,
                'description' => 'Pembelian bola basket dan jersey',
                'category' => 'equipment',
                'date' => Carbon::parse('2025-08-08'),
                'created_at' => Carbon::parse('2025-08-08'),
                'updated_at' => Carbon::parse('2025-08-08'),
            ],
            [
                'eschool_id' => 4,
                'recorder_id' => 9, // Bendahara Basket Bandung
                'type' => 'expense',
                'amount' => 25000,
                'description' => 'Transport untuk pertandingan basket',
                'category' => 'transportation',
                'date' => Carbon::parse('2025-08-20'),
                'created_at' => Carbon::parse('2025-08-20'),
                'updated_at' => Carbon::parse('2025-08-20'),
            ],

            // === KAS RECORDS UNTUK FUTSAL BANDUNG (ESCHOOL_ID = 5) ===
            [
                'eschool_id' => 5,
                'recorder_id' => 9, // Bendahara Basket Bandung (multi-role)
                'type' => 'income',
                'amount' => 168000, // 6 members x 28000
                'description' => 'Kas bulanan Agustus 2025 - Futsal Bandung',
                'category' => 'monthly_kas',
                'date' => Carbon::parse('2025-08-01'),
                'created_at' => Carbon::parse('2025-08-01'),
                'updated_at' => Carbon::parse('2025-08-01'),
            ],
            [
                'eschool_id' => 5,
                'recorder_id' => 9, // Bendahara Basket Bandung (multi-role)
                'type' => 'expense',
                'amount' => 60000,
                'description' => 'Sewa lapangan futsal untuk latihan',
                'category' => 'facility',
                'date' => Carbon::parse('2025-08-15'),
                'created_at' => Carbon::parse('2025-08-15'),
                'updated_at' => Carbon::parse('2025-08-15'),
            ],

            // === KAS RECORDS TERBARU (SEPTEMBER 2025) ===
            [
                'eschool_id' => 3,
                'recorder_id' => 7, // Bendahara Taekwondo
                'type' => 'income',
                'amount' => 150000, // 5 members x 30000
                'description' => 'Kas bulanan September 2025 - Taekwondo Jakarta',
                'category' => 'monthly_kas',
                'date' => Carbon::parse('2025-09-01'),
                'created_at' => Carbon::parse('2025-09-01'),
                'updated_at' => Carbon::parse('2025-09-01'),
            ],
            [
                'eschool_id' => 4,
                'recorder_id' => 9, // Bendahara Basket Bandung
                'type' => 'income',
                'amount' => 245000, // 7 members x 35000
                'description' => 'Kas bulanan September 2025 - Basket Bandung',
                'category' => 'monthly_kas',
                'date' => Carbon::parse('2025-09-01'),
                'created_at' => Carbon::parse('2025-09-01'),
                'updated_at' => Carbon::parse('2025-09-01'),
            ],
            [
                'eschool_id' => 5,
                'recorder_id' => 9, // Bendahara Futsal Bandung
                'type' => 'income',
                'amount' => 196000, // 7 members x 28000
                'description' => 'Kas bulanan September 2025 - Futsal Bandung',
                'category' => 'monthly_kas',
                'date' => Carbon::parse('2025-09-01'),
                'created_at' => Carbon::parse('2025-09-01'),
                'updated_at' => Carbon::parse('2025-09-01'),
            ],
        ];

        foreach ($kasRecords as $kasRecord) {
            KasRecord::create($kasRecord);
        }

        $this->command->info('✅ Kas Records seeded successfully! Created ' . count($kasRecords) . ' kas records.');
        $this->command->line('');
        $this->command->info('💰 Kas Summary by Eschool:');
        $this->command->line('🥋 Karate Jakarta: Income 425k, Expense 80k, Balance 345k');
        $this->command->line('🇮🇩 Paskibra Jakarta: Income 300k, Expense 75k, Balance 225k');
        $this->command->line('🥋 Taekwondo Jakarta: Income 270k, Expense 45k, Balance 225k');
        $this->command->line('🏀 Basket Bandung: Income 455k, Expense 105k, Balance 350k');
        $this->command->line('⚽ Futsal Bandung: Income 364k, Expense 60k, Balance 304k');
    }
}
