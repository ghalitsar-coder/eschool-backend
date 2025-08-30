<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UniqueMembersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all eschools
        $eschools = DB::table('eschools')->get();
        
        // Track which users are already members
        $usedUserIds = [];
        
        // Create unique members for each eschool
        foreach ($eschools as $eschool) {
            // Create 10 unique members for each eschool
            for ($i = 1; $i <= 10; $i++) {
                // Generate unique user data for each member
                $userName = "Student {$eschool->id}-{$i} (Eschool {$eschool->name})";
                $userEmail = "student{$eschool->id}-{$i}@eschool{$eschool->id}.com";
                
                // Check if user already exists
                $existingUser = DB::table('users')
                                 ->where('email', $userEmail)
                                 ->first();
                
                if ($existingUser) {
                    $userId = $existingUser->id;
                } else {
                    // Create new user with unique name
                    $userId = DB::table('users')->insertGetId([
                        'name' => $userName,
                        'email' => $userEmail,
                        'password' => Hash::make('password'),
                        'role' => 'siswa',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
                
                // Skip if user is already used as a member
                if (in_array($userId, $usedUserIds)) {
                    continue;
                }
                
                $usedUserIds[] = $userId;
                
                // Check if member already exists for this user
                $existingMember = DB::table('members')
                                   ->where('user_id', $userId)
                                   ->first();
                
                if (!$existingMember) {
                    // Create member record with unique student_id
                    $memberId = DB::table('members')->insertGetId([
                        'school_id' => $eschool->school_id,
                        'user_id' => $userId,
                        'nip' => "NIP{$eschool->id}{$i}",
                        'name' => $userName, // Will be synced by model
                        'student_id' => "STD{$eschool->id}" . str_pad($i, 3, '0', STR_PAD_LEFT),
                        'date_of_birth' => now()->subYears(rand(15, 20))->format('Y-m-d'),
                        'gender' => rand(0, 1) ? 'L' : 'P',
                        'address' => "Address for Student {$eschool->id}-{$i} in Eschool {$eschool->name}",
                        'phone' => "0812345678" . str_pad($i, 2, '0', STR_PAD_LEFT),
                        'status' => 'active',
                        'is_active' => true,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                    
                    // Associate member with eschool
                    DB::table('eschool_member')->insert([
                        'eschool_id' => $eschool->id,
                        'member_id' => $memberId,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }
    }
}