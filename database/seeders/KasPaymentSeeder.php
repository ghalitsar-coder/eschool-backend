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
        $members = Member::with('eschools')->get();
        $kasRecords = KasRecord::where('type', 'income')->get();

        if ($members->isEmpty() || $kasRecords->isEmpty()) {
            $this->command->warn('Make sure Members and Income Kas Records exist before running this seeder.');
            return;
        }

        $kasPayments = [];

        foreach ($members as $member) {
            // Get all eschools for this member (many-to-many relationship)
            $eschools = $member->eschools;
            
            foreach ($eschools as $eschool) {
                // Generate payments for last 6 months for each eschool
                for ($monthOffset = 0; $monthOffset < 6; $monthOffset++) {
                    $targetDate = Carbon::now()->subMonths($monthOffset);
                    $month = $targetDate->month;
                    $year = $targetDate->year;

                    // Find kas record for this eschool and month
                    $relatedKasRecord = $kasRecords->where('eschool_id', $eschool->id)
                                                  ->filter(function ($record) use ($month, $year) {
                                                      return $record->date->month == $month && $record->date->year == $year;
                                                  })
                                                  ->first();

                    // If no exact match, get any kas record for this eschool
                    if (!$relatedKasRecord) {
                        $relatedKasRecord = $kasRecords->where('eschool_id', $eschool->id)->first();
                    }

                    if ($relatedKasRecord) {
                        // Most payments are made (85% payment rate for realistic data)
                        $isPaid = rand(0, 100) > 15;
                        $paidDate = $isPaid ? $targetDate->copy()->addDays(rand(1, 20)) : null;

                        $kasPayments[] = [
                            'member_id' => $member->id,
                            'kas_record_id' => $relatedKasRecord->id,
                            'amount' => $eschool->monthly_kas_amount,
                            'month' => $month,
                            'year' => $year,
                            'is_paid' => $isPaid,
                            'paid_date' => $paidDate,
                            'created_at' => $targetDate,
                            'updated_at' => $paidDate ?? $targetDate,
                        ];
                    }
                }
            }
        }

        foreach ($kasPayments as $payment) {
            KasPayment::create($payment);
        }
    }
}