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
            // Income records (kas masuk)
            for ($i = 0; $i < 10; $i++) {
                $kasRecords[] = [
                    'eschool_id' => $eschool->id,
                    'recorder_id' => $recorders->random()->id,
                    'type' => 'income',
                    'amount' => $eschool->monthly_kas_amount * rand(1, 5),
                    'description' => 'Pembayaran kas bulan ' . Carbon::now()->subMonths(rand(0, 6))->format('F Y'),
                    'date' => Carbon::now()->subDays(rand(1, 180)),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            // Expense records (kas keluar)
            $expenses = [
                'Pembelian alat tulis',
                'Konsumsi rapat',
                'Biaya transportasi kegiatan',
                'Pembelian perlengkapan kelas',
                'Biaya fotokopi',
                'Pembelian hadiah lomba',
                'Biaya dekorasi kelas',
                'Pembelian snack untuk acara'
            ];

            for ($i = 0; $i < 6; $i++) {
                $kasRecords[] = [
                    'eschool_id' => $eschool->id,
                    'recorder_id' => $recorders->random()->id,
                    'type' => 'expense',
                    'amount' => rand(10000, 100000),
                    'description' => $expenses[array_rand($expenses)],
                    'date' => Carbon::now()->subDays(rand(1, 120)),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        foreach ($kasRecords as $record) {
            KasRecord::create($record);
        }
    }
}