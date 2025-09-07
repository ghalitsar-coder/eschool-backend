<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Profil IDs berdasarkan urutan di ProfilesTableSeeder
        $profileIds = range(1, 14);
        
        $users = [];
        foreach ($profileIds as $index => $profileId) {
            $users[] = [
                'profile_id' => $profileId,
                'name' => DB::table('profiles')->where('id', $profileId)->value('name'),
                'email' => strtolower(str_replace(' ', '.', DB::table('profiles')->where('id', $profileId)->value('name'))) . '@example.com',
                'email_verified_at' => now(),
                'password' => Hash::make('password123'),
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        
        DB::table('users')->insert($users);
    }
}