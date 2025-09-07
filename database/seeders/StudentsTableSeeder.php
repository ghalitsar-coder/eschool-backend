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
        // Berdasarkan data profil, profile IDs 5-14 adalah siswa
        $students = [];
        
        // Siswa Sekolah A (IDs 5, 7, 8, 9, 10)
        $students[] = [
            'profile_id' => 5, // Andi (bendahara)
            'school_id' => 1, // Sekolah A
            'student_id' => 'SISWA001',
            'grade_level' => '11',
            'created_at' => now(),
            'updated_at' => now(),
        ];
        
        $students[] = [
            'profile_id' => 7, // Hani (bendahara)
            'school_id' => 1, // Sekolah A
            'student_id' => 'SISWA003',
            'grade_level' => '11',
            'created_at' => now(),
            'updated_at' => now(),
        ];
        
        $students[] = [
            'profile_id' => 8, // Budi (anggota)
            'school_id' => 1, // Sekolah A
            'student_id' => 'SISWA004',
            'grade_level' => '11',
            'created_at' => now(),
            'updated_at' => now(),
        ];
        
        $students[] = [
            'profile_id' => 9, // Cici (anggota)
            'school_id' => 1, // Sekolah A
            'student_id' => 'SISWA005',
            'grade_level' => '10',
            'created_at' => now(),
            'updated_at' => now(),
        ];
        
        $students[] = [
            'profile_id' => 10, // Dedi (anggota)
            'school_id' => 1, // Sekolah A
            'student_id' => 'SISWA006',
            'grade_level' => '10',
            'created_at' => now(),
            'updated_at' => now(),
        ];
        
        // Siswa Sekolah B (IDs 6, 11, 12)
        $students[] = [
            'profile_id' => 6, // Eka (bendahara)
            'school_id' => 2, // Sekolah B
            'student_id' => 'SISWB001',
            'grade_level' => '11',
            'created_at' => now(),
            'updated_at' => now(),
        ];
        
        $students[] = [
            'profile_id' => 11, // Fani (anggota)
            'school_id' => 2, // Sekolah B
            'student_id' => 'SISWB002',
            'grade_level' => '10',
            'created_at' => now(),
            'updated_at' => now(),
        ];
        
        $students[] = [
            'profile_id' => 12, // Gita (anggota)
            'school_id' => 2, // Sekolah B
            'student_id' => 'SISWB003',
            'grade_level' => '11',
            'created_at' => now(),
            'updated_at' => now(),
        ];
        
        // Siswa Sekolah C (IDs 13, 14)
        $students[] = [
            'profile_id' => 13, // Iko (anggota)
            'school_id' => 3, // Sekolah C
            'student_id' => 'SISWC001',
            'grade_level' => '10',
            'created_at' => now(),
            'updated_at' => now(),
        ];
        
        $students[] = [
            'profile_id' => 14, // Joni (anggota)
            'school_id' => 3, // Sekolah C
            'student_id' => 'SISWC002',
            'grade_level' => '11',
            'created_at' => now(),
            'updated_at' => now(),
        ];
        
        DB::table('students')->insert($students);
    }
}