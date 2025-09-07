<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UserEschoolRolesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [];
        
        // Staff (Supervisor) - tidak memerlukan eschool_id
        // Sekolah A: Bu Sari (user_id: 1)
        $roles[] = [
            'user_id' => 1, // Bu Sari
            'eschool_id' => null, // Supervisor tidak terkait dengan eschool spesifik
            'role' => 'supervisor',
            'created_at' => now(),
            'updated_at' => now(),
        ];
        
        // Sekolah B: Bu Rina (user_id: 3)
        $roles[] = [
            'user_id' => 3, // Bu Rina
            'eschool_id' => null, // Supervisor tidak terkait dengan eschool spesifik
            'role' => 'supervisor',
            'created_at' => now(),
            'updated_at' => now(),
        ];
        
        // Sekolah C: Pak Heru (user_id: 4)
        $roles[] = [
            'user_id' => 4, // Pak Heru
            'eschool_id' => null, // Supervisor tidak terkait dengan eschool spesifik
            'role' => 'supervisor',
            'created_at' => now(),
            'updated_at' => now(),
        ];
        
        // Koordinator - hanya untuk satu eschool
        // Sekolah A: Pak Joko (user_id: 2) -> Basket (eschool_id: 1)
        $roles[] = [
            'user_id' => 2, // Pak Joko
            'eschool_id' => 1, // Basket
            'role' => 'coordinator',
            'created_at' => now(),
            'updated_at' => now(),
        ];
        
        // Bendahara - hanya untuk satu eschool
        // Sekolah A: Andi (user_id: 5) -> Basket (eschool_id: 1)
        $roles[] = [
            'user_id' => 5, // Andi
            'eschool_id' => 1, // Basket
            'role' => 'treasurer',
            'created_at' => now(),
            'updated_at' => now(),
        ];
        
        // Sekolah B: Eka (user_id: 6) -> Matematika (eschool_id: 4)
        $roles[] = [
            'user_id' => 6, // Eka
            'eschool_id' => 4, // Matematika
            'role' => 'treasurer',
            'created_at' => now(),
            'updated_at' => now(),
        ];
        
        // Sekolah A: Hani (user_id: 7) -> Lukis (eschool_id: 3)
        $roles[] = [
            'user_id' => 7, // Hani
            'eschool_id' => 3, // Lukis
            'role' => 'treasurer',
            'created_at' => now(),
            'updated_at' => now(),
        ];
        
        // Members - bisa join multiple eschool
        // Sekolah A:
        // Budi (user_id: 8) -> Basket (eschool_id: 1), Voli (eschool_id: 2), Lukis (eschool_id: 3)
        $roles[] = [
            'user_id' => 8, // Budi
            'eschool_id' => 1, // Basket
            'role' => 'member',
            'created_at' => now(),
            'updated_at' => now(),
        ];
        
        $roles[] = [
            'user_id' => 8, // Budi
            'eschool_id' => 2, // Voli
            'role' => 'member',
            'created_at' => now(),
            'updated_at' => now(),
        ];
        
        $roles[] = [
            'user_id' => 8, // Budi
            'eschool_id' => 3, // Lukis
            'role' => 'member',
            'created_at' => now(),
            'updated_at' => now(),
        ];
        
        // Cici (user_id: 9) -> Basket (eschool_id: 1), Voli (eschool_id: 2)
        $roles[] = [
            'user_id' => 9, // Cici
            'eschool_id' => 1, // Basket
            'role' => 'member',
            'created_at' => now(),
            'updated_at' => now(),
        ];
        
        $roles[] = [
            'user_id' => 9, // Cici
            'eschool_id' => 2, // Voli
            'role' => 'member',
            'created_at' => now(),
            'updated_at' => now(),
        ];
        
        // Dedi (user_id: 10) -> Basket (eschool_id: 1)
        $roles[] = [
            'user_id' => 10, // Dedi
            'eschool_id' => 1, // Basket
            'role' => 'member',
            'created_at' => now(),
            'updated_at' => now(),
        ];
        
        // Sekolah B:
        // Andi (user_id: 5) -> Voli (eschool_id: 2) - sebagai member
        $roles[] = [
            'user_id' => 5, // Andi
            'eschool_id' => 2, // Voli
            'role' => 'member',
            'created_at' => now(),
            'updated_at' => now(),
        ];
        
        // Fani (user_id: 11) -> Matematika (eschool_id: 4), Musik (eschool_id: 5)
        $roles[] = [
            'user_id' => 11, // Fani
            'eschool_id' => 4, // Matematika
            'role' => 'member',
            'created_at' => now(),
            'updated_at' => now(),
        ];
        
        $roles[] = [
            'user_id' => 11, // Fani
            'eschool_id' => 5, // Musik
            'role' => 'member',
            'created_at' => now(),
            'updated_at' => now(),
        ];
        
        // Gita (user_id: 12) -> Musik (eschool_id: 5)
        $roles[] = [
            'user_id' => 12, // Gita
            'eschool_id' => 5, // Musik
            'role' => 'member',
            'created_at' => now(),
            'updated_at' => now(),
        ];
        
        // Sekolah C:
        // Budi (user_id: 8) -> Robotika (eschool_id: 6) - sebagai member
        $roles[] = [
            'user_id' => 8, // Budi
            'eschool_id' => 6, // Robotika
            'role' => 'member',
            'created_at' => now(),
            'updated_at' => now(),
        ];
        
        // Iko (user_id: 13) -> Robotika (eschool_id: 6), Teater (eschool_id: 7)
        $roles[] = [
            'user_id' => 13, // Iko
            'eschool_id' => 6, // Robotika
            'role' => 'member',
            'created_at' => now(),
            'updated_at' => now(),
        ];
        
        $roles[] = [
            'user_id' => 13, // Iko
            'eschool_id' => 7, // Teater
            'role' => 'member',
            'created_at' => now(),
            'updated_at' => now(),
        ];
        
        // Joni (user_id: 14) -> Teater (eschool_id: 7)
        $roles[] = [
            'user_id' => 14, // Joni
            'eschool_id' => 7, // Teater
            'role' => 'member',
            'created_at' => now(),
            'updated_at' => now(),
        ];
        
        DB::table('user_eschool_roles')->insert($roles);
    }
}