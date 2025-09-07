<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TeachersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Berdasarkan data profil, staff dan koordinator adalah guru
        // Profile IDs 1-4 adalah guru (staff dan koordinator)
        $teachers = [];
        
        // Sekolah A: Bu Sari (staff), Pak Joko (koordinator)
        $teachers[] = [
            'profile_id' => 1, // Bu Sari
            'license_number' => 'LIC001',
            'school_id' => 1, // Sekolah A
            'created_at' => now(),
            'updated_at' => now(),
        ];
        
        $teachers[] = [
            'profile_id' => 2, // Pak Joko
            'license_number' => 'LIC002',
            'school_id' => 1, // Sekolah A
            'created_at' => now(),
            'updated_at' => now(),
        ];
        
        // Sekolah B: Bu Rina (staff)
        $teachers[] = [
            'profile_id' => 3, // Bu Rina
            'license_number' => 'LIC003',
            'school_id' => 2, // Sekolah B
            'created_at' => now(),
            'updated_at' => now(),
        ];
        
        // Sekolah C: Pak Heru (staff)
        $teachers[] = [
            'profile_id' => 4, // Pak Heru
            'license_number' => 'LIC004',
            'school_id' => 3, // Sekolah C
            'created_at' => now(),
            'updated_at' => now(),
        ];
        
        DB::table('teachers')->insert($teachers);
    }
}