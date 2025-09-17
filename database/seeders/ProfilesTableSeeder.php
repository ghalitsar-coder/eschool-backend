<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProfilesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Profil untuk staff, koordinator, dan bendahara (guru)
        DB::table('profiles')->insert([
            // Staff Sekolah A
            [
                'name' => 'Bu Sari',
                'date_of_birth' => '1980-05-15',
                'gender' => 'F',
                'address' => 'Jl. Anggrek No. 1, Jakarta',
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Pak Joko',
                'date_of_birth' => '1975-08-22',
                'gender' => 'M',
                'address' => 'Jl. Melati No. 5, Jakarta',
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Pak Dudung',
                'date_of_birth' => '1975-09-22',
                'gender' => 'M',
                'address' => 'Jl. Mawar No. 5, Jakarta',
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Pak Jajang',
                'date_of_birth' => '1975-10-22',
                'gender' => 'M',
                'address' => 'Jl. Anggrek No. 5, Jakarta',
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            
            // Staff Sekolah B
            [
                'name' => 'Bu Rina',
                'date_of_birth' => '1982-11-30',
                'gender' => 'F',
                'address' => 'Jl. Mawar No. 10, Bandung',
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            
            // Staff Sekolah C
            [
                'name' => 'Pak Heru',
                'date_of_birth' => '1978-03-12',
                'gender' => 'M',
                'address' => 'Jl. Kenanga No. 15, Surabaya',
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            
            // Bendahara (siswa)
            [
                'name' => 'Andi',
                'date_of_birth' => '2005-07-10',
                'gender' => 'M',
                'address' => 'Jl. Cendana No. 20, Jakarta',
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Eka',
                'date_of_birth' => '2006-01-25',
                'gender' => 'F',
                'address' => 'Jl. Pinus No. 25, Bandung',
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Hani',
                'date_of_birth' => '2005-12-05',
                'gender' => 'F',
                'address' => 'Jl. Beringin No. 30, Surabaya',
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            
            // Anggota (siswa)
            [
                'name' => 'Budi',
                'date_of_birth' => '2006-04-18',
                'gender' => 'M',
                'address' => 'Jl. Dahlia No. 35, Jakarta',
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Cici',
                'date_of_birth' => '2005-09-12',
                'gender' => 'F',
                'address' => 'Jl. Kamboja No. 40, Jakarta',
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Dedi',
                'date_of_birth' => '2006-02-28',
                'gender' => 'M',
                'address' => 'Jl. Teratai No. 45, Jakarta',
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Fani',
                'date_of_birth' => '2005-11-03',
                'gender' => 'F',
                'address' => 'Jl. Sakura No. 50, Bandung',
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Gita',
                'date_of_birth' => '2006-06-17',
                'gender' => 'F',
                'address' => 'Jl. Lily No. 55, Bandung',
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Iko',
                'date_of_birth' => '2005-10-08',
                'gender' => 'M',
                'address' => 'Jl. Tulip No. 60, Surabaya',
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Joni',
                'date_of_birth' => '2006-03-22',
                'gender' => 'M',
                'address' => 'Jl. Orchid No. 65, Surabaya',
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}