<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StudentsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clear existing data
        DB::table('students')->delete();
        
        // Get all profiles
        $profiles = DB::table('profiles')
            ->select('id', 'name')
            ->get();
        
        // Create a mapping of profile names to profile IDs
        $profileMap = [];
        foreach ($profiles as $profile) {
            $profileMap[$profile->name] = $profile->id;
        }
        
        // Define students based on the QWEN.md requirements
        $students = [];
        
        // Siswa Sekolah A
        if (isset($profileMap['Andi'])) {
            $students[] = [
                'profile_id' => $profileMap['Andi'], // Andi (bendahara)
                'school_id' => 1, // Sekolah A
                'student_id' => 'SISWA001',
                'grade_level' => '11',
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        
        if (isset($profileMap['Eka'])) {
            $students[] = [
                'profile_id' => $profileMap['Eka'], // Eka (bendahara)
                'school_id' => 2, // Sekolah B
                'student_id' => 'SISWB001',
                'grade_level' => '11',
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        
        if (isset($profileMap['Hani'])) {
            $students[] = [
                'profile_id' => $profileMap['Hani'], // Hani (bendahara)
                'school_id' => 1, // Sekolah A
                'student_id' => 'SISWA003',
                'grade_level' => '11',
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        
        if (isset($profileMap['Budi'])) {
            $students[] = [
                'profile_id' => $profileMap['Budi'], // Budi (anggota)
                'school_id' => 1, // Sekolah A
                'student_id' => 'SISWA004',
                'grade_level' => '11',
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        
        if (isset($profileMap['Cici'])) {
            $students[] = [
                'profile_id' => $profileMap['Cici'], // Cici (anggota)
                'school_id' => 1, // Sekolah A
                'student_id' => 'SISWA005',
                'grade_level' => '10',
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        
        if (isset($profileMap['Dedi'])) {
            $students[] = [
                'profile_id' => $profileMap['Dedi'], // Dedi (anggota)
                'school_id' => 1, // Sekolah A
                'student_id' => 'SISWA006',
                'grade_level' => '10',
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        
        if (isset($profileMap['Fani'])) {
            $students[] = [
                'profile_id' => $profileMap['Fani'], // Fani (anggota)
                'school_id' => 2, // Sekolah B
                'student_id' => 'SISWB002',
                'grade_level' => '10',
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        
        if (isset($profileMap['Gita'])) {
            $students[] = [
                'profile_id' => $profileMap['Gita'], // Gita (anggota)
                'school_id' => 2, // Sekolah B
                'student_id' => 'SISWB003',
                'grade_level' => '11',
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        
        if (isset($profileMap['Iko'])) {
            $students[] = [
                'profile_id' => $profileMap['Iko'], // Iko (anggota)
                'school_id' => 3, // Sekolah C
                'student_id' => 'SISWC001',
                'grade_level' => '10',
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        
        if (isset($profileMap['Joni'])) {
            $students[] = [
                'profile_id' => $profileMap['Joni'], // Joni (anggota)
                'school_id' => 3, // Sekolah C
                'student_id' => 'SISWC002',
                'grade_level' => '11',
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        
        // Insert all students
        if (!empty($students)) {
            DB::table('students')->insert($students);
        }
    }
}