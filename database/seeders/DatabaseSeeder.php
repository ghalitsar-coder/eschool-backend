<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Menjalankan seeder dalam urutan yang tepat untuk menghindari masalah foreign key
        $this->call([
            SchoolsTableSeeder::class,
            ProfilesTableSeeder::class,
            UsersTableSeeder::class,
            TeachersTableSeeder::class,
            StudentsTableSeeder::class,
            EschoolsTableSeeder::class,
            UserEschoolRolesTableSeeder::class,
            AttendanceRecordTableSeeder::class,
            KasRecordTableSeeder::class,
            KasPaymentTableSeeder::class,
        ]);
    }
}