<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\KasPayment;
use Carbon\Carbon;

class NewKasPaymentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Seeder untuk Kas Payments berdasarkan multi-role system
     */
    public function run(): void
    {
        $kasPayments = [
            // === PAYMENTS UNTUK KAS RECORD ID 1 (Karate Jakarta - Agustus 2025) ===
            [
                'kas_record_id' => 1,
                'member_id' => 1, // Ahmad Rizki Pratama
                'amount' => 25000,
                'month' => 8,
                'year' => 2025,
                'is_paid' => true,
                'paid_date' => Carbon::parse('2025-08-01'),
                'created_at' => Carbon::parse('2025-08-01'),
                'updated_at' => Carbon::parse('2025-08-01'),
            ],
            [
                'kas_record_id' => 1,
                'member_id' => 2, // Siti Nurhaliza Putri
                'amount' => 25000,
                'month' => 8,
                'year' => 2025,
                'is_paid' => true,
                'paid_date' => Carbon::parse('2025-08-01'),
                'created_at' => Carbon::parse('2025-08-01'),
                'updated_at' => Carbon::parse('2025-08-01'),
            ],
            [
                'kas_record_id' => 1,
                'member_id' => 7, // Multi Role User 1
                'amount' => 25000,
                'month' => 8,
                'year' => 2025,
                'is_paid' => true,
                'paid_date' => Carbon::parse('2025-08-01'),
                'created_at' => Carbon::parse('2025-08-01'),
                'updated_at' => Carbon::parse('2025-08-01'),
            ],
            [
                'kas_record_id' => 1,
                'member_id' => 8, // Multi Role User 2
                'amount' => 25000,
                'month' => 8,
                'year' => 2025,
                'is_paid' => true,
                'paid_date' => Carbon::parse('2025-08-01'),
                'created_at' => Carbon::parse('2025-08-01'),
                'updated_at' => Carbon::parse('2025-08-01'),
            ],

            // === PAYMENTS UNTUK KAS RECORD ID 4 (Karate Jakarta - September 2025) ===
            [
                'kas_record_id' => 4,
                'member_id' => 1, // Ahmad Rizki Pratama
                'amount' => 25000,
                'month' => 9,
                'year' => 2025,
                'is_paid' => true,
                'paid_date' => Carbon::parse('2025-09-01'),
                'created_at' => Carbon::parse('2025-09-01'),
                'updated_at' => Carbon::parse('2025-09-01'),
            ],
            [
                'kas_record_id' => 4,
                'member_id' => 2, // Siti Nurhaliza Putri
                'amount' => 25000,
                'month' => 9,
                'year' => 2025,
                'is_paid' => true,
                'paid_date' => Carbon::parse('2025-09-01'),
                'created_at' => Carbon::parse('2025-09-01'),
                'updated_at' => Carbon::parse('2025-09-01'),
            ],
            [
                'kas_record_id' => 4,
                'member_id' => 7, // Multi Role User 1
                'amount' => 25000,
                'month' => 9,
                'year' => 2025,
                'is_paid' => true,
                'paid_date' => Carbon::parse('2025-09-01'),
                'created_at' => Carbon::parse('2025-09-01'),
                'updated_at' => Carbon::parse('2025-09-01'),
            ],
            [
                'kas_record_id' => 4,
                'member_id' => 8, // Multi Role User 2
                'amount' => 25000,
                'month' => 9,
                'year' => 2025,
                'is_paid' => true,
                'paid_date' => Carbon::parse('2025-09-01'),
                'created_at' => Carbon::parse('2025-09-01'),
                'updated_at' => Carbon::parse('2025-09-01'),
            ],

            // === PAYMENTS UNTUK KAS RECORD ID 5 (Paskibra Jakarta - Agustus 2025) ===
            [
                'kas_record_id' => 5,
                'member_id' => 1, // Ahmad Rizki (multi-role di Paskibra juga)
                'amount' => 20000,
                'month' => 8,
                'year' => 2025,
                'is_paid' => true,
                'paid_date' => Carbon::parse('2025-08-01'),
                'created_at' => Carbon::parse('2025-08-01'),
                'updated_at' => Carbon::parse('2025-08-01'),
            ],
            [
                'kas_record_id' => 5,
                'member_id' => 3, // Budi Santoso Wijaya
                'amount' => 20000,
                'month' => 8,
                'year' => 2025,
                'is_paid' => true,
                'paid_date' => Carbon::parse('2025-08-01'),
                'created_at' => Carbon::parse('2025-08-01'),
                'updated_at' => Carbon::parse('2025-08-01'),
            ],
            [
                'kas_record_id' => 5,
                'member_id' => 7, // Multi Role User 1 (juga di Paskibra)
                'amount' => 20000,
                'month' => 8,
                'year' => 2025,
                'is_paid' => true,
                'paid_date' => Carbon::parse('2025-08-01'),
                'created_at' => Carbon::parse('2025-08-01'),
                'updated_at' => Carbon::parse('2025-08-01'),
            ],

            // === PAYMENTS UNTUK KAS RECORD ID 7 (Paskibra Jakarta - September 2025) ===
            [
                'kas_record_id' => 7,
                'member_id' => 1, // Ahmad Rizki
                'amount' => 20000,
                'month' => 9,
                'year' => 2025,
                'is_paid' => true,
                'paid_date' => Carbon::parse('2025-09-01'),
                'created_at' => Carbon::parse('2025-09-01'),
                'updated_at' => Carbon::parse('2025-09-01'),
            ],
            [
                'kas_record_id' => 7,
                'member_id' => 3, // Budi Santoso
                'amount' => 20000,
                'month' => 9,
                'year' => 2025,
                'is_paid' => true,
                'paid_date' => Carbon::parse('2025-09-01'),
                'created_at' => Carbon::parse('2025-09-01'),
                'updated_at' => Carbon::parse('2025-09-01'),
            ],
            [
                'kas_record_id' => 7,
                'member_id' => 7, // Multi Role User 1
                'amount' => 20000,
                'month' => 9,
                'year' => 2025,
                'is_paid' => true,
                'paid_date' => Carbon::parse('2025-09-01'),
                'created_at' => Carbon::parse('2025-09-01'),
                'updated_at' => Carbon::parse('2025-09-01'),
            ],

            // === PAYMENTS UNTUK KAS RECORD ID 8 (Taekwondo Jakarta - Agustus 2025) ===
            [
                'kas_record_id' => 8,
                'member_id' => 2, // Siti Nurhaliza (multi-role di Taekwondo)
                'amount' => 30000,
                'month' => 8,
                'year' => 2025,
                'is_paid' => true,
                'paid_date' => Carbon::parse('2025-08-01'),
                'created_at' => Carbon::parse('2025-08-01'),
                'updated_at' => Carbon::parse('2025-08-01'),
            ],
            [
                'kas_record_id' => 8,
                'member_id' => 4, // Indira Sari Dewi
                'amount' => 30000,
                'month' => 8,
                'year' => 2025,
                'is_paid' => true,
                'paid_date' => Carbon::parse('2025-08-01'),
                'created_at' => Carbon::parse('2025-08-01'),
                'updated_at' => Carbon::parse('2025-08-01'),
            ],
            [
                'kas_record_id' => 8,
                'member_id' => 7, // Multi Role User 1 (juga di Taekwondo)
                'amount' => 30000,
                'month' => 8,
                'year' => 2025,
                'is_paid' => true,
                'paid_date' => Carbon::parse('2025-08-01'),
                'created_at' => Carbon::parse('2025-08-01'),
                'updated_at' => Carbon::parse('2025-08-01'),
            ],

            // === PAYMENTS UNTUK KAS RECORD ID 10 (Basket Bandung - Agustus 2025) ===
            [
                'kas_record_id' => 10,
                'member_id' => 5, // Eko Prasetyo Hadi
                'amount' => 35000,
                'month' => 8,
                'year' => 2025,
                'is_paid' => true,
                'paid_date' => Carbon::parse('2025-08-01'),
                'created_at' => Carbon::parse('2025-08-01'),
                'updated_at' => Carbon::parse('2025-08-01'),
            ],
            [
                'kas_record_id' => 10,
                'member_id' => 6, // Rina Melati Sari
                'amount' => 35000,
                'month' => 8,
                'year' => 2025,
                'is_paid' => true,
                'paid_date' => Carbon::parse('2025-08-01'),
                'created_at' => Carbon::parse('2025-08-01'),
                'updated_at' => Carbon::parse('2025-08-01'),
            ],

            // === PAYMENTS UNTUK KAS RECORD ID 13 (Futsal Bandung - Agustus 2025) ===
            [
                'kas_record_id' => 13,
                'member_id' => 5, // Eko Prasetyo (multi-role di Futsal juga)
                'amount' => 28000,
                'month' => 8,
                'year' => 2025,
                'is_paid' => true,
                'paid_date' => Carbon::parse('2025-08-01'),
                'created_at' => Carbon::parse('2025-08-01'),
                'updated_at' => Carbon::parse('2025-08-01'),
            ],

            // === PAYMENTS UNTUK SEPTEMBER 2025 ===
            [
                'kas_record_id' => 15, // Taekwondo Jakarta September
                'member_id' => 2, // Siti Nurhaliza
                'amount' => 30000,
                'month' => 9,
                'year' => 2025,
                'is_paid' => true,
                'paid_date' => Carbon::parse('2025-09-01'),
                'created_at' => Carbon::parse('2025-09-01'),
                'updated_at' => Carbon::parse('2025-09-01'),
            ],
            [
                'kas_record_id' => 15, // Taekwondo Jakarta September
                'member_id' => 4, // Indira Sari
                'amount' => 30000,
                'month' => 9,
                'year' => 2025,
                'is_paid' => true,
                'paid_date' => Carbon::parse('2025-09-01'),
                'created_at' => Carbon::parse('2025-09-01'),
                'updated_at' => Carbon::parse('2025-09-01'),
            ],
            [
                'kas_record_id' => 16, // Basket Bandung September
                'member_id' => 5, // Eko Prasetyo
                'amount' => 35000,
                'month' => 9,
                'year' => 2025,
                'is_paid' => true,
                'paid_date' => Carbon::parse('2025-09-01'),
                'created_at' => Carbon::parse('2025-09-01'),
                'updated_at' => Carbon::parse('2025-09-01'),
            ],
            [
                'kas_record_id' => 16, // Basket Bandung September
                'member_id' => 6, // Rina Melati
                'amount' => 35000,
                'month' => 9,
                'year' => 2025,
                'is_paid' => true,
                'paid_date' => Carbon::parse('2025-09-01'),
                'created_at' => Carbon::parse('2025-09-01'),
                'updated_at' => Carbon::parse('2025-09-01'),
            ],
            [
                'kas_record_id' => 17, // Futsal Bandung September
                'member_id' => 5, // Eko Prasetyo (multi-role)
                'amount' => 28000,
                'month' => 9,
                'year' => 2025,
                'is_paid' => true,
                'paid_date' => Carbon::parse('2025-09-01'),
                'created_at' => Carbon::parse('2025-09-01'),
                'updated_at' => Carbon::parse('2025-09-01'),
            ],

            // === CONTOH PEMBAYARAN BELUM LUNAS ===
            [
                'kas_record_id' => 4, // Karate Jakarta September
                'member_id' => 3, // Budi Santoso Wijaya - belum bayar
                'amount' => 25000,
                'month' => 9,
                'year' => 2025,
                'is_paid' => false,
                'paid_date' => null,
                'created_at' => Carbon::parse('2025-09-01'),
                'updated_at' => Carbon::parse('2025-09-01'),
            ],
            [
                'kas_record_id' => 7, // Paskibra Jakarta September
                'member_id' => 8, // Member yang terlambat bayar
                'amount' => 20000,
                'month' => 9,
                'year' => 2025,
                'is_paid' => false,
                'paid_date' => null,
                'created_at' => Carbon::parse('2025-09-01'),
                'updated_at' => Carbon::parse('2025-09-01'),
            ],
        ];

        foreach ($kasPayments as $payment) {
            KasPayment::create($payment);
        }

        $this->command->info('✅ Kas Payments seeded successfully! Created ' . count($kasPayments) . ' payment records.');
        $this->command->line('');
        $this->command->info('💳 Payment Status Summary:');
        $this->command->line('✅ Paid: ' . collect($kasPayments)->where('is_paid', true)->count() . ' payments');
        $this->command->line('⏳ Unpaid: ' . collect($kasPayments)->where('is_paid', false)->count() . ' payments');
        $this->command->line('');
        $this->command->info('🔄 Multi-role Payment Examples:');
        $this->command->line('👤 Ahmad Rizki: Pays for Karate + Paskibra');
        $this->command->line('👤 Siti Nurhaliza: Pays for Karate + Taekwondo');
        $this->command->line('👤 Eko Prasetyo: Pays for Basket + Futsal');
        $this->command->line('👤 Multi Role User 1: Pays for 3 different eschools');
    }
}
