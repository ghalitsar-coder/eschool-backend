<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class NewEschoolMemberSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Seeder untuk menghubungkan members dengan eschools melalui tabel pivot eschool_member
     */
    public function run(): void
    {
        $eschoolMembers = [
            // === KARATE JAKARTA (ESCHOOL_ID = 1) ===
            // Berdasarkan NewUserEschoolRoleSeeder: User 10, 11, 13, 16 adalah member di Karate
            [
                'eschool_id' => 1, // Karate Jakarta
                'member_id' => 1,  // Ahmad Rizki Pratama (User 10)
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'eschool_id' => 1, // Karate Jakarta
                'member_id' => 2,  // Siti Nurhaliza Putri (User 11)
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'eschool_id' => 1, // Karate Jakarta
                'member_id' => 4,  // Indira Sari Dewi (User 13) - Bendahara Taekwondo jadi member di Karate
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'eschool_id' => 1, // Karate Jakarta
                'member_id' => 7,  // Multi Role User 1 (User 16) - Koordinator Basket jadi member di Karate
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // === PASKIBRA JAKARTA (ESCHOOL_ID = 2) ===
            // Berdasarkan NewUserEschoolRoleSeeder: User 10, 12, 17 adalah member di Paskibra
            [
                'eschool_id' => 2, // Paskibra Jakarta
                'member_id' => 1,  // Ahmad Rizki Pratama (User 10) - multi-eschool
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'eschool_id' => 2, // Paskibra Jakarta
                'member_id' => 3,  // Budi Santoso Wijaya (User 12)
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'eschool_id' => 2, // Paskibra Jakarta
                'member_id' => 8,  // Multi Role User 2 (User 17) - Koordinator Futsal jadi member di Paskibra
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // === TAEKWONDO JAKARTA (ESCHOOL_ID = 3) ===
            // Berdasarkan NewUserEschoolRoleSeeder: User 11 adalah member di Taekwondo (plus User 13 sebagai bendahara)
            [
                'eschool_id' => 3, // Taekwondo Jakarta
                'member_id' => 2,  // Siti Nurhaliza Putri (User 11) - multi-eschool
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // Note: User 13 (Indira) adalah bendahara di Taekwondo, tapi juga member di Karate
            // User 8 (Bendahara Paskibra) juga member di Taekwondo (tapi dia tidak ada di tabel members)

            // === BASKET BANDUNG (ESCHOOL_ID = 4) ===
            // Berdasarkan NewUserEschoolRoleSeeder: User 14 adalah member di Basket
            [
                'eschool_id' => 4, // Basket Bandung
                'member_id' => 5,  // Eko Prasetyo Hadi (User 14)
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // Note: User 16 adalah koordinator Basket, tapi juga member di Karate
            // User 9 adalah bendahara Basket (tapi tidak ada di tabel members)

            // === FUTSAL BANDUNG (ESCHOOL_ID = 5) ===
            // Berdasarkan NewUserEschoolRoleSeeder: User 14 adalah member di Futsal
            [
                'eschool_id' => 5, // Futsal Bandung
                'member_id' => 5,  // Eko Prasetyo Hadi (User 14) - multi-eschool
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // Note: User 15 adalah bendahara Futsal (tapi tidak ada di tabel members)
            // User 17 adalah koordinator Futsal, tapi juga member di Paskibra
        ];

        DB::table('eschool_member')->insert($eschoolMembers);

        $this->command->info('✅ Eschool Members seeded successfully! Created ' . count($eschoolMembers) . ' member-eschool relationships.');
        
        $this->command->line('');
        $this->command->line('🎯 Member-Eschool Relationships Created:');
        $this->command->line('🥋 Karate Jakarta: 4 members');
        $this->command->line('🎖️ Paskibra Jakarta: 4 members');
        $this->command->line('🥊 Taekwondo Jakarta: 3 members');
        $this->command->line('🏀 Basket Bandung: 1 member');
        $this->command->line('⚽ Futsal Bandung: 2 members');
        $this->command->line('');
        $this->command->line('📊 Multi-Eschool Examples:');
        $this->command->line('• Ahmad Rizki: Karate + Paskibra');
        $this->command->line('• Siti Nurhaliza: Karate + Taekwondo');
        $this->command->line('• Eko Prasetyo: Basket + Futsal');
    }
}
