<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\KasPayment;
use App\Models\Member;
use App\Models\KasRecord;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB; // <- ini yang benar

class KasPaymentSeeder extends Seeder
{
    public function run(): void
    {
        // $members = Member::with('eschools')->get();
        // $kasRecords = KasRecord::where('type', 'income')->get();

        // if ($members->isEmpty() || $kasRecords->isEmpty()) {
        //     $this->command->warn('Make sure Members and Income Kas Records exist before running this seeder.');
        //     return;
        // }

        // $kasPayments = [];
        // $paymentsCreated = 0;

        // foreach ($members as $member) {
        //     // Get all eschools for this member (many-to-many relationship)
        //     $eschools = $member->eschools;
            
        //     foreach ($eschools as $eschool) {
        //         // Generate payments for last 6 months for each eschool
        //         for ($monthOffset = 0; $monthOffset < 6; $monthOffset++) {
        //             $targetDate = Carbon::now()->subMonths($monthOffset);
        //             $month = $targetDate->month;
        //             $year = $targetDate->year;

        //             // Find kas record for this eschool and month
        //             $relatedKasRecord = $kasRecords->where('eschool_id', $eschool->id)
        //                                           ->filter(function ($record) use ($month, $year) {
        //                                               return $record->date->month == $month && $record->date->year == $year;
        //                                           })
        //                                           ->first();

        //             // If no exact match, get any kas record for this eschool
        //             if (!$relatedKasRecord) {
        //                 $relatedKasRecord = $kasRecords->where('eschool_id', $eschool->id)->first();
        //             }

        //             if ($relatedKasRecord) {
        //                 // Most payments are made (85% payment rate for realistic data)
        //                 $isPaid = rand(0, 100) > 15;
        //                 $paidDate = $isPaid ? $targetDate->copy()->addDays(rand(1, 20)) : null;

        //                 $kasPayments[] = [
        //                     'member_id' => $member->id,
        //                     'kas_record_id' => $relatedKasRecord->id,
        //                     'amount' => $eschool->monthly_kas_amount,
        //                     'month' => $month,
        //                     'year' => $year,
        //                     'is_paid' => $isPaid,
        //                     'paid_date' => $paidDate,
        //                     'created_at' => $targetDate,
        //                     'updated_at' => $paidDate ?? $targetDate,
        //                 ];
                        
        //                 $paymentsCreated++;
        //             }
        //         }
        //     }
        // }

        // foreach ($kasPayments as $payment) {
        //     KasPayment::create($payment);
        // }
        
        // $this->command->info("Created {$this->formatNumber($paymentsCreated)} kas payments.");
         $kasPayments = [
            // Pembayaran Kas untuk Eschool Basket (Agustus 2025)
            [
                'member_id' => 1, // Andi
                'kas_record_id' => 1,
                'amount' => 25000,
                'month' => 8,
                'year' => 2025,
                'is_paid' => true,
                'paid_date' => '2025-08-01 10:00:00',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'member_id' => 4, // Budi
                'kas_record_id' => 1,
                'amount' => 25000,
                'month' => 8,
                'year' => 2025,
                'is_paid' => true,
                'paid_date' => '2025-08-02 11:30:00',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'member_id' => 5, // Cici
                'kas_record_id' => 1,
                'amount' => 25000,
                'month' => 8,
                'year' => 2025,
                'is_paid' => true,
                'paid_date' => '2025-08-03 09:45:00',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'member_id' => 6, // Dedi
                'kas_record_id' => 1,
                'amount' => 25000,
                'month' => 8,
                'year' => 2025,
                'is_paid' => false,
                'paid_date' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // Pembayaran Kas untuk Eschool Voli (Agustus 2025)
            [
                'member_id' => 2, // Eka
                'kas_record_id' => 4,
                'amount' => 20000,
                'month' => 8,
                'year' => 2025,
                'is_paid' => true,
                'paid_date' => '2025-08-01 11:00:00',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'member_id' => 7, // Fani
                'kas_record_id' => 4,
                'amount' => 20000,
                'month' => 8,
                'year' => 2025,
                'is_paid' => true,
                'paid_date' => '2025-08-04 14:15:00',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'member_id' => 8, // Gita
                'kas_record_id' => 4,
                'amount' => 20000,
                'month' => 8,
                'year' => 2025,
                'is_paid' => true,
                'paid_date' => '2025-08-05 16:30:00',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'member_id' => 1, // Andi (ikut voli sebagai member)
                'kas_record_id' => 4,
                'amount' => 20000,
                'month' => 8,
                'year' => 2025,
                'is_paid' => false,
                'paid_date' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // Pembayaran Kas untuk Eschool Lukis (Agustus 2025)
            [
                'member_id' => 3, // Hani
                'kas_record_id' => 6,
                'amount' => 30000,
                'month' => 8,
                'year' => 2025,
                'is_paid' => true,
                'paid_date' => '2025-08-01 12:00:00',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'member_id' => 9, // Iko
                'kas_record_id' => 6,
                'amount' => 30000,
                'month' => 8,
                'year' => 2025,
                'is_paid' => true,
                'paid_date' => '2025-08-06 10:45:00',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'member_id' => 4, // Budi (ikut lukis sebagai member)
                'kas_record_id' => 6,
                'amount' => 30000,
                'month' => 8,
                'year' => 2025,
                'is_paid' => false,
                'paid_date' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // Pembayaran Kas untuk Eschool Matematika - Sekolah 2 (Agustus 2025)
            [
                'member_id' => 10, // Rina Permata
                'kas_record_id' => 7,
                'amount' => 15000,
                'month' => 8,
                'year' => 2025,
                'is_paid' => true,
                'paid_date' => '2025-08-01 13:00:00',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'member_id' => 12, // Lisa
                'kas_record_id' => 7,
                'amount' => 15000,
                'month' => 8,
                'year' => 2025,
                'is_paid' => true,
                'paid_date' => '2025-08-08 15:20:00',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'member_id' => 13, // Raka
                'kas_record_id' => 7,
                'amount' => 15000,
                'month' => 8,
                'year' => 2025,
                'is_paid' => false,
                'paid_date' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // Pembayaran Kas untuk Eschool Teater - Sekolah 2 (Agustus 2025)
            [
                'member_id' => 11, // Doni
                'kas_record_id' => 9,
                'amount' => 35000,
                'month' => 8,
                'year' => 2025,
                'is_paid' => true,
                'paid_date' => '2025-08-01 14:00:00',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'member_id' => 12, // Lisa (ikut teater juga)
                'kas_record_id' => 9,
                'amount' => 35000,
                'month' => 8,
                'year' => 2025,
                'is_paid' => true,
                'paid_date' => '2025-08-09 13:10:00',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'member_id' => 10, // Rina (ikut teater sebagai member)
                'kas_record_id' => 9,
                'amount' => 35000,
                'month' => 8,
                'year' => 2025,
                'is_paid' => false,
                'paid_date' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('kas_payments')->insert($kasPayments);
        //    foreach ($eschools as $eschool) {
        //     Eschool::create($eschool);
        // }
        // KasPayment::create($kasPayments);
    }

    private function formatNumber($number) {
        return number_format($number, 0, ',', '.');
    }
}