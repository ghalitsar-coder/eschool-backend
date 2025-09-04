<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Member;
use App\Models\Eschool;
use App\Models\User;
use App\Models\School;

class MemberSeeder extends Seeder
{
    public function run(): void
    {
        // $eschools = Eschool::all();
        // $students = User::where('role', 'siswa')->get();

        // if ($eschools->isEmpty() || $students->isEmpty()) {
        //     $this->command->warn('Make sure Eschools and Students exist before running this seeder.');
        //     return;
        // }

        // // Array of realistic Indonesian names
        // $indonesianNames = [
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
        //     'Fitria Nurul', 'Ganda Pratama', 'Hesti Wulandari', 'Irfan Hakim', 'Jamilah Sari'
        // ];

        // // Array of realistic addresses
        // $addresses = [
        //     'Jl. Merdeka No. 123, Jakarta Pusat',
        //     'Jl. Sudirman No. 45, Bandung',
        //     'Jl. Diponegoro No. 67, Surabaya',
        //     'Jl. Thamrin No. 89, Medan',
        //     'Jl. Gatot Subroto No. 101, Yogyakarta',
        //     'Jl. Ahmad Yani No. 23, Semarang',
        //     'Jl. Pahlawan No. 45, Malang',
        //     'Jl. Kartini No. 67, Solo',
        //     'Jl. Basuki Rahmat No. 89, Denpasar',
        //     'Jl. Imam Bonjol No. 101, Makassar',
        //     'Jl. Panglima Polim No. 12, Jakarta Selatan',
        //     'Jl. Asia Afrika No. 34, Bandung',
        //     'Jl. Tunjungan No. 56, Surabaya',
        //     'Jl. Veteran No. 78, Yogyakarta',
        //     'Jl. Pemuda No. 90, Semarang'
        // ];

        // $membersCreated = 0;

        // foreach ($eschools as $eschool) {
        //     // Get students from the same school as the eschool
        //     $schoolStudents = $students->filter(function ($student) use ($eschool) {
        //         return $student->school_id === $eschool->school_id;
        //     });
            
        //     if ($schoolStudents->isEmpty()) {
        //         continue;
        //     }

        //     // Setiap eschool memiliki 8-15 member
        //     $memberCount = min(rand(8, 15), $schoolStudents->count());
            
        //     $studentIndex = 0;
        //     for ($i = 0; $i < $memberCount; $i++) {
        //         if ($studentIndex >= $schoolStudents->count()) {
        //             break;
        //         }

        //         $student = $schoolStudents[$studentIndex];
                
        //         // Generate realistic member data
        //         $gender = (rand(0, 1) == 0) ? 'L' : 'P';
        //         $birthYear = rand(2005, 2008); // Typical high school student age
        //         $birthMonth = rand(1, 12);
        //         $birthDay = rand(1, 28); // To avoid issues with February
                
        //         $memberData = [
        //             'school_id' => $student->school_id, // Using school_id from user
        //             'user_id' => $student->id,
        //             'nip' => 'NIP' . str_pad($membersCreated + 1, 5, '0', STR_PAD_LEFT),
        //             'name' => $student->name, // Will be synced from user, but we set it explicitly for clarity
        //             'student_id' => 'STD' . str_pad($membersCreated + 1, 5, '0', STR_PAD_LEFT),
        //             'date_of_birth' => "{$birthYear}-{$birthMonth}-{$birthDay}",
        //             'gender' => $gender,
        //             'address' => $addresses[array_rand($addresses)],
        //             'phone' => '08' . rand(1000000000, 9999999999),
        //             'status' => 'active',
        //             'is_active' => rand(0, 10) > 1, // 90% aktif
        //             'created_at' => now(),
        //             'updated_at' => now(),
        //         ];

        //         $member = Member::create($memberData);
                
        //         // Attach member to eschool using many-to-many relationship
        //         $member->eschools()->attach($eschool->id);
                
        //         $studentIndex++;
        //         $membersCreated++;
        //     }
        // }
        
        // $this->command->info("Created {$this->formatNumber($membersCreated)} members.");
         $members = [
            // Members untuk Sekolah 1
            [
                'school_id' => 1,
                'user_id' => 6, // Andi Pratama (bendahara)
                'student_id' => '2024001',
                'name' => 'Andi Pratama',
                'date_of_birth' => '2007-03-15',
                'gender' => 'L',
                'address' => 'Jl. Mawar No. 12, Jakarta Pusat',
                'phone' => '081234567890',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'school_id' => 1,
                'user_id' => 7, // Eka Putri (bendahara)
                'student_id' => '2024002',
                'name' => 'Eka Putri',
                'date_of_birth' => '2007-05-20',
                'gender' => 'P',
                'address' => 'Jl. Melati No. 25, Jakarta Pusat',
                'phone' => '081234567891',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'school_id' => 1,
                'user_id' => 8, // Hani Sari (bendahara)
                'student_id' => '2024003',
                'name' => 'Hani Sari',
                'date_of_birth' => '2007-08-10',
                'gender' => 'P',
                'address' => 'Jl. Anggrek No. 8, Jakarta Pusat',
                'phone' => '081234567892',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'school_id' => 1,
                'user_id' => 9, // Budi Setiawan (siswa)
                'student_id' => '2024004',
                'name' => 'Budi Setiawan',
                'date_of_birth' => '2007-01-25',
                'gender' => 'L',
                'address' => 'Jl. Kenanga No. 15, Jakarta Pusat',
                'phone' => '081234567893',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'school_id' => 1,
                'user_id' => 10, // Cici Amanda (siswa)
                'student_id' => '2024005',
                'name' => 'Cici Amanda',
                'date_of_birth' => '2007-06-30',
                'gender' => 'P',
                'address' => 'Jl. Dahlia No. 22, Jakarta Pusat',
                'phone' => '081234567894',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'school_id' => 1,
                'user_id' => 11, // Dedi Rahman (siswa)
                'student_id' => '2024006',
                'name' => 'Dedi Rahman',
                'date_of_birth' => '2007-09-12',
                'gender' => 'L',
                'address' => 'Jl. Tulip No. 5, Jakarta Pusat',
                'phone' => '081234567895',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'school_id' => 1,
                'user_id' => 12, // Fani Lestari (siswa)
                'student_id' => '2024007',
                'name' => 'Fani Lestari',
                'date_of_birth' => '2007-11-18',
                'gender' => 'P',
                'address' => 'Jl. Sakura No. 18, Jakarta Pusat',
                'phone' => '081234567896',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'school_id' => 1,
                'user_id' => 13, // Gita Sari (siswa)
                'student_id' => '2024008',
                'name' => 'Gita Sari',
                'date_of_birth' => '2007-04-08',
                'gender' => 'P',
                'address' => 'Jl. Cempaka No. 30, Jakarta Pusat',
                'phone' => '081234567897',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'school_id' => 1,
                'user_id' => 14, // Iko Firmansyah (siswa)
                'student_id' => '2024009',
                'name' => 'Iko Firmansyah',
                'date_of_birth' => '2007-07-22',
                'gender' => 'L',
                'address' => 'Jl. Seruni No. 7, Jakarta Pusat',
                'phone' => '081234567898',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // Members untuk Sekolah 2
            [
                'school_id' => 2,
                'user_id' => 17, // Rina Permata (bendahara)
                'student_id' => '2024010',
                'name' => 'Rina Permata',
                'date_of_birth' => '2007-02-14',
                'gender' => 'P',
                'address' => 'Jl. Gardenia No. 45, Jakarta Selatan',
                'phone' => '081234567899',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'school_id' => 2,
                'user_id' => 18, // Doni Setiawan (bendahara)
                'student_id' => '2024011',
                'name' => 'Doni Setiawan',
                'date_of_birth' => '2007-10-05',
                'gender' => 'L',
                'address' => 'Jl. Flamboyan No. 33, Jakarta Selatan',
                'phone' => '081234567800',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'school_id' => 2,
                'user_id' => 19, // Lisa Anggraini (siswa)
                'student_id' => '2024012',
                'name' => 'Lisa Anggraini',
                'date_of_birth' => '2007-12-03',
                'gender' => 'P',
                'address' => 'Jl. Bougenville No. 28, Jakarta Selatan',
                'phone' => '081234567801',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'school_id' => 2,
                'user_id' => 20, // Raka Mahendra (siswa)
                'student_id' => '2024013',
                'name' => 'Raka Mahendra',
                'date_of_birth' => '2007-04-17',
                'gender' => 'L',
                'address' => 'Jl. Kamboja No. 11, Jakarta Selatan',
                'phone' => '081234567802',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];
        // DB::table('members')->insert($members);
        // Member::create($members);
        // Contoh untuk MemberSeeder dengan Eloquent
foreach ($members as $memberData) {
    Member::create($memberData);
}
        

    }

    // private function formatNumber($number) {
    //     return number_format($number, 0, ',', '.');
    // }
}