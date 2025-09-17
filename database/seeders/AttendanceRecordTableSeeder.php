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
        // Clear existing data
        DB::table('attendance_record')->delete();
        
        // Get all users with their profiles
        $users = DB::table('users')
            ->join('profiles', 'users.profile_id', '=', 'profiles.id')
            ->select('users.id as user_id', 'profiles.name as profile_name')
            ->get();
        
        // Get all eschools with their names
        $eschools = DB::table('eschools')
            ->select('id', 'name')
            ->get();
        
        // Create a mapping of user names to user IDs
        $userMap = [];
        foreach ($users as $user) {
            $userMap[$user->profile_name] = $user->user_id;
        }
        
        // Create a mapping of eschool names to eschool IDs
        $eschoolMap = [];
        foreach ($eschools as $eschool) {
            $eschoolMap[$eschool->name] = $eschool->id;
        }
        
        // Sample attendance data with updated schema
        $attendanceData = [
            // Basket (eschool_id: 1)
            ['user_name' => 'Andi', 'eschool_name' => 'Basket', 'is_present' => true, 'notes' => null],
            ['user_name' => 'Budi', 'eschool_name' => 'Basket', 'is_present' => true, 'notes' => null],
            ['user_name' => 'Cici', 'eschool_name' => 'Basket', 'is_present' => true, 'notes' => 'Sakit'],
            ['user_name' => 'Dedi', 'eschool_name' => 'Basket', 'is_present' => true, 'notes' => 'Terlambat 15 menit'],
            
            // Voli (eschool_id: 2)
            ['user_name' => 'Budi', 'eschool_name' => 'Voli', 'is_present' => true, 'notes' => null],
            ['user_name' => 'Cici', 'eschool_name' => 'Voli', 'is_present' => true, 'notes' => null],
            ['user_name' => 'Andi', 'eschool_name' => 'Voli', 'is_present' => true, 'notes' => 'Terlambat 10 menit'],
            
            // Lukis (eschool_id: 3)
            ['user_name' => 'Hani', 'eschool_name' => 'Lukis', 'is_present' => true, 'notes' => null],
            ['user_name' => 'Budi', 'eschool_name' => 'Lukis', 'is_present' => true, 'notes' => null],
            ['user_name' => 'Cici', 'eschool_name' => 'Lukis', 'is_present' => true, 'notes' => 'Izin'],
            
            // Matematika (eschool_id: 4)
            ['user_name' => 'Eka', 'eschool_name' => 'Matematika', 'is_present' => true, 'notes' => null],
            ['user_name' => 'Fani', 'eschool_name' => 'Matematika', 'is_present' => true, 'notes' => null],
            ['user_name' => 'Gita', 'eschool_name' => 'Matematika', 'is_present' => true, 'notes' => 'Terlambat 5 menit'],
            
            // Musik (eschool_id: 5)
            ['user_name' => 'Fani', 'eschool_name' => 'Musik', 'is_present' => true, 'notes' => null],
            ['user_name' => 'Gita', 'eschool_name' => 'Musik', 'is_present' => true, 'notes' => null],
            
            // Robotika (eschool_id: 6)
            ['user_name' => 'Budi', 'eschool_name' => 'Robotika', 'is_present' => true, 'notes' => null],
            ['user_name' => 'Iko', 'eschool_name' => 'Robotika', 'is_present' => true, 'notes' => 'Sakit'],
            
            // Teater (eschool_id: 7)
            ['user_name' => 'Iko', 'eschool_name' => 'Teater', 'is_present' => true, 'notes' => null],
            ['user_name' => 'Joni', 'eschool_name' => 'Teater', 'is_present' => true, 'notes' => null],
        ];
        
        $attendances = [];
        $currentDate = now()->format('Y-m-d');
        
        foreach ($attendanceData as $data) {
            // Map user name to user ID
            if (!isset($userMap[$data['user_name']])) {
                continue;
            }
            
            // Map eschool name to eschool ID
            if (!isset($eschoolMap[$data['eschool_name']])) {
                continue;
            }
            
            $userId = $userMap[$data['user_name']];
            $eschoolId = $eschoolMap[$data['eschool_name']];
            
            // Get user_eschool_role
            $role = DB::table('user_eschool_roles')
                ->where('user_id', $userId)
                ->where('eschool_id', $eschoolId)
                ->first();
                
            if ($role) {
                // Get coordinator for this eschool as recorder
                $coordinator = DB::table('user_eschool_roles')
                    ->where('eschool_id', $eschoolId)
                    ->where('role', 'coordinator')
                    ->first();
                
                $attendances[] = [
                    'user_eschool_role_id' => $role->id,
                    'is_present' => $data['is_present'],
                    'date' => $currentDate,
                    'notes' => $data['notes'],
                    'proof_document' => null,
                    'recorder_id' => $coordinator ? $coordinator->user_id : null, // Coordinator's user_id as recorder
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