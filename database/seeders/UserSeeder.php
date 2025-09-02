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
        // // Get all schools
        // $schools = School::all();
        
        // if ($schools->isEmpty()) {
        //     $this->command->warn('Make sure Schools exist before running this seeder.');
        //     return;
        // }
        
        // // Array of realistic Indonesian names for students
        // $studentNames = [
        //     'Aditya Pratama', 'Budi Santoso', 'Citra Dewi', 'Dian Permata', 'Eko Prasetyo',
        //     'Fitri Handayani', 'Galih Ramadhan', 'Hana Putri', 'Indra Kusuma', 'Jenny Wijaya',
        //     'Kevin Sanjaya', 'Lina Marlina', 'Mega Sari', 'Nanda Kurnia', 'Oka Pradana',
        //     'Putri Ayu', 'Rendi Saputra', 'Sari Indah', 'Taufik Hidayat', 'Umi Kalsum',
        //     'Vina Anggraini', 'Wawan Setiawan', 'Yani Susanti', 'Zainal Abidin', 'Ayu Lestari',
        //     'Bambang Widodo', 'Cinta Nurul', 'Dodi Firmansyah', 'Elisa Damayanti', 'Fajar Nugroho',
        //     'Gita Savitri', 'Heru Prasetyo', 'Intan Permata', 'Joko Susilo', 'Kartika Sari',
        //     'Lukman Hakim', 'Maya Indah', 'Nugroho Putra', 'Olivia Wulandari', 'Pandu Aditya',
        //     'Queen Amalia', 'Rizki Ramadhan', 'Sinta Nurhaliza', 'Teguh Santoso', 'Ulfa Rahayu',
        //     'Verdi Pratama', 'Wulan Sari', 'Xanana Gusmao', 'Yoga Pradana', 'Zahra Aisyah',
        //     'Ade Saputra', 'Bunga Sari', 'Candra Wijaya', 'Dewi Lestari', 'Eka Putra',
        //     'Fitria Nurul', 'Ganda Pratama', 'Hesti Wulandari', 'Irfan Hakim', 'Jamilah Sari',
        //     'Karina Putri', 'Lingga Pratama', 'Mira Sari', 'Nanda Permana', 'Opik Maulana',
        //     'Putu Wijaya', 'Qori Hasan', 'Ratna Sari', 'Sigit Prasetyo', 'Tina Wulandari',
        //     'Ujang Hermawan', 'Vivi Andriani', 'Wahyu Setiawan', 'Yuni Astuti', 'Zaki Rahman'
        // ];

        // // Array of realistic names for staff, coordinators, and treasurers
        // $staffNames = [
        //     'Siti Nurhaliza', 'Ahmad Fauzi', 'Dewi Sartika', 'Budi Santoso', 'Rina Wijaya',
        //     'Joko Widodo', 'Maya Indah', 'Agus Salim', 'Ratna Sari', 'Hendra Gunawan',
        //     'Linda Kurnia', 'Dedi Prasetyo', 'Rini Wulandari', 'Toni Santoso', 'Sari Utami',
        //     'Eko Prasetyo', 'Indah Permata', 'Ferry Wijaya', 'Nurul Hidayah', 'Herman Susanto'
        // ];

        // $users = [
        //     // Bendahara users untuk MVP testing
        //     [
        //         'name' => 'Siti Nurhaliza',
        //         'email' => 'bendahara1@example.com',
        //         'password' => Hash::make('password'),
        //         'role' => 'bendahara',
        //     ],
        //     [
        //         'name' => 'Ahmad Fauzi',
        //         'email' => 'bendahara2@example.com',
        //         'password' => Hash::make('password'),
        //         'role' => 'bendahara',
        //     ],
        //     [
        //         'name' => 'Dewi Sartika',
        //         'email' => 'bendahara3@example.com',
        //         'password' => Hash::make('password'),
        //         'role' => 'bendahara',
        //     ],
            
        //     // Koordinator users
        //     [
        //         'name' => 'Budi Santoso',
        //         'email' => 'koordinator1@example.com',
        //         'password' => Hash::make('password'),
        //         'role' => 'koordinator',
        //     ],
        //     [
        //         'name' => 'Rina Wijaya',
        //         'email' => 'koordinator2@example.com',
        //         'password' => Hash::make('password'),
        //         'role' => 'koordinator',
        //     ],
        //     [
        //         'name' => 'Maya Indah',
        //         'email' => 'koordinator3@example.com',
        //         'password' => Hash::make('password'),
        //         'role' => 'koordinator',
        //     ],
        //     [
        //         'name' => 'Agus Salim',
        //         'email' => 'koordinator4@example.com',
        //         'password' => Hash::make('password'),
        //         'role' => 'koordinator',
        //     ],
        //     [
        //         'name' => 'Ratna Sari',
        //         'email' => 'koordinator5@example.com',
        //         'password' => Hash::make('password'),
        //         'role' => 'koordinator',
        //     ],
            
        //     // Staff users - assign to different schools
        //     [
        //         'name' => 'Joko Widodo',
        //         'email' => 'staff1@example.com',
        //         'password' => Hash::make('password'),
        //         'role' => 'staff',
        //         'school_id' => $schools->first()->id, // Assign staff to first school
        //     ],
        //     [
        //         'name' => 'Linda Kurnia',
        //         'email' => 'staff2@example.com',
        //         'password' => Hash::make('password'),
        //         'role' => 'staff',
        //         'school_id' => $schools->skip(1)->first()->id, // Assign to second school
        //     ],
        //     [
        //         'name' => 'Dedi Prasetyo',
        //         'email' => 'staff3@example.com',
        //         'password' => Hash::make('password'),
        //         'role' => 'staff',
        //         'school_id' => $schools->skip(2)->first()->id, // Assign to third school
        //     ],
        // ];

        // // Add realistic student users
        // foreach ($studentNames as $index => $name) {
        //     // Distribute students across schools (for demo purposes)
        //     $schoolId = $schools->get($index % $schools->count())->id;
            
        //     $users[] = [
        //         'name' => $name,
        //         'email' => strtolower(str_replace(' ', '.', $name)) . '@example.com',
        //         'password' => Hash::make('password'),
        //         'role' => 'siswa',
        //         'school_id' => $schoolId, // Assign students to schools
        //     ];
        // }

        // $usersCreated = 0;
          $users = [
            // Staff Sekolah 1
            [
                'name' => 'Bu Sari Wijaya',
                'email' => 'sari.wijaya@sman1jakarta.sch.id',
                'password' => Hash::make('password123'),
                'role' => 'staff',
                'school_id' => 1,
                'email_verified_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Pak Budi Santoso',
                'email' => 'budi.santoso@sman1jakarta.sch.id',
                'password' => Hash::make('password123'),
                'role' => 'staff',
                'school_id' => 1,
                'email_verified_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // Koordinator Sekolah 1
            [
                'name' => 'Pak Joko Susilo',
                'email' => 'joko.susilo@sman1jakarta.sch.id',
                'password' => Hash::make('password123'),
                'role' => 'koordinator',
                'school_id' => 1,
                'email_verified_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Bu Rina Sari',
                'email' => 'rina.sari@sman1jakarta.sch.id',
                'password' => Hash::make('password123'),
                'role' => 'koordinator',
                'school_id' => 1,
                'email_verified_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Pak Heru Prasetyo',
                'email' => 'heru.prasetyo@sman1jakarta.sch.id',
                'password' => Hash::make('password123'),
                'role' => 'koordinator',
                'school_id' => 1,
                'email_verified_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // Bendahara Sekolah 1 (siswa)
            [
                'name' => 'Andi Pratama',
                'email' => 'andi.pratama@student.sman1jakarta.sch.id',
                'password' => Hash::make('password123'),
                'role' => 'bendahara',
                'school_id' => 1,
                'email_verified_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Eka Putri',
                'email' => 'eka.putri@student.sman1jakarta.sch.id',
                'password' => Hash::make('password123'),
                'role' => 'bendahara',
                'school_id' => 1,
                'email_verified_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Hani Sari',
                'email' => 'hani.sari@student.sman1jakarta.sch.id',
                'password' => Hash::make('password123'),
                'role' => 'bendahara',
                'school_id' => 1,
                'email_verified_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // Member/Siswa Sekolah 1
            [
                'name' => 'Budi Setiawan',
                'email' => 'budi.setiawan@student.sman1jakarta.sch.id',
                'password' => Hash::make('password123'),
                'role' => 'siswa',
                'school_id' => 1,
                'email_verified_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Cici Amanda',
                'email' => 'cici.amanda@student.sman1jakarta.sch.id',
                'password' => Hash::make('password123'),
                'role' => 'siswa',
                'school_id' => 1,
                'email_verified_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Dedi Rahman',
                'email' => 'dedi.rahman@student.sman1jakarta.sch.id',
                'password' => Hash::make('password123'),
                'role' => 'siswa',
                'school_id' => 1,
                'email_verified_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Fani Lestari',
                'email' => 'fani.lestari@student.sman1jakarta.sch.id',
                'password' => Hash::make('password123'),
                'role' => 'siswa',
                'school_id' => 1,
                'email_verified_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Gita Sari',
                'email' => 'gita.sari@student.sman1jakarta.sch.id',
                'password' => Hash::make('password123'),
                'role' => 'siswa',
                'school_id' => 1,
                'email_verified_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Iko Firmansyah',
                'email' => 'iko.firmansyah@student.sman1jakarta.sch.id',
                'password' => Hash::make('password123'),
                'role' => 'siswa',
                'school_id' => 1,
                'email_verified_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Joni Kurniawan',
                'email' => 'joni.kurniawan@student.sman1jakarta.sch.id',
                'password' => Hash::make('password123'),
                'role' => 'siswa',
                'school_id' => 1,
                'email_verified_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'zulfikar',
                'email' => 'zulfikar@student.sman1jakarta.sch.id',
                'password' => Hash::make('password123'),
                'role' => 'siswa',
                'school_id' => 1,
                'email_verified_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'edwin',
                'email' => 'edwin@student.sman1jakarta.sch.id',
                'password' => Hash::make('password123'),
                'role' => 'siswa',
                'school_id' => 1,
                'email_verified_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Imron',
                'email' => 'imron@student.sman1jakarta.sch.id',
                'password' => Hash::make('password123'),
                'role' => 'siswa',
                'school_id' => 1,
                'email_verified_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // Staff Sekolah 2
            [
                'name' => 'Bu Maya Indri',
                'email' => 'maya.indri@sman2jakarta.sch.id',
                'password' => Hash::make('password123'),
                'role' => 'staff',
                'school_id' => 2,
                'email_verified_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // Koordinator Sekolah 2
            [
                'name' => 'Pak Ahmad Fauzi',
                'email' => 'ahmad.fauzi@sman2jakarta.sch.id',
                'password' => Hash::make('password123'),
                'role' => 'koordinator',
                'school_id' => 2,
                'email_verified_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Bu Lina Marlina',
                'email' => 'lina.marlina@sman2jakarta.sch.id',
                'password' => Hash::make('password123'),
                'role' => 'koordinator',
                'school_id' => 2,
                'email_verified_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // Bendahara Sekolah 2
            [
                'name' => 'Rina Permata',
                'email' => 'rina.permata@student.sman2jakarta.sch.id',
                'password' => Hash::make('password123'),
                'role' => 'bendahara',
                'school_id' => 2,
                'email_verified_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Doni Setiawan',
                'email' => 'doni.setiawan@student.sman2jakarta.sch.id',
                'password' => Hash::make('password123'),
                'role' => 'bendahara',
                'school_id' => 2,
                'email_verified_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // Member Sekolah 2
            [
                'name' => 'Lisa Anggraini',
                'email' => 'lisa.anggraini@student.sman2jakarta.sch.id',
                'password' => Hash::make('password123'),
                'role' => 'siswa',
                'school_id' => 2,
                'email_verified_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Raka Mahendra',
                'email' => 'raka.mahendra@student.sman2jakarta.sch.id',
                'password' => Hash::make('password123'),
                'role' => 'siswa',
                'school_id' => 2,
                'email_verified_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];
        foreach ($users as $user) {
            User::create($user);
            // $usersCreated++;
        }
        
        // $this->command->info("Created {$this->formatNumber($usersCreated)} users.");
    }

    private function formatNumber($number) {
        return number_format($number, 0, ',', '.');
    }
}