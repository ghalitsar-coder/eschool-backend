<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\KasPayment;
use App\Models\Member;
use App\Models\KasRecord;
use Carbon\Carbon;

class KasPaymentSeeder extends Seeder
{
    public function run(): void
    {
        $members = Member::with('eschool')->get();
        $kasRecords = KasRecord::where('type', 'income')->get();

        if ($members->isEmpty() || $kasRecords->isEmpty()) {
            $this->command->warn('Make sure Members and Income Kas Records exist before running this seeder.');
            return;
        }

        $kasPayments = [];
        $currentYear = Carbon::now()->year;

        foreach ($members as $member) {
            // Generate payments untuk 6 bulan terakhir
            for ($monthOffset = 0; $monthOffset < 6; $monthOffset++) {
                $targetDate = Carbon::now()->subMonths($monthOffset);
                $month = $targetDate->month;
                $year = $targetDate->year;

                // Cari kas record yang sesuai dengan eschool
                $relatedKasRecord = $kasRecords->where('eschool_id', $member->eschool_id)->random();

                $isPaid = rand(0, 10) > 4; // 60% sudah bayar, 40% belum untuk testing MVP
                $paidDate = $isPaid ? $targetDate->addDays(rand(1, 15)) : null;

                $kasPayments[] = [
                    'member_id' => $member->id,
                    'kas_record_id' => $relatedKasRecord->id,
                    'amount' => $member->eschool->monthly_kas_amount,
                    'month' => $month,
                    'year' => $year,
                    'is_paid' => $isPaid,
                    'paid_date' => $paidDate,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        foreach ($kasPayments as $payment) {
            KasPayment::create($payment);
        }
    }
}