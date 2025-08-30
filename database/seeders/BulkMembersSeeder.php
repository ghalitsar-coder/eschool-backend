<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class BulkMembersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all eschools
        $eschools = DB::table('eschools')->get();
        
        // Get all existing users with role 'siswa'
        $existingStudents = DB::table('users')->where('role', 'siswa')->pluck('id')->toArray();
        $usedUserIds = [];
        
        // Create members for each eschool
        foreach ($eschools as $eschool) {
            // Create 10 members for each eschool
            for ($i = 1; $i <= 10; $i++) {
                // Find an unused student user or create a new one
                $availableUsers = array_diff($existingStudents, $usedUserIds);
                if (!empty($availableUsers)) {
                    // Use an existing user
                    $userId = array_shift($availableUsers);
                } else {
                    // Create a new user
                    $userId = DB::table('users')->insertGetId([
                        'name' => "Student {$eschool->id}-{$i}",
                        'email' => "student{$eschool->id}-{$i}@example.com",
                        'password' => Hash::make('password'),
                        'role' => 'siswa',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
                
                $usedUserIds[] = $userId;
                
                // Create member record using Eloquent to trigger name sync
                $member = new \App\Models\Member([
                    'school_id' => $eschool->school_id,
                    'user_id' => $userId,
                    'nip' => "NIP{$eschool->id}{$i}",
                    'date_of_birth' => now()->subYears(rand(15, 20))->format('Y-m-d'),
                    'gender' => rand(0, 1) ? 'L' : 'P',
                    'address' => "Address for Student {$eschool->id}-{$i}",
                    'phone' => "0812345678" . str_pad($i, 2, '0', STR_PAD_LEFT),
                    'status' => 'active',
                    'is_active' => true,
                ]);
                $member->save();
                
                // Associate member with eschool
                $member->eschools()->attach($eschool->id);
            }
        }
    }
}