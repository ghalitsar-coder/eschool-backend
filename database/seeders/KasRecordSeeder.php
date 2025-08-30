<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\KasRecord;
use App\Models\Eschool;
use App\Models\User;
use Carbon\Carbon;

class KasRecordSeeder extends Seeder
{
    public function run(): void
    {
        $eschools = Eschool::all();
        $recorders = User::whereIn('role', ['bendahara', 'koordinator', 'staff'])->get();

        if ($eschools->isEmpty() || $recorders->isEmpty()) {
            $this->command->warn('Make sure Eschools and Recorders exist before running this seeder.');
            return;
        }

        $kasRecords = [];

        foreach ($eschools as $eschool) {
            // Income records (kas masuk) - Generate records for last 6 months
            $months = [];
            for ($m = 0; $m < 6; $m++) {
                $months[] = Carbon::now()->subMonths($m);
            }
            
            foreach ($months as $month) {
                // Create income record for monthly kas collection
                $kasRecords[] = [
                    'eschool_id' => $eschool->id,
                    'recorder_id' => $recorders->random()->id,
                    'type' => 'income',
                    'amount' => $eschool->monthly_kas_amount * rand(15, 25), // 15-25 members paying
                    'description' => 'Pembayaran iuran bulanan ' . $month->format('F Y') . ' - ' . $eschool->name,
                    'date' => $month->copy()->addDays(rand(1, 10)), // Collection happens within 10 days of month start
                    'created_at' => $month,
                    'updated_at' => $month,
                ];
            }

            // Expense records (kas keluar) - More realistic expense descriptions
            $expenses = [
                'Pembelian alat tulis dan perlengkapan kegiatan',
                'Biaya konsumsi rapat bulanan ekstrakurikuler',
                'Transportasi untuk kegiatan lomba antar sekolah',
                'Pembelian piala dan hadiah untuk lomba internal',
                'Biaya fotokopi materi pelatihan',
                'Sewa ruangan untuk kegiatan khusus',
                'Pembelian bahan baku untuk proyek kreatif',
                'Biaya pendaftaran lomba tingkat kota',
                'Pembelian seragam atau atribut kegiatan',
                'Maintenance peralatan ekstrakurikuler',
                'Biaya dokumentasi kegiatan (foto/video)',
                'Konsumsi acara pentas seni akhir semester'
            ];

            // Generate 8-12 expense records per eschool over the last year
            $expenseCount = rand(8, 12);
            for ($i = 0; $i < $expenseCount; $i++) {
                $expenseDate = Carbon::now()->subDays(rand(30, 365));
                $kasRecords[] = [
                    'eschool_id' => $eschool->id,
                    'recorder_id' => $recorders->random()->id,
                    'type' => 'expense',
                    'amount' => rand(20000, 150000),
                    'description' => $expenses[array_rand($expenses)],
                    'date' => $expenseDate,
                    'created_at' => $expenseDate,
                    'updated_at' => $expenseDate,
                ];
            }
        }

        foreach ($kasRecords as $record) {
            KasRecord::create($record);
        }
    }
}