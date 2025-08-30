<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\School;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run()
    {
        // Get all schools
        $schools = School::all();
        
        if ($schools->isEmpty()) {
            $this->command->warn('Make sure Schools exist before running this seeder.');
            return;
        }
        
        // Array of realistic Indonesian names for students
        $studentNames = [
            'Aditya Pratama', 'Budi Santoso', 'Citra Dewi', 'Dian Permata', 'Eko Prasetyo',
            'Fitri Handayani', 'Galih Ramadhan', 'Hana Putri', 'Indra Kusuma', 'Jenny Wijaya',
            'Kevin Sanjaya', 'Lina Marlina', 'Mega Sari', 'Nanda Kurnia', 'Oka Pradana',
            'Putri Ayu', 'Rendi Saputra', 'Sari Indah', 'Taufik Hidayat', 'Umi Kalsum',
            'Vina Anggraini', 'Wawan Setiawan', 'Yani Susanti', 'Zainal Abidin', 'Ayu Lestari',
            'Bambang Widodo', 'Cinta Nurul', 'Dodi Firmansyah', 'Elisa Damayanti', 'Fajar Nugroho',
            'Gita Savitri', 'Heru Prasetyo', 'Intan Permata', 'Joko Susilo', 'Kartika Sari',
            'Lukman Hakim', 'Maya Indah', 'Nugroho Putra', 'Olivia Wulandari', 'Pandu Aditya',
            'Queen Amalia', 'Rizki Ramadhan', 'Sinta Nurhaliza', 'Teguh Santoso', 'Ulfa Rahayu',
            'Verdi Pratama', 'Wulan Sari', 'Xanana Gusmao', 'Yoga Pradana', 'Zahra Aisyah'
        ];

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
                'school_id' => $schools->first()->id, // Assign staff to first school
            ],
        ];

        // Add realistic student users
        foreach ($studentNames as $index => $name) {
            // Distribute students across schools (for demo purposes)
            $schoolId = $schools->get($index % $schools->count())->id;
            
            $users[] = [
                'name' => $name,
                'email' => strtolower(str_replace(' ', '.', $name)) . '@example.com',
                'password' => Hash::make('password'),
                'role' => 'siswa',
                'school_id' => $schoolId, // Assign students to schools
            ];
        }

        foreach ($users as $user) {
            User::create($user);
        }
    }
}