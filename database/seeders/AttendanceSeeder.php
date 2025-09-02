<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AttendanceRecord;
use App\Models\Eschool;
use App\Models\Member;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB; // <- ini yang benar

class AttendanceSeeder extends Seeder
{
    public function run(): void
    {
        // $eschools = Eschool::all();
        // $coordinators = User::where('role', 'koordinator')->get();

        // if ($eschools->isEmpty() || $coordinators->isEmpty()) {
        //     $this->command->warn('Make sure Eschools and Coordinators exist before running this seeder.');
        //     return;
        // }

        // $attendanceRecords = [];

        // foreach ($eschools as $eschool) {
        //     // Get members for this eschool
        //     $members = $eschool->members()->where('is_active', true)->get();
            
        //     if ($members->isEmpty()) {
        //         continue;
        //     }

        //     // Get coordinator for this eschool
        //     $coordinator = $coordinators->firstWhere('id', $eschool->coordinator_id);
        //     if (!$coordinator) {
        //         $coordinator = $coordinators->first();
        //     }

        //     // Generate attendance records for the last 30 days
        //     for ($dayOffset = 0; $dayOffset < 30; $dayOffset++) {
        //         $attendanceDate = Carbon::now()->subDays($dayOffset);
                
        //         // Skip weekends (Saturday and Sunday)
        //         if ($attendanceDate->isWeekend()) {
        //             continue;
        //         }

        //         // Create attendance records for each member
        //         foreach ($members as $member) {
        //             // 90% attendance rate for realistic data
        //             $isPresent = rand(1, 100) <= 90;
                    
        //             // Add notes for some absent records
        //             $notes = null;
        //             if (!$isPresent) {
        //                 $absentReasons = [
        //                     'Sakit',
        //                     'Izin keluarga',
        //                     'Acara keluarga',
        //                     'Transportasi bermasalah',
        //                     'Kebutuhan mendadak',
        //                     null // Sometimes no reason given
        //                 ];
        //                 $notes = $absentReasons[array_rand($absentReasons)];
        //             }

        //             $attendanceRecords[] = [
        //                 'eschool_id' => $eschool->id,
        //                 'member_id' => $member->id,
        //                 'recorder_id' => $coordinator->id,
        //                 'date' => $attendanceDate->format('Y-m-d'),
        //                 'is_present' => $isPresent,
        //                 'notes' => $notes,
        //                 'created_at' => $attendanceDate,
        //                 'updated_at' => $attendanceDate,
        //             ];
        //         }
        //     }
        // }

        // foreach ($attendanceRecords as $record) {
        //     AttendanceRecord::create($record);
        // }

        // $this->command->info("Created {$this->formatNumber(count($attendanceRecords))} attendance records.");
         $attendanceRecords = [];
        
        // Generate attendance records untuk beberapa hari terakhir
        $dates = [
            '2025-08-26', '2025-08-27', '2025-08-28', '2025-08-29', '2025-08-30'
        ];

        foreach ($dates as $date) {
            // Attendance untuk Eschool Basket (ID: 1)
            $basketMembers = [1, 4, 5, 6]; // Andi, Budi, Cici, Dedi
            foreach ($basketMembers as $memberId) {
                $attendanceRecords[] = [
                    'eschool_id' => 1,
                    'member_id' => $memberId,
                    'recorder_id' => 3, // Pak Joko (koordinator)
                    'date' => $date . ' 15:30:00',
                    'is_present' => rand(0, 1) ? true : false,
                    'notes' => rand(0, 1) ? null : 'Sakit',
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            // Attendance untuk Eschool Voli (ID: 2)
            $voliMembers = [2, 7, 8, 1]; // Eka, Fani, Gita, Andi
            foreach ($voliMembers as $memberId) {
                $attendanceRecords[] = [
                    'eschool_id' => 2,
                    'member_id' => $memberId,
                    'recorder_id' => 4, // Bu Rina (koordinator)
                    'date' => $date . ' 16:00:00',
                    'is_present' => rand(0, 1) ? true : false,
                    'notes' => rand(0, 1) ? null : 'Izin',
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            // Attendance untuk Eschool Lukis (ID: 3)
            $lukisMembers = [3, 9, 4]; // Hani, Iko, Budi
            foreach ($lukisMembers as $memberId) {
                $attendanceRecords[] = [
                    'eschool_id' => 3,
                    'member_id' => $memberId,
                    'recorder_id' => 5, // Pak Heru (koordinator)
                    'date' => $date . ' 14:00:00',
                    'is_present' => rand(0, 1) ? true : false,
                    'notes' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            // Attendance untuk Eschool Matematika (ID: 4) - Sekolah 2
            $matematikaMembers = [10, 12, 13]; // Rina, Lisa, Raka
            foreach ($matematikaMembers as $memberId) {
                $attendanceRecords[] = [
                    'eschool_id' => 4,
                    'member_id' => $memberId,
                    'recorder_id' => 15, // Pak Ahmad (koordinator)
                    'date' => $date . ' 15:00:00',
                    'is_present' => rand(0, 1) ? true : false,
                    'notes' => rand(0, 1) ? null : 'Terlambat',
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            // Attendance untuk Eschool Teater (ID: 5) - Sekolah 2
            $teaterMembers = [11, 12, 10]; // Doni, Lisa, Rina
            foreach ($teaterMembers as $memberId) {
                $attendanceRecords[] = [
                    'eschool_id' => 5,
                    'member_id' => $memberId,
                    'recorder_id' => 16, // Bu Lina (koordinator)
                    'date' => $date . ' 16:30:00',
                    'is_present' => rand(0, 1) ? true : false,
                    'notes' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        // Insert attendance records in chunks untuk performa yang lebih baik
        $chunks = array_chunk($attendanceRecords, 50);
        foreach ($chunks as $chunk) {
            DB::table('attendance_records')->insert($chunk);
        }
    }

    private function formatNumber($number) {
        return number_format($number, 0, ',', '.');
    }
}