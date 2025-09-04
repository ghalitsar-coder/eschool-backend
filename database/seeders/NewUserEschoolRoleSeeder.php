<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\UserEschoolRole;
use Carbon\Carbon;

class NewUserEschoolRoleSeeder extends Seeder
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
                'school_id' => 1, // Jakarta
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
                'school_id' => 1, // Jakarta
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
                'school_id' => 1, // Jakarta
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
                'school_id' => 2, // Bandung
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
                'school_id' => 2, // Bandung
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
                'school_id' => 1, // Jakarta
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
                'school_id' => 1, // Jakarta
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
                'school_id' => 1, // Jakarta
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
                'school_id' => 2, // Bandung
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
                'school_id' => 2, // Bandung
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
                'school_id' => 1, // Jakarta
                'role' => 'member',
                'status' => 'active',
                'assigned_at' => now(),
                'notes' => 'Member Paskibra - bendahara hanya di Karate',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => 8, // Bendahara Paskibra (juga member Taekwondo)
                'eschool_id' => 3, // Taekwondo Jakarta
                'school_id' => 1, // Jakarta
                'role' => 'member',
                'status' => 'active',
                'assigned_at' => now(),
                'notes' => 'Member Taekwondo - bendahara hanya di Paskibra',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => 13, // Bendahara Taekwondo (juga member Karate)
                'eschool_id' => 1, // Karate Jakarta
                'school_id' => 1, // Jakarta
                'role' => 'member',
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
                'school_id' => 1, // Jakarta
                'role' => 'member',
                'status' => 'active',
                'assigned_at' => now(),
                'notes' => 'Member Karate Jakarta',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => 10, // Ahmad Rizki Pratama (multi-role)
                'eschool_id' => 2, // Paskibra Jakarta
                'school_id' => 1, // Jakarta
                'role' => 'member',
                'status' => 'active',
                'assigned_at' => now(),
                'notes' => 'Member Paskibra Jakarta',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => 11, // Siti Nurhaliza Putri
                'eschool_id' => 1, // Karate Jakarta
                'school_id' => 1, // Jakarta
                'role' => 'member',
                'status' => 'active',
                'assigned_at' => now(),
                'notes' => 'Member Karate Jakarta',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => 11, // Siti Nurhaliza Putri (multi-role)
                'eschool_id' => 3, // Taekwondo Jakarta
                'school_id' => 1, // Jakarta
                'role' => 'member',
                'status' => 'active',
                'assigned_at' => now(),
                'notes' => 'Member Taekwondo Jakarta',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => 12, // Budi Santoso Wijaya
                'eschool_id' => 2, // Paskibra Jakarta
                'school_id' => 1, // Jakarta
                'role' => 'member',
                'status' => 'active',
                'assigned_at' => now(),
                'notes' => 'Member Paskibra Jakarta',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => 14, // Eko Prasetyo Hadi (Bandung)
                'eschool_id' => 4, // Basket Bandung
                'school_id' => 2, // Bandung
                'role' => 'member',
                'status' => 'active',
                'assigned_at' => now(),
                'notes' => 'Member Basket Bandung',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => 14, // Eko Prasetyo Hadi (multi-role)
                'eschool_id' => 5, // Futsal Bandung
                'school_id' => 2, // Bandung
                'role' => 'member',
                'status' => 'active',
                'assigned_at' => now(),
                'notes' => 'Member Futsal Bandung',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // CATATAN: User 16 dan 17 adalah koordinator, sesuai QWEN.md:
            // "1 orang = 1 eschool saja, Tidak bisa rangkap jadi koordinator eschool lain"
            // Jadi mereka HANYA jadi koordinator di 1 eschool, tidak bisa member di tempat lain

            // Member tambahan untuk testing multi-role (member bebas ikut multiple eschool)
            [
                'user_id' => 18, // Test Multi Member 1
                'eschool_id' => 1, // Karate Jakarta
                'school_id' => 1, // Jakarta
                'role' => 'member',
                'status' => 'active',
                'assigned_at' => now(),
                'notes' => 'Member Karate Jakarta',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => 18, // Test Multi Member 1 (multi-role valid)
                'eschool_id' => 3, // Taekwondo Jakarta
                'school_id' => 1, // Jakarta
                'role' => 'member',
                'status' => 'active',
                'assigned_at' => now(),
                'notes' => 'Member Taekwondo Jakarta (multi-role)',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => 19, // Test Multi Member 2
                'eschool_id' => 2, // Paskibra Jakarta
                'school_id' => 1, // Jakarta
                'role' => 'member',
                'status' => 'active',
                'assigned_at' => now(),
                'notes' => 'Member Paskibra Jakarta',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => 19, // Test Multi Member 2 (multi-role valid)
                'eschool_id' => 1, // Karate Jakarta
                'school_id' => 1, // Jakarta
                'role' => 'member',
                'status' => 'active',
                'assigned_at' => now(),
                'notes' => 'Member Karate Jakarta (multi-role)',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        UserEschoolRole::insert($userEschoolRoles);

        $this->command->info('✅ User Eschool Roles seeded successfully! Created ' . count($userEschoolRoles) . ' role assignments.');
        
        $this->command->line('');
        $this->command->line('🎯 Multi-Role Examples Created (Sesuai QWEN.md):');
        $this->command->line('👩‍🏫 User 4 (Koordinator): Karate Jakarta ONLY');
        $this->command->line('👩‍🏫 User 5 (Koordinator): Paskibra Jakarta ONLY');
        $this->command->line('👩‍🏫 User 6 (Koordinator): Taekwondo Jakarta ONLY');
        $this->command->line('👩‍🏫 User 15 (Koordinator): Basket Bandung ONLY');
        $this->command->line('�‍🏫 User 16 (Koordinator): Basket Bandung ONLY');
        $this->command->line('👩‍🏫 User 17 (Koordinator): Futsal Bandung ONLY');
        $this->command->line('�💰 User 7 (Bendahara): Karate + Member Paskibra');
        $this->command->line('💰 User 8 (Bendahara): Paskibra + Member Taekwondo');
        $this->command->line('💰 User 13 (Bendahara): Taekwondo + Member Karate');
        $this->command->line('🎓 User 10 (Member): Karate + Paskibra Jakarta');
        $this->command->line('🎓 User 18 (Member): Karate + Taekwondo Jakarta');
        $this->command->line('🎓 User 19 (Member): Paskibra + Karate Jakarta');
    }
}
