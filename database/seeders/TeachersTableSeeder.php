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
        // Clear existing data
        DB::table('teachers')->delete();
        
        // Get all profiles
        $profiles = DB::table('profiles')
            ->select('id', 'name')
            ->get();
        
        // Create a mapping of profile names to profile IDs
        $profileMap = [];
        foreach ($profiles as $profile) {
            $profileMap[$profile->name] = $profile->id;
        }
        
        // Define teachers based on the QWEN.md requirements
        $teachers = [];
        
        // Sekolah A: Bu Sari (staff), Pak Joko (koordinator)
        if (isset($profileMap['Bu Sari'])) {
            $teachers[] = [
                'profile_id' => $profileMap['Bu Sari'],
                'license_number' => 'LIC001',
                'school_id' => 1, // Sekolah A
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        
        if (isset($profileMap['Pak Joko'])) {
            $teachers[] = [
                'profile_id' => $profileMap['Pak Joko'],
                'license_number' => 'LIC002',
                'school_id' => 1, // Sekolah A
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        
        if (isset($profileMap['Pak Dudung'])) {
            $teachers[] = [
                'profile_id' => $profileMap['Pak Dudung'],
                'license_number' => 'LIC005',
                'school_id' => 1, // Sekolah A
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        
        if (isset($profileMap['Pak Jajang'])) {
            $teachers[] = [
                'profile_id' => $profileMap['Pak Jajang'],
                'license_number' => 'LIC006',
                'school_id' => 1, // Sekolah A
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        
        // Sekolah B: Bu Rina (staff)
        if (isset($profileMap['Bu Rina'])) {
            $teachers[] = [
                'profile_id' => $profileMap['Bu Rina'],
                'license_number' => 'LIC003',
                'school_id' => 2, // Sekolah B
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        
        // Sekolah C: Pak Heru (staff)
        if (isset($profileMap['Pak Heru'])) {
            $teachers[] = [
                'profile_id' => $profileMap['Pak Heru'],
                'license_number' => 'LIC004',
                'school_id' => 3, // Sekolah C
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        
        // Insert all teachers
        if (!empty($teachers)) {
            DB::table('teachers')->insert($teachers);
        }
    }
}