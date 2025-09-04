<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AllEntitySeeder extends Seeder
{
    /**
     * Run all seeders in a single file
     */
    public function run()
    {
        // Schools data
        $schools = [
            [
                'name' => 'SMA Negeri 1 Jakarta',
                'address' => 'Jl. Merdeka No. 123, Jakarta Pusat',
                'phone' => '021-12345678',
                'email' => 'info@sman1jakarta.sch.id',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'SMA Negeri 2 Jakarta', 
                'address' => 'Jl. Kemerdekaan No. 456, Jakarta Selatan',
                'phone' => '021-87654321',
                'email' => 'info@sman2jakarta.sch.id',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'SMA Negeri 3 Jakarta',
                'address' => 'Jl. Pemuda No. 789, Jakarta Timur',
                'phone' => '021-11223344',
                'email' => 'info@sman3jakarta.sch.id',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('schools')->insert($schools);
        echo "Schools seeded.\n";

        // Users data
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

        DB::table('users')->insert($users);
        echo "Users seeded.\n";

        // Eschools data
        $eschools = [
            // Eschools untuk Sekolah 1 (SMA Negeri 1 Jakarta)
            [
                'school_id' => 1,
                'coordinator_id' => 3, // Pak Joko Susilo
                'treasurer_id' => 6,   // Andi Pratama
                'name' => 'Basket',
                'description' => 'Ekstrakurikuler Bola Basket untuk mengembangkan kemampuan olahraga dan kerjasama tim',
                'monthly_kas_amount' => 25000,
                'schedule_days' => json_encode(['Selasa', 'Kamis', 'Sabtu']),
                'total_schedule_days' => 3,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'school_id' => 1,
                'coordinator_id' => 4, // Bu Rina Sari
                'treasurer_id' => 7,   // Eka Putri
                'name' => 'Voli',
                'description' => 'Ekstrakurikuler Bola Voli untuk meningkatkan koordinasi dan sportivitas',
                'monthly_kas_amount' => 20000,
                'schedule_days' => json_encode(['Senin', 'Rabu', 'Jumat']),
                'total_schedule_days' => 3,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'school_id' => 1,
                'coordinator_id' => 5, // Pak Heru Prasetyo
                'treasurer_id' => 8,   // Hani Sari
                'name' => 'Lukis',
                'description' => 'Ekstrakurikuler Seni Lukis untuk mengembangkan kreativitas dan bakat seni',
                'monthly_kas_amount' => 30000,
                'schedule_days' => json_encode(['Kamis', 'Sabtu']),
                'total_schedule_days' => 2,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // Eschools untuk Sekolah 2 (SMA Negeri 2 Jakarta)
            [
                'school_id' => 2,
                'coordinator_id' => 15, // Pak Ahmad Fauzi
                'treasurer_id' => 17,   // Rina Permata
                'name' => 'Matematika',
                'description' => 'Ekstrakurikuler Olimpiade Matematika untuk mengasah kemampuan logika dan pemecahan masalah',
                'monthly_kas_amount' => 15000,
                'schedule_days' => json_encode(['Selasa', 'Kamis']),
                'total_schedule_days' => 2,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'school_id' => 2,
                'coordinator_id' => 16, // Bu Lina Marlina
                'treasurer_id' => 18,   // Doni Setiawan
                'name' => 'Teater',
                'description' => 'Ekstrakurikuler Teater untuk mengembangkan kepercayaan diri dan kemampuan berakting',
                'monthly_kas_amount' => 35000,
                'schedule_days' => json_encode(['Rabu', 'Jumat', 'Sabtu']),
                'total_schedule_days' => 3,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('eschools')->insert($eschools);
        echo "Eschools seeded.\n";

        // Members data
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

        DB::table('members')->insert($members);
        echo "Members seeded.\n";

        // Eschool-Member relationships
        $eschoolMembers = [
            // Eschool Basket (ID: 1) - Sekolah 1
            ['eschool_id' => 1, 'member_id' => 1], // Andi (bendahara + member)
            ['eschool_id' => 1, 'member_id' => 4], // Budi 
            ['eschool_id' => 1, 'member_id' => 5], // Cici
            ['eschool_id' => 1, 'member_id' => 6], // Dedi

            // Eschool Voli (ID: 2) - Sekolah 1  
            ['eschool_id' => 2, 'member_id' => 2], // Eka (bendahara + member)
            ['eschool_id' => 2, 'member_id' => 7], // Fani
            ['eschool_id' => 2, 'member_id' => 8], // Gita
            ['eschool_id' => 2, 'member_id' => 1], // Andi (ikut sebagai member biasa)

            // Eschool Lukis (ID: 3) - Sekolah 1
            ['eschool_id' => 3, 'member_id' => 3], // Hani (bendahara + member)
            ['eschool_id' => 3, 'member_id' => 9], // Iko
            ['eschool_id' => 3, 'member_id' => 4], // Budi (ikut sebagai member di eschool lain)

            // Eschool Matematika (ID: 4) - Sekolah 2
            ['eschool_id' => 4, 'member_id' => 10], // Rina Permata (bendahara + member)
            ['eschool_id' => 4, 'member_id' => 12], // Lisa
            ['eschool_id' => 4, 'member_id' => 13], // Raka

            // Eschool Teater (ID: 5) - Sekolah 2
            ['eschool_id' => 5, 'member_id' => 11], // Doni (bendahara + member)
            ['eschool_id' => 5, 'member_id' => 12], // Lisa (ikut sebagai member di eschool lain)
            ['eschool_id' => 5, 'member_id' => 10], // Rina (ikut sebagai member biasa)
        ];

        foreach ($eschoolMembers as &$eschoolMember) {
            $eschoolMember['created_at'] = now();
            $eschoolMember['updated_at'] = now();
        }

        DB::table('eschool_member')->insert($eschoolMembers);
        echo "Eschool-Member relationships seeded.\n";

        // Kas Records
        $kasRecords = [
            // Kas Records untuk Eschool Basket (ID: 1)
            [
                'eschool_id' => 1,
                'recorder_id' => 6, // Andi (bendahara)
                'type' => 'income',
                'amount' => 100000,
                'description' => 'Kas bulanan anggota bulan Agustus',
                'category' => 'Kas Bulanan',
                'date' => '2025-08-01 10:00:00',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'eschool_id' => 1,
                'recorder_id' => 6, // Andi (bendahara)
                'type' => 'expense',
                'amount' => 50000,
                'description' => 'Pembelian bola basket baru',
                'category' => 'Peralatan',
                'date' => '2025-08-05 14:30:00',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'eschool_id' => 1,
                'recorder_id' => 3, // Pak Joko (koordinator)
                'type' => 'expense',
                'amount' => 25000,
                'description' => 'Biaya transportasi ke pertandingan',
                'category' => 'Transportasi',
                'date' => '2025-08-10 09:15:00',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // Kas Records untuk Eschool Voli (ID: 2)
            [
                'eschool_id' => 2,
                'recorder_id' => 7, // Eka (bendahara)
                'type' => 'income',
                'amount' => 80000,
                'description' => 'Kas bulanan anggota bulan Agustus',
                'category' => 'Kas Bulanan',
                'date' => '2025-08-01 11:00:00',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'eschool_id' => 2,
                'recorder_id' => 7, // Eka (bendahara)
                'type' => 'expense',
                'amount' => 30000,
                'description' => 'Pembelian net voli',
                'category' => 'Peralatan',
                'date' => '2025-08-07 16:20:00',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // Kas Records untuk Eschool Lukis (ID: 3)
            [
                'eschool_id' => 3,
                'recorder_id' => 8, // Hani (bendahara)
                'type' => 'expense',
                'amount' => 45000,
                'description' => 'Pembelian cat dan kuas lukis',
                'category' => 'Peralatan',
                'date' => '2025-08-08 13:45:00',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // Kas Records untuk Eschool Matematika (ID: 4) - Sekolah 2
            [
                'eschool_id' => 4,
                'recorder_id' => 17, // Rina Permata (bendahara)
                'type' => 'income',
                'amount' => 45000,
                'description' => 'Kas bulanan anggota bulan Agustus',
                'category' => 'Kas Bulanan',
                'date' => '2025-08-01 13:00:00',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'eschool_id' => 4,
                'recorder_id' => 17, // Rina Permata (bendahara)
                'type' => 'expense',
                'amount' => 20000,
                'description' => 'Pembelian buku latihan soal olimpiade',
                'category' => 'Buku dan Materi',
                'date' => '2025-08-12 15:30:00',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // Kas Records untuk Eschool Teater (ID: 5) - Sekolah 2
            [
                'eschool_id' => 5,
                'recorder_id' => 18, // Doni (bendahara)
                'type' => 'income',
                'amount' => 105000,
                'description' => 'Kas bulanan anggota bulan Agustus',
                'category' => 'Kas Bulanan',
                'date' => '2025-08-01 14:00:00',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'eschool_id' => 5,
                'recorder_id' => 18, // Doni (bendahara)
                'type' => 'expense',
                'amount' => 60000,
                'description' => 'Pembelian kostum untuk pementasan',
                'category' => 'Kostum dan Properti',
                'date' => '2025-08-15 10:20:00',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('kas_records')->insert($kasRecords);
        echo "Kas records seeded.\n";

        // Kas Payments
        $kasPayments = [
            // Pembayaran Kas untuk Eschool Basket (Agustus 2025)
            [
                'member_id' => 1, // Andi
                'kas_record_id' => 1,
                'amount' => 25000,
                'month' => 8,
                'year' => 2025,
                'is_paid' => true,
                'paid_date' => '2025-08-01 10:00:00',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'member_id' => 4, // Budi
                'kas_record_id' => 1,
                'amount' => 25000,
                'month' => 8,
                'year' => 2025,
                'is_paid' => true,
                'paid_date' => '2025-08-02 11:30:00',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'member_id' => 5, // Cici
                'kas_record_id' => 1,
                'amount' => 25000,
                'month' => 8,
                'year' => 2025,
                'is_paid' => true,
                'paid_date' => '2025-08-03 09:45:00',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'member_id' => 6, // Dedi
                'kas_record_id' => 1,
                'amount' => 25000,
                'month' => 8,
                'year' => 2025,
                'is_paid' => false,
                'paid_date' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // Pembayaran Kas untuk Eschool Voli (Agustus 2025)
            [
                'member_id' => 2, // Eka
                'kas_record_id' => 4,
                'amount' => 20000,
                'month' => 8,
                'year' => 2025,
                'is_paid' => true,
                'paid_date' => '2025-08-01 11:00:00',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'member_id' => 7, // Fani
                'kas_record_id' => 4,
                'amount' => 20000,
                'month' => 8,
                'year' => 2025,
                'is_paid' => true,
                'paid_date' => '2025-08-04 14:15:00',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'member_id' => 8, // Gita
                'kas_record_id' => 4,
                'amount' => 20000,
                'month' => 8,
                'year' => 2025,
                'is_paid' => true,
                'paid_date' => '2025-08-05 16:30:00',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'member_id' => 1, // Andi (ikut voli sebagai member)
                'kas_record_id' => 4,
                'amount' => 20000,
                'month' => 8,
                'year' => 2025,
                'is_paid' => false,
                'paid_date' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // Pembayaran Kas untuk Eschool Lukis (Agustus 2025)
            [
                'member_id' => 3, // Hani
                'kas_record_id' => 6,
                'amount' => 30000,
                'month' => 8,
                'year' => 2025,
                'is_paid' => true,
                'paid_date' => '2025-08-01 12:00:00',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'member_id' => 9, // Iko
                'kas_record_id' => 6,
                'amount' => 30000,
                'month' => 8,
                'year' => 2025,
                'is_paid' => true,
                'paid_date' => '2025-08-06 10:45:00',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'member_id' => 4, // Budi (ikut lukis sebagai member)
                'kas_record_id' => 6,
                'amount' => 30000,
                'month' => 8,
                'year' => 2025,
                'is_paid' => false,
                'paid_date' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // Pembayaran Kas untuk Eschool Matematika - Sekolah 2 (Agustus 2025)
            [
                'member_id' => 10, // Rina Permata
                'kas_record_id' => 7,
                'amount' => 15000,
                'month' => 8,
                'year' => 2025,
                'is_paid' => true,
                'paid_date' => '2025-08-01 13:00:00',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'member_id' => 12, // Lisa
                'kas_record_id' => 7,
                'amount' => 15000,
                'month' => 8,
                'year' => 2025,
                'is_paid' => true,
                'paid_date' => '2025-08-08 15:20:00',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'member_id' => 13, // Raka
                'kas_record_id' => 7,
                'amount' => 15000,
                'month' => 8,
                'year' => 2025,
                'is_paid' => false,
                'paid_date' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // Pembayaran Kas untuk Eschool Teater - Sekolah 2 (Agustus 2025)
            [
                'member_id' => 11, // Doni
                'kas_record_id' => 9,
                'amount' => 35000,
                'month' => 8,
                'year' => 2025,
                'is_paid' => true,
                'paid_date' => '2025-08-01 14:00:00',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'member_id' => 12, // Lisa (ikut teater juga)
                'kas_record_id' => 9,
                'amount' => 35000,
                'month' => 8,
                'year' => 2025,
                'is_paid' => true,
                'paid_date' => '2025-08-09 13:10:00',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'member_id' => 10, // Rina (ikut teater sebagai member)
                'kas_record_id' => 9,
                'amount' => 35000,
                'month' => 8,
                'year' => 2025,
                'is_paid' => false,
                'paid_date' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('kas_payments')->insert($kasPayments);
        echo "Kas payments seeded.\n";

        // Attendance Records
        $attendanceRecords = [];
        
        // Generate attendance records untuk beberapa hari terakhir
        $dates = [
            '2025-08-26', '2025-08-27', '2025-08-28', '2025-08-29', '2025-08-30'
        ];

        foreach ($dates as $date) {
            // Attendance untuk Eschool Basket (ID: 1)
            $basketMembers = [1, 4, 5, 6]; // Andi, Budi, Cici, Dedi
            foreach ($basketMembers as $memberId) {
                $attendanceRecords[] = [
                    'eschool_id' => 1,
                    'member_id' => $memberId,
                    'recorder_id' => 3, // Pak Joko (koordinator)
                    'date' => $date . ' 15:30:00',
                    'is_present' => rand(0, 1) ? true : false,
                    'notes' => rand(0, 1) ? null : 'Sakit',
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            // Attendance untuk Eschool Voli (ID: 2)
            $voliMembers = [2, 7, 8, 1]; // Eka, Fani, Gita, Andi
            foreach ($voliMembers as $memberId) {
                $attendanceRecords[] = [
                    'eschool_id' => 2,
                    'member_id' => $memberId,
                    'recorder_id' => 4, // Bu Rina (koordinator)
                    'date' => $date . ' 16:00:00',
                    'is_present' => rand(0, 1) ? true : false,
                    'notes' => rand(0, 1) ? null : 'Izin',
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            // Attendance untuk Eschool Lukis (ID: 3)
            $lukisMembers = [3, 9, 4]; // Hani, Iko, Budi
            foreach ($lukisMembers as $memberId) {
                $attendanceRecords[] = [
                    'eschool_id' => 3,
                    'member_id' => $memberId,
                    'recorder_id' => 5, // Pak Heru (koordinator)
                    'date' => $date . ' 14:00:00',
                    'is_present' => rand(0, 1) ? true : false,
                    'notes' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            // Attendance untuk Eschool Matematika (ID: 4) - Sekolah 2
            $matematikaMembers = [10, 12, 13]; // Rina, Lisa, Raka
            foreach ($matematikaMembers as $memberId) {
                $attendanceRecords[] = [
                    'eschool_id' => 4,
                    'member_id' => $memberId,
                    'recorder_id' => 15, // Pak Ahmad (koordinator)
                    'date' => $date . ' 15:00:00',
                    'is_present' => rand(0, 1) ? true : false,
                    'notes' => rand(0, 1) ? null : 'Terlambat',
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            // Attendance untuk Eschool Teater (ID: 5) - Sekolah 2
            $teaterMembers = [11, 12, 10]; // Doni, Lisa, Rina
            foreach ($teaterMembers as $memberId) {
                $attendanceRecords[] = [
                    'eschool_id' => 5,
                    'member_id' => $memberId,
                    'recorder_id' => 16, // Bu Lina (koordinator)
                    'date' => $date . ' 16:30:00',
                    'is_present' => rand(0, 1) ? true : false,
                    'notes' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        // Insert attendance records in chunks untuk performa yang lebih baik
        $chunks = array_chunk($attendanceRecords, 50);
        foreach ($chunks as $chunk) {
            DB::table('attendance_records')->insert($chunk);
        }
        echo "Attendance records seeded.\n";

        echo "All entities seeded successfully!\n";
    }
}