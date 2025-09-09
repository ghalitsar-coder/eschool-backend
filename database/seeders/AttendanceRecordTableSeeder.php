<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AttendanceRecordTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Sample attendance data with new schema
        $attendanceData = [
            // Basket (eschool_id: 1)
            ['user_id' => 2, 'eschool_id' => 1, 'is_present' => true, 'notes' => 'Latihan rutin'],
            ['user_id' => 5, 'eschool_id' => 1, 'is_present' => true, 'notes' => null],
            ['user_id' => 8, 'eschool_id' => 1, 'is_present' => true, 'notes' => null],
            ['user_id' => 9, 'eschool_id' => 1, 'is_present' => false, 'notes' => 'Sakit'],
            ['user_id' => 10, 'eschool_id' => 1, 'is_present' => false, 'notes' => 'Terlambat 15 menit'],
            
            // Voli (eschool_id: 2)
            ['user_id' => 8, 'eschool_id' => 2, 'is_present' => true, 'notes' => null],
            ['user_id' => 9, 'eschool_id' => 2, 'is_present' => true, 'notes' => null],
            ['user_id' => 5, 'eschool_id' => 2, 'is_present' => false, 'notes' => 'Terlambat 10 menit'],
            
            // Lukis (eschool_id: 3)
            ['user_id' => 7, 'eschool_id' => 3, 'is_present' => true, 'notes' => null],
            ['user_id' => 8, 'eschool_id' => 3, 'is_present' => true, 'notes' => null],
            ['user_id' => 9, 'eschool_id' => 3, 'is_present' => false, 'notes' => 'Izin'],
            
            // Matematika (eschool_id: 4)
            ['user_id' => 6, 'eschool_id' => 4, 'is_present' => true, 'notes' => null],
            ['user_id' => 11, 'eschool_id' => 4, 'is_present' => true, 'notes' => null],
            ['user_id' => 12, 'eschool_id' => 4, 'is_present' => false, 'notes' => 'Terlambat 5 menit'],
            
            // Musik (eschool_id: 5)
            ['user_id' => 11, 'eschool_id' => 5, 'is_present' => true, 'notes' => null],
            ['user_id' => 12, 'eschool_id' => 5, 'is_present' => true, 'notes' => null],
            
            // Robotika (eschool_id: 6)
            ['user_id' => 8, 'eschool_id' => 6, 'is_present' => true, 'notes' => null],
            ['user_id' => 13, 'eschool_id' => 6, 'is_present' => false, 'notes' => 'Sakit'],
            
            // Teater (eschool_id: 7)
            ['user_id' => 13, 'eschool_id' => 7, 'is_present' => true, 'notes' => null],
            ['user_id' => 14, 'eschool_id' => 7, 'is_present' => true, 'notes' => null],
        ];
        
        $attendances = [];
        $currentDate = now()->format('Y-m-d');
        
        foreach ($attendanceData as $data) {
            $role = DB::table('user_eschool_roles')
                ->where('user_id', $data['user_id'])
                ->where('eschool_id', $data['eschool_id'])
                ->first();
                
            if ($role) {
                $attendances[] = [
                    'user_eschool_role_id' => $role->id,
                    'is_present' => $data['is_present'],
                    'date' => $currentDate,
                    'notes' => $data['notes'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }
        
        if (!empty($attendances)) {
            DB::table('attendance_record')->insert($attendances);
        }
    }
}