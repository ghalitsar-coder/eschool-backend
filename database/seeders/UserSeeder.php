<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run()
    {
        $users = [
            // Bendahara users untuk MVP testing
            [
                'name' => 'Siti Nurhaliza',
                'email' => 'bendahara1@example.com',
                'password' => Hash::make('password'),
                'role' => 'bendahara',
            ],
            [
                'name' => 'Ahmad Fauzi',
                'email' => 'bendahara2@example.com',
                'password' => Hash::make('password'),
                'role' => 'bendahara',
            ],
            [
                'name' => 'Dewi Sartika',
                'email' => 'bendahara3@example.com',
                'password' => Hash::make('password'),
                'role' => 'bendahara',
            ],
            
            // Koordinator users
            [
                'name' => 'Budi Santoso',
                'email' => 'koordinator1@example.com',
                'password' => Hash::make('password'),
                'role' => 'koordinator',
            ],
            [
                'name' => 'Rina Wijaya',
                'email' => 'koordinator2@example.com',
                'password' => Hash::make('password'),
                'role' => 'koordinator',
            ],
            
            // Staff users
            [
                'name' => 'Joko Widodo',
                'email' => 'staff1@example.com',
                'password' => Hash::make('password'),
                'role' => 'staff',
            ],
            
            // Siswa users (banyak untuk testing)
            [
                'name' => 'Andi Pratama',
                'email' => 'siswa1@example.com',
                'password' => Hash::make('password'),
                'role' => 'siswa',
            ],
            [
                'name' => 'Sari Indah',
                'email' => 'siswa2@example.com',
                'password' => Hash::make('password'),
                'role' => 'siswa',
            ],
            [
                'name' => 'Rudi Hermawan',
                'email' => 'siswa3@example.com',
                'password' => Hash::make('password'),
                'role' => 'siswa',
            ],
            [
                'name' => 'Maya Sari',
                'email' => 'siswa4@example.com',
                'password' => Hash::make('password'),
                'role' => 'siswa',
            ],
            [
                'name' => 'Doni Setiawan',
                'email' => 'siswa5@example.com',
                'password' => Hash::make('password'),
                'role' => 'siswa',
            ],
            [
                'name' => 'Lina Marlina',
                'email' => 'siswa6@example.com',
                'password' => Hash::make('password'),
                'role' => 'siswa',
            ],
            [
                'name' => 'Agus Salim',
                'email' => 'siswa7@example.com',
                'password' => Hash::make('password'),
                'role' => 'siswa',
            ],
            [
                'name' => 'Fitri Handayani',
                'email' => 'siswa8@example.com',
                'password' => Hash::make('password'),
                'role' => 'siswa',
            ],
            [
                'name' => 'Hendra Gunawan',
                'email' => 'siswa9@example.com',
                'password' => Hash::make('password'),
                'role' => 'siswa',
            ],
            [
                'name' => 'Novi Rahayu',
                'email' => 'siswa10@example.com',
                'password' => Hash::make('password'),
                'role' => 'siswa',
            ],
            [
                'name' => 'Bayu Aji',
                'email' => 'siswa11@example.com',
                'password' => Hash::make('password'),
                'role' => 'siswa',
            ],
            [
                'name' => 'Citra Dewi',
                'email' => 'siswa12@example.com',
                'password' => Hash::make('password'),
                'role' => 'siswa',
            ],
            [
                'name' => 'Eko Prasetyo',
                'email' => 'siswa13@example.com',
                'password' => Hash::make('password'),
                'role' => 'siswa',
            ],
            [
                'name' => 'Gita Savitri',
                'email' => 'siswa14@example.com',
                'password' => Hash::make('password'),
                'role' => 'siswa',
            ],
            [
                'name' => 'Irfan Hakim',
                'email' => 'siswa15@example.com',
                'password' => Hash::make('password'),
                'role' => 'siswa',
            ],
        ];

        foreach ($users as $user) {
            User::create($user);
        }
    }
}