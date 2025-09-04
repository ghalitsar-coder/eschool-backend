<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AttendanceRecord;
use Carbon\Carbon;

class NewAttendanceRecordSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Seeder untuk Attendance Records berdasarkan multi-role system
     */
    public function run(): void
    {
        $attendanceRecords = [];
        $startDate = Carbon::parse('2025-08-01');
        $endDate = Carbon::parse('2025-09-04');

        // Define eschool schedules
        $eschoolSchedules = [
            1 => ['Monday', 'Wednesday', 'Friday'], // Karate Jakarta
            2 => ['Tuesday', 'Thursday'], // Paskibra Jakarta
            3 => ['Wednesday', 'Saturday'], // Taekwondo Jakarta
            4 => ['Monday', 'Wednesday', 'Friday', 'Saturday'], // Basket Bandung
            5 => ['Tuesday', 'Thursday', 'Saturday'], // Futsal Bandung
        ];

        // Define members for each eschool (based on UserEschoolRole)
        $eschoolMembers = [
            1 => [1, 2, 7, 8], // Karate Jakarta: Ahmad, Siti, MultiRole1, MultiRole2
            2 => [1, 3, 7], // Paskibra Jakarta: Ahmad, Budi, MultiRole1
            3 => [2, 4, 7], // Taekwondo Jakarta: Siti, Indira, MultiRole1
            4 => [5, 6], // Basket Bandung: Eko, Rina
            5 => [5], // Futsal Bandung: Eko
        ];

        // Define recorders (koordinator for each eschool)
        $eschoolRecorders = [
            1 => 4, // Koordinator Karate Jakarta
            2 => 5, // Koordinator Paskibra Jakarta
            3 => 4, // Koordinator Karate Jakarta (multi-role)
            4 => 6, // Koordinator Basket Bandung
            5 => 6, // Koordinator Basket Bandung (multi-role)
        ];

        $recordId = 1;

        // Generate attendance records for each eschool
        foreach ($eschoolSchedules as $eschoolId => $scheduleDays) {
            $members = $eschoolMembers[$eschoolId];
            $recorderId = $eschoolRecorders[$eschoolId];

            // Loop through dates
            $currentDate = $startDate->copy();
            while ($currentDate <= $endDate) {
                $dayName = $currentDate->format('l'); // Get day name (Monday, Tuesday, etc.)

                // Check if this day is a schedule day for this eschool
                if (in_array($dayName, $scheduleDays)) {
                    // Create attendance records for all members
                    foreach ($members as $memberId) {
                        // Generate random attendance pattern (90% present)
                        $isPresent = rand(1, 100) <= 90;
                        
                        // Some specific patterns for realism
                        if ($currentDate->format('Y-m-d') === '2025-08-17') {
                            // Indonesian Independence Day - lower attendance
                            $isPresent = rand(1, 100) <= 30;
                        }
                        
                        if ($currentDate->isWeekend() && $eschoolId <= 2) {
                            // Weekend activities might have different attendance
                            $isPresent = rand(1, 100) <= 70;
                        }

                        $attendanceRecords[] = [
                            'id' => $recordId++,
                            'eschool_id' => $eschoolId,
                            'member_id' => $memberId,
                            'recorder_id' => $recorderId,
                            'date' => $currentDate->format('Y-m-d'),
                            'is_present' => $isPresent,
                            'notes' => $this->generateNotes($isPresent, $currentDate),
                            'proof_document_path' => null, // No proof documents in seeder
                            'proof_document_name' => null,
                            'proof_document_type' => null,
                            'proof_document_size' => null,
                            'created_at' => $currentDate->copy()->addHours(rand(14, 18))->addMinutes(rand(0, 59)),
                            'updated_at' => $currentDate->copy()->addHours(rand(14, 18))->addMinutes(rand(0, 59)),
                        ];
                    }
                }

                $currentDate->addDay();
            }
        }

        // Add some specific scenarios for testing
        $specialRecords = [
            // Recent attendance for testing
            [
                'id' => $recordId++,
                'eschool_id' => 1,
                'member_id' => 1, // Ahmad Rizki
                'recorder_id' => 4,
                'date' => '2025-09-04',
                'is_present' => true,
                'notes' => 'Hadir tepat waktu untuk latihan karate',
                'proof_document_path' => null,
                'proof_document_name' => null,
                'proof_document_type' => null,
                'proof_document_size' => null,
                'created_at' => Carbon::parse('2025-09-04 15:30:00'),
                'updated_at' => Carbon::parse('2025-09-04 15:30:00'),
            ],
            [
                'id' => $recordId++,
                'eschool_id' => 1,
                'member_id' => 2, // Siti Nurhaliza
                'recorder_id' => 4,
                'date' => '2025-09-04',
                'is_present' => false,
                'notes' => 'Sakit demam, sudah ada surat dokter',
                'proof_document_path' => null,
                'proof_document_name' => null,
                'proof_document_type' => null,
                'proof_document_size' => null,
                'created_at' => Carbon::parse('2025-09-04 15:30:00'),
                'updated_at' => Carbon::parse('2025-09-04 15:30:00'),
            ],
            [
                'id' => $recordId++,
                'eschool_id' => 4,
                'member_id' => 5, // Eko Prasetyo (multi-role example)
                'recorder_id' => 6,
                'date' => '2025-09-04',
                'is_present' => true,
                'notes' => 'Latihan basket persiapan kompetisi',
                'proof_document_path' => null,
                'proof_document_name' => null,
                'proof_document_type' => null,
                'proof_document_size' => null,
                'created_at' => Carbon::parse('2025-09-04 16:00:00'),
                'updated_at' => Carbon::parse('2025-09-04 16:00:00'),
            ],
        ];

        $attendanceRecords = array_merge($attendanceRecords, $specialRecords);

        // Bulk insert for better performance
        foreach (array_chunk($attendanceRecords, 100) as $chunk) {
            AttendanceRecord::insert($chunk);
        }

        $this->command->info('✅ Attendance Records seeded successfully! Created ' . count($attendanceRecords) . ' attendance records.');
        $this->command->line('');
        $this->command->info('📊 Attendance Summary:');
        
        $totalPresent = collect($attendanceRecords)->where('is_present', true)->count();
        $totalAbsent = collect($attendanceRecords)->where('is_present', false)->count();
        $attendanceRate = $totalPresent / count($attendanceRecords) * 100;
        
        $this->command->line("✅ Present: {$totalPresent} records");
        $this->command->line("❌ Absent: {$totalAbsent} records");
        $this->command->line("📈 Overall Attendance Rate: " . round($attendanceRate, 2) . "%");
        $this->command->line('');
        $this->command->info('📅 Date Range: 2025-08-01 to 2025-09-04');
        $this->command->info('🔄 Multi-role Examples:');
        $this->command->line('👤 Ahmad Rizki: Attendance in Karate + Paskibra');
        $this->command->line('👤 Siti Nurhaliza: Attendance in Karate + Taekwondo');
        $this->command->line('👤 Eko Prasetyo: Attendance in Basket + Futsal');
    }

    private function generateNotes($isPresent, $date)
    {
        if ($isPresent) {
            $presentNotes = [
                'Hadir tepat waktu',
                'Mengikuti latihan dengan baik',
                'Semangat dalam berlatih',
                'Menunjukkan kemajuan yang baik',
                'Aktif dalam kegiatan',
                null, // Some records without notes
            ];
            return $presentNotes[array_rand($presentNotes)];
        } else {
            $absentNotes = [
                'Sakit',
                'Izin keperluan keluarga',
                'Ada ujian sekolah',
                'Transportasi bermasalah',
                'Cuaca buruk',
                'Keperluan mendadak',
            ];
            return $absentNotes[array_rand($absentNotes)];
        }
    }
}
