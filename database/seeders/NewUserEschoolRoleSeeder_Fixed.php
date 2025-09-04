<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\UserEschoolRole;
use Carbon\Carbon;

class NewUserEschoolRoleSeeder_Fixed extends Seeder
{
    /**
     * Run the database seeds.
     * Seeder untuk User Eschool Roles berdasarkan QWEN.md rules
     */
    public function run(): void
    {
        $userEschoolRoles = [
            // === KOORDINATOR ROLES (1 koordinator = 1 eschool SAJA) ===
            [
                'user_id' => 4, // Koordinator Karate Jakarta
                'eschool_id' => 1, // Karate Jakarta SAJA
                'role' => 'koordinator',
                'status' => 'active',
                'assigned_at' => now(),
                'notes' => 'Koordinator Karate Jakarta',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => 5, // Koordinator Paskibra Jakarta
                'eschool_id' => 2, // Paskibra Jakarta SAJA
                'role' => 'koordinator',
                'status' => 'active',
                'assigned_at' => now(),
                'notes' => 'Koordinator Paskibra Jakarta',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => 6, // Koordinator Taekwondo Jakarta
                'eschool_id' => 3, // Taekwondo Jakarta SAJA
                'role' => 'koordinator',
                'status' => 'active',
                'assigned_at' => now(),
                'notes' => 'Koordinator Taekwondo Jakarta',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => 16, // Multi Role User 1 jadi Koordinator Basket
                'eschool_id' => 4, // Basket Bandung SAJA
                'role' => 'koordinator',
                'status' => 'active',
                'assigned_at' => now(),
                'notes' => 'Koordinator Basket Bandung',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => 17, // Multi Role User 2 jadi Koordinator Futsal
                'eschool_id' => 5, // Futsal Bandung SAJA
                'role' => 'koordinator',
                'status' => 'active',
                'assigned_at' => now(),
                'notes' => 'Koordinator Futsal Bandung',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // === BENDAHARA ROLES (1 bendahara = 1 eschool SAJA) ===
            [
                'user_id' => 7, // Bendahara Karate Jakarta
                'eschool_id' => 1, // Karate Jakarta SAJA
                'role' => 'bendahara',
                'status' => 'active',
                'assigned_at' => now(),
                'notes' => 'Bendahara untuk Karate Jakarta',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => 8, // Bendahara Paskibra Jakarta
                'eschool_id' => 2, // Paskibra Jakarta SAJA
                'role' => 'bendahara',
                'status' => 'active',
                'assigned_at' => now(),
                'notes' => 'Bendahara Paskibra Jakarta',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => 13, // Indira Sari Dewi - Bendahara Taekwondo
                'eschool_id' => 3, // Taekwondo Jakarta SAJA
                'role' => 'bendahara',
                'status' => 'active',
                'assigned_at' => now(),
                'notes' => 'Bendahara Taekwondo Jakarta',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => 9, // Bendahara Basket Bandung
                'eschool_id' => 4, // Basket Bandung SAJA
                'role' => 'bendahara',
                'status' => 'active',
                'assigned_at' => now(),
                'notes' => 'Bendahara Basket Bandung',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => 15, // Rina Melati Sari - Bendahara Futsal
                'eschool_id' => 5, // Futsal Bandung SAJA
                'role' => 'bendahara',
                'status' => 'active',
                'assigned_at' => now(),
                'notes' => 'Bendahara Futsal Bandung',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // === MULTI-ROLE MEMBERS (Siswa bisa ikut multiple eschool sebagai member) ===

            // Bendahara yang juga jadi member di eschool lain (sesuai aturan QWEN.md)
            [
                'user_id' => 7, // Bendahara Karate (juga member Paskibra)
                'eschool_id' => 2, // Paskibra Jakarta
                'role' => 'siswa',
                'status' => 'active',
                'assigned_at' => now(),
                'notes' => 'Member Paskibra - bendahara hanya di Karate',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => 8, // Bendahara Paskibra (juga member Taekwondo)
                'eschool_id' => 3, // Taekwondo Jakarta
                'role' => 'siswa',
                'status' => 'active',
                'assigned_at' => now(),
                'notes' => 'Member Taekwondo - bendahara hanya di Paskibra',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => 13, // Bendahara Taekwondo (juga member Karate)
                'eschool_id' => 1, // Karate Jakarta
                'role' => 'siswa',
                'status' => 'active',
                'assigned_at' => now(),
                'notes' => 'Member Karate - bendahara hanya di Taekwondo',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // Member biasa (multi-eschool)
            [
                'user_id' => 10, // Ahmad Rizki Pratama
                'eschool_id' => 1, // Karate Jakarta
                'role' => 'siswa',
                'status' => 'active',
                'assigned_at' => now(),
                'notes' => 'Member Karate Jakarta',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => 10, // Ahmad Rizki Pratama (multi-role)
                'eschool_id' => 2, // Paskibra Jakarta
                'role' => 'siswa',
                'status' => 'active',
                'assigned_at' => now(),
                'notes' => 'Member Paskibra Jakarta',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => 11, // Siti Nurhaliza Putri
                'eschool_id' => 1, // Karate Jakarta
                'role' => 'siswa',
                'status' => 'active',
                'assigned_at' => now(),
                'notes' => 'Member Karate Jakarta',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => 11, // Siti Nurhaliza Putri (multi-role)
                'eschool_id' => 3, // Taekwondo Jakarta
                'role' => 'siswa',
                'status' => 'active',
                'assigned_at' => now(),
                'notes' => 'Member Taekwondo Jakarta',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => 12, // Budi Santoso Wijaya
                'eschool_id' => 2, // Paskibra Jakarta
                'role' => 'siswa',
                'status' => 'active',
                'assigned_at' => now(),
                'notes' => 'Member Paskibra Jakarta',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => 14, // Eko Prasetyo Hadi (Bandung)
                'eschool_id' => 4, // Basket Bandung
                'role' => 'siswa',
                'status' => 'active',
                'assigned_at' => now(),
                'notes' => 'Member Basket Bandung',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => 14, // Eko Prasetyo Hadi (multi-role)
                'eschool_id' => 5, // Futsal Bandung
                'role' => 'siswa',
                'status' => 'active',
                'assigned_at' => now(),
                'notes' => 'Member Futsal Bandung',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // Test User Multi-Role Extreme
            [
                'user_id' => 16, // Multi Role User 1 (selain jadi koordinator Basket)
                'eschool_id' => 1, // Karate Jakarta
                'role' => 'siswa',
                'status' => 'active',
                'assigned_at' => now(),
                'notes' => 'Member Karate - koordinator hanya di Basket',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => 17, // Multi Role User 2 (selain jadi koordinator Futsal)
                'eschool_id' => 2, // Paskibra Jakarta
                'role' => 'siswa',
                'status' => 'active',
                'assigned_at' => now(),
                'notes' => 'Member Paskibra - koordinator hanya di Futsal',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        UserEschoolRole::insert($userEschoolRoles);

        $this->command->info('✅ User Eschool Roles seeded successfully! Created ' . count($userEschoolRoles) . ' role assignments.');
        
        $this->command->line('');
        $this->command->line('🎯 Multi-Role Examples Created:');
        $this->command->line('👩‍🏫 User 4 (Koordinator): Karate Jakarta ONLY');
        $this->command->line('👩‍🏫 User 5 (Koordinator): Paskibra Jakarta ONLY');
        $this->command->line('👩‍🏫 User 6 (Koordinator): Taekwondo Jakarta ONLY');
        $this->command->line('💰 User 7 (Bendahara): Karate + Member Paskibra');
        $this->command->line('💰 User 8 (Bendahara): Paskibra + Member Taekwondo');
        $this->command->line('💰 User 13 (Bendahara): Taekwondo + Member Karate');
        $this->command->line('🎓 User 10 (Member): Karate + Paskibra Jakarta');
        $this->command->line('🎓 User 16 (Mixed): Koordinator Basket + Member Karate');
        $this->command->line('🎓 User 17 (Mixed): Koordinator Futsal + Member Paskibra');
    }
}
