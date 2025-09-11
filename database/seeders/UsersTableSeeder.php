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
        // Ambil semua profil yang ada
        $profiles = DB::table('profiles')->get();
        
        $users = [];
        foreach ($profiles as $profile) {
            // Bersihkan nama untuk membuat email yang valid
            $cleanName = preg_replace('/[^a-zA-Z0-9\s]/', '', $profile->name);
            $email = strtolower(str_replace(' ', '.', $cleanName)) . '@example.com';
            
            $users[] = [
                'profile_id' => $profile->id,
                'name' => $profile->name,
                'email' => $email,
                'email_verified_at' => now(),
                'password' => Hash::make('password123'),
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        
        // Hanya masukkan jika ada profil
        if (!empty($users)) {
            DB::table('users')->insert($users);
        }
    }
}