<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\KasRecord;
use App\Models\Eschool;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB; // <- ini yang benar
class KasRecordSeeder extends Seeder
{
    public function run(): void
    {
        // $eschools = Eschool::all();
        // $recorders = User::whereIn('role', ['bendahara', 'koordinator', 'staff'])->get();

        // if ($eschools->isEmpty() || $recorders->isEmpty()) {
        //     $this->command->warn('Make sure Eschools and Recorders exist before running this seeder.');
        //     return;
        // }

        // $kasRecords = [];

        // foreach ($eschools as $eschool) {
        //     // Income records (kas masuk) - Generate records for last 6 months
        //     $months = [];
        //     for ($m = 0; $m < 6; $m++) {
        //         $months[] = Carbon::now()->subMonths($m);
        //     }
            
        //     foreach ($months as $month) {
        //         // Create income record for monthly kas collection
        //         $kasRecords[] = [
        //             'eschool_id' => $eschool->id,
        //             'recorder_id' => $recorders->random()->id,
        //             'type' => 'income',
        //             'amount' => $eschool->monthly_kas_amount * rand(15, 25), // 15-25 members paying
        //             'description' => 'Pembayaran iuran bulanan ' . $month->format('F Y') . ' - ' . $eschool->name,
        //             'category' => null, // Income records don't have categories
        //             'date' => $month->copy()->addDays(rand(1, 10)), // Collection happens within 10 days of month start
        //             'created_at' => $month,
        //             'updated_at' => $month,
        //         ];
        //     }

        //     // Expense records (kas keluar) - More realistic expense descriptions with categories
        //     $expenses = [
        //         [
        //             'description' => 'Pembelian alat tulis dan perlengkapan kegiatan',
        //             'category' => 'supplies'
        //         ],
        //         [
        //             'description' => 'Biaya konsumsi rapat bulanan ekstrakurikuler',
        //             'category' => 'events'
        //         ],
        //         [
        //             'description' => 'Transportasi untuk kegiatan lomba antar sekolah',
        //             'category' => 'events'
        //         ],
        //         [
        //             'description' => 'Pembelian piala dan hadiah untuk lomba internal',
        //             'category' => 'events'
        //         ],
        //         [
        //             'description' => 'Biaya fotokopi materi pelatihan',
        //             'category' => 'supplies'
        //         ],
        //         [
        //             'description' => 'Sewa ruangan untuk kegiatan khusus',
        //             'category' => 'events'
        //         ],
        //         [
        //             'description' => 'Pembelian bahan baku untuk proyek kreatif',
        //             'category' => 'supplies'
        //         ],
        //         [
        //             'description' => 'Biaya pendaftaran lomba tingkat kota',
        //             'category' => 'events'
        //         ],
        //         [
        //             'description' => 'Pembelian seragam atau atribut kegiatan',
        //             'category' => 'equipment'
        //         ],
        //         [
        //             'description' => 'Maintenance peralatan ekstrakurikuler',
        //             'category' => 'equipment'
        //         ],
        //         [
        //             'description' => 'Biaya dokumentasi kegiatan (foto/video)',
        //             'category' => 'events'
        //         ],
        //         [
        //             'description' => 'Konsumsi acara pentas seni akhir semester',
        //             'category' => 'events'
        //         ],
        //         [
        //             'description' => 'Pembelian peralatan olahraga baru',
        //             'category' => 'equipment'
        //         ],
        //         [
        //             'description' => 'Biaya cetak sertifikat peserta',
        //             'category' => 'events'
        //         ],
        //         [
        //             'description' => 'Pembelian buku referensi untuk perpustakaan kecil',
        //             'category' => 'supplies'
        //         ]
        //     ];

        //     // Generate 8-12 expense records per eschool over the last year
        //     $expenseCount = rand(8, 12);
        //     for ($i = 0; $i < $expenseCount; $i++) {
        //         $expenseDate = Carbon::now()->subDays(rand(30, 365));
        //         $randomExpense = $expenses[array_rand($expenses)];
                
        //         $kasRecords[] = [
        //             'eschool_id' => $eschool->id,
        //             'recorder_id' => $recorders->random()->id,
        //             'type' => 'expense',
        //             'amount' => rand(20000, 150000),
        //             'description' => $randomExpense['description'],
        //             'category' => $randomExpense['category'],
        //             'date' => $expenseDate,
        //             'created_at' => $expenseDate,
        //             'updated_at' => $expenseDate,
        //         ];
        //     }
        // }

        // foreach ($kasRecords as $record) {
        //     KasRecord::create($record);
        // }
        
        // $this->command->info("Created {$this->formatNumber(count($kasRecords))} kas records.");
         // Kas Records
        $kasRecords = [
            // Kas Records untuk Eschool Basket (ID: 1)
            [
                'eschool_id' => 1,
                'recorder_id' => 6, // Andi (bendahara)
                'type' => 'income',
                'amount' => 100000,
                'description' => 'Kas bulanan anggota bulan Agustus',
                'category' => 'Kas Bulanan',
                'date' => '2025-08-01 10:00:00',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'eschool_id' => 1,
                'recorder_id' => 6, // Andi (bendahara)
                'type' => 'expense',
                'amount' => 50000,
                'description' => 'Pembelian bola basket baru',
                'category' => 'Peralatan',
                'date' => '2025-08-05 14:30:00',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'eschool_id' => 1,
                'recorder_id' => 3, // Pak Joko (koordinator)
                'type' => 'expense',
                'amount' => 25000,
                'description' => 'Biaya transportasi ke pertandingan',
                'category' => 'Transportasi',
                'date' => '2025-08-10 09:15:00',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // Kas Records untuk Eschool Voli (ID: 2)
            [
                'eschool_id' => 2,
                'recorder_id' => 7, // Eka (bendahara)
                'type' => 'income',
                'amount' => 80000,
                'description' => 'Kas bulanan anggota bulan Agustus',
                'category' => 'Kas Bulanan',
                'date' => '2025-08-01 11:00:00',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'eschool_id' => 2,
                'recorder_id' => 7, // Eka (bendahara)
                'type' => 'expense',
                'amount' => 30000,
                'description' => 'Pembelian net voli',
                'category' => 'Peralatan',
                'date' => '2025-08-07 16:20:00',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // Kas Records untuk Eschool Lukis (ID: 3)
            [
                'eschool_id' => 3,
                'recorder_id' => 8, // Hani (bendahara)
                'type' => 'expense',
                'amount' => 45000,
                'description' => 'Pembelian cat dan kuas lukis',
                'category' => 'Peralatan',
                'date' => '2025-08-08 13:45:00',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // Kas Records untuk Eschool Matematika (ID: 4) - Sekolah 2
            [
                'eschool_id' => 4,
                'recorder_id' => 17, // Rina Permata (bendahara)
                'type' => 'income',
                'amount' => 45000,
                'description' => 'Kas bulanan anggota bulan Agustus',
                'category' => 'Kas Bulanan',
                'date' => '2025-08-01 13:00:00',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'eschool_id' => 4,
                'recorder_id' => 17, // Rina Permata (bendahara)
                'type' => 'expense',
                'amount' => 20000,
                'description' => 'Pembelian buku latihan soal olimpiade',
                'category' => 'Buku dan Materi',
                'date' => '2025-08-12 15:30:00',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // Kas Records untuk Eschool Teater (ID: 5) - Sekolah 2
            [
                'eschool_id' => 5,
                'recorder_id' => 18, // Doni (bendahara)
                'type' => 'income',
                'amount' => 105000,
                'description' => 'Kas bulanan anggota bulan Agustus',
                'category' => 'Kas Bulanan',
                'date' => '2025-08-01 14:00:00',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'eschool_id' => 5,
                'recorder_id' => 18, // Doni (bendahara)
                'type' => 'expense',
                'amount' => 60000,
                'description' => 'Pembelian kostum untuk pementasan',
                'category' => 'Kostum dan Properti',
                'date' => '2025-08-15 10:20:00',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('kas_records')->insert($kasRecords);
        // KasRecord::create($kasRecords);

        echo "Kas records seeded.\n";
    }

    private function formatNumber($number) {
        return number_format($number, 0, ',', '.');
    }
}