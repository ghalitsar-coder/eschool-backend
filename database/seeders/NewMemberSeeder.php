<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Member;

class NewMemberSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Seeder untuk Member berdasarkan multi-role system
     */
    public function run(): void
    {
        $members = [
            // === MEMBERS JAKARTA (SCHOOL_ID = 1) ===
            [
                'school_id' => 1,
                'user_id' => 10, // Ahmad Rizki Pratama
                'student_id' => 'SMA1JKT001',
                'nip' => null,
                'name' => 'Ahmad Rizki Pratama',
                'date_of_birth' => '2007-03-15',
                'gender' => 'L',
                'address' => 'Jl. Melati No. 15, Jakarta Selatan',
                'phone' => '08123456789',
                'status' => 'active',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'school_id' => 1,
                'user_id' => 11, // Siti Nurhaliza Putri
                'student_id' => 'SMA1JKT002',
                'nip' => null,
                'name' => 'Siti Nurhaliza Putri',
                'date_of_birth' => '2007-07-22',
                'gender' => 'P',
                'address' => 'Jl. Mawar No. 8, Jakarta Timur',
                'phone' => '08234567890',
                'status' => 'active',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'school_id' => 1,
                'user_id' => 12, // Budi Santoso Wijaya
                'student_id' => 'SMA1JKT003',
                'nip' => null,
                'name' => 'Budi Santoso Wijaya',
                'date_of_birth' => '2006-12-10',
                'gender' => 'L',
                'address' => 'Jl. Anggrek No. 23, Jakarta Barat',
                'phone' => '08345678901',
                'status' => 'active',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'school_id' => 1,
                'user_id' => 13, // Indira Sari Dewi
                'student_id' => 'SMA1JKT004',
                'nip' => null,
                'name' => 'Indira Sari Dewi',
                'date_of_birth' => '2007-05-18',
                'gender' => 'P',
                'address' => 'Jl. Cendana No. 45, Jakarta Utara',
                'phone' => '08456789012',
                'status' => 'active',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // === MEMBERS BANDUNG (SCHOOL_ID = 2) ===
            [
                'school_id' => 2,
                'user_id' => 14, // Eko Prasetyo Hadi
                'student_id' => 'SMA2BDG001',
                'nip' => null,
                'name' => 'Eko Prasetyo Hadi',
                'date_of_birth' => '2007-01-25',
                'gender' => 'L',
                'address' => 'Jl. Dago No. 67, Bandung',
                'phone' => '08567890123',
                'status' => 'active',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'school_id' => 2,
                'user_id' => 15, // Rina Melati Sari
                'student_id' => 'SMA2BDG002',
                'nip' => null,
                'name' => 'Rina Melati Sari',
                'date_of_birth' => '2007-09-14',
                'gender' => 'P',
                'address' => 'Jl. Braga No. 89, Bandung',
                'phone' => '08678901234',
                'status' => 'active',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // === MULTI-ROLE MEMBERS ===
            [
                'school_id' => 1,
                'user_id' => 16, // Multi Role User 1
                'student_id' => 'SMA1JKT005',
                'nip' => null,
                'name' => 'Multi Role User 1',
                'date_of_birth' => '2006-11-03',
                'gender' => 'L',
                'address' => 'Jl. Multi Role No. 1, Jakarta',
                'phone' => '08789012345',
                'status' => 'active',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'school_id' => 1,
                'user_id' => 17, // Multi Role User 2
                'student_id' => 'SMA1JKT006',
                'nip' => null,
                'name' => 'Multi Role User 2',
                'date_of_birth' => '2007-04-08',
                'gender' => 'P',
                'address' => 'Jl. Multi Role No. 2, Jakarta',
                'phone' => '08890123456',
                'status' => 'active',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($members as $memberData) {
            Member::create($memberData);
        }

        $this->command->info('✅ Members seeded successfully! Created ' . count($members) . ' members.');
        $this->command->line('');
        $this->command->info('👥 Members created:');
        $this->command->line('📍 Jakarta (School 1): 7 members');
        $this->command->line('📍 Bandung (School 2): 2 members');
        $this->command->line('🔄 Multi-role test users: 2 members');
    }
}
