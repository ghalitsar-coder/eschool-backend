<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class NewUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Multi-role user seeder untuk sistem baru
     */
    public function run(): void
    {
        $users = [
            // === STAFF USERS ===
            [
                'name' => 'Staff System Administrator',
                'email' => 'staff.admin@eschool.com',
                'password' => Hash::make('password123'),
                'base_role' => 'staff',
                'is_system_admin' => true,
                'email_verified_at' => now(),
            ],
            [
                'name' => 'Staff Jakarta',
                'email' => 'staff.jakarta@eschool.com',
                'password' => Hash::make('password123'),
                'base_role' => 'staff',
                'is_system_admin' => false,
                'email_verified_at' => now(),
            ],
            [
                'name' => 'Staff Bandung',
                'email' => 'staff.bandung@eschool.com',
                'password' => Hash::make('password123'),
                'base_role' => 'staff',
                'is_system_admin' => false,
                'email_verified_at' => now(),
            ],

            // === KOORDINATOR USERS ===
            [
                'name' => 'Koordinator Karate Jakarta',
                'email' => 'koordinator.karate@gmail.com',
                'password' => Hash::make('password123'),
                'base_role' => 'guru',
                'is_system_admin' => false,
                'email_verified_at' => now(),
            ],
            [
                'name' => 'Koordinator Paskibra Jakarta',
                'email' => 'koordinator.paskibra@gmail.com',
                'password' => Hash::make('password123'),
                'base_role' => 'guru',
                'is_system_admin' => false,
                'email_verified_at' => now(),
            ],
            [
                'name' => 'Koordinator Basket Bandung',
                'email' => 'koordinator.basket@gmail.com',
                'password' => Hash::make('password123'),
                'base_role' => 'guru',
                'is_system_admin' => false,
                'email_verified_at' => now(),
            ],

            // === BENDAHARA USERS (SISWA) ===
            [
                'name' => 'Bendahara Karate Jakarta',
                'email' => 'bendahara.karate@gmail.com',
                'password' => Hash::make('password123'),
                'base_role' => 'siswa', // Bendahara adalah siswa
                'is_system_admin' => false,
                'email_verified_at' => now(),
            ],
            [
                'name' => 'Bendahara Paskibra Jakarta',
                'email' => 'bendahara.paskibra@gmail.com',
                'password' => Hash::make('password123'),
                'base_role' => 'siswa', // Bendahara adalah siswa
                'is_system_admin' => false,
                'email_verified_at' => now(),
            ],
            [
                'name' => 'Bendahara Basket Bandung',
                'email' => 'bendahara.basket@gmail.com',
                'password' => Hash::make('password123'),
                'base_role' => 'siswa', // Bendahara adalah siswa
                'is_system_admin' => false,
                'email_verified_at' => now(),
            ],

            // === MEMBER USERS (Yang akan punya multi-role) ===
            [
                'name' => 'Ahmad Rizki Pratama',
                'email' => 'ahmad.rizki@student.com',
                'password' => Hash::make('password123'),
                'base_role' => 'siswa',
                'is_system_admin' => false,
                'email_verified_at' => now(),
            ],
            [
                'name' => 'Siti Nurhaliza Putri',
                'email' => 'siti.nurhaliza@student.com',
                'password' => Hash::make('password123'),
                'base_role' => 'siswa',
                'is_system_admin' => false,
                'email_verified_at' => now(),
            ],
            [
                'name' => 'Budi Santoso Wijaya',
                'email' => 'budi.santoso@student.com',
                'password' => Hash::make('password123'),
                'base_role' => 'siswa',
                'is_system_admin' => false,
                'email_verified_at' => now(),
            ],
            [
                'name' => 'Indira Sari Dewi',
                'email' => 'indira.sari@student.com',
                'password' => Hash::make('password123'),
                'base_role' => 'siswa',
                'is_system_admin' => false,
                'email_verified_at' => now(),
            ],
            [
                'name' => 'Eko Prasetyo Hadi',
                'email' => 'eko.prasetyo@student.com',
                'password' => Hash::make('password123'),
                'base_role' => 'siswa',
                'is_system_admin' => false,
                'email_verified_at' => now(),
            ],
            [
                'name' => 'Rina Melati Sari',
                'email' => 'rina.melati@student.com',
                'password' => Hash::make('password123'),
                'base_role' => 'siswa',
                'is_system_admin' => false,
                'email_verified_at' => now(),
            ],

            // === USERS UNTUK MULTI-ROLE TESTING ===
            [
                'name' => 'Multi Role User 1',
                'email' => 'multirole1@student.com',
                'password' => Hash::make('password123'),
                'base_role' => 'siswa',
                'is_system_admin' => false,
                'email_verified_at' => now(),
            ],
            [
                'name' => 'Multi Role User 2',
                'email' => 'multirole2@student.com',
                'password' => Hash::make('password123'),
                'base_role' => 'siswa',
                'is_system_admin' => false,
                'email_verified_at' => now(),
            ],
            [
                'name' => 'Test Multi Member 1',
                'email' => 'testmulti1@student.com',
                'password' => Hash::make('password123'),
                'base_role' => 'siswa',
                'is_system_admin' => false,
                'email_verified_at' => now(),
            ],
            [
                'name' => 'Test Multi Member 2',
                'email' => 'testmulti2@student.com',
                'password' => Hash::make('password123'),
                'base_role' => 'siswa',
                'is_system_admin' => false,
                'email_verified_at' => now(),
            ],
        ];

        foreach ($users as $userData) {
            User::create($userData);
        }

        $this->command->info('✅ Users seeded successfully! Created ' . count($users) . ' users.');
        $this->command->info('📧 Default password for all users: password123');
        $this->command->line('');
        $this->command->info('🔐 Login credentials:');
        $this->command->line('Staff Admin: staff.admin@eschool.com / password123');
        $this->command->line('Koordinator Karate: koordinator.karate@gmail.com / password123');
        $this->command->line('Bendahara Karate: bendahara.karate@gmail.com / password123');
        $this->command->line('Student Multi-role: multirole1@student.com / password123');
    }
}
