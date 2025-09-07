<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EschoolsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $eschools = [];
        
        // Eschool untuk Sekolah A
        $eschools[] = [
            'school_id' => 1, // Sekolah A
            'name' => 'Basket',
            'schedule_days' => 'Senin, Kamis',
            'description' => 'Ekstrakurikuler olahraga basket',
            'is_active' => true,
            'monthly_fee_amount' => 50000.00,
            'created_at' => now(),
            'updated_at' => now(),
        ];
        
        $eschools[] = [
            'school_id' => 1, // Sekolah A
            'name' => 'Voli',
            'schedule_days' => 'Selasa, Jumat',
            'description' => 'Ekstrakurikuler olahraga voli',
            'is_active' => true,
            'monthly_fee_amount' => 45000.00,
            'created_at' => now(),
            'updated_at' => now(),
        ];
        
        $eschools[] = [
            'school_id' => 1, // Sekolah A
            'name' => 'Lukis',
            'schedule_days' => 'Rabu, Sabtu',
            'description' => 'Ekstrakurikuler seni melukis',
            'is_active' => true,
            'monthly_fee_amount' => 30000.00,
            'created_at' => now(),
            'updated_at' => now(),
        ];
        
        // Eschool untuk Sekolah B
        $eschools[] = [
            'school_id' => 2, // Sekolah B
            'name' => 'Matematika',
            'schedule_days' => 'Senin, Rabu, Jumat',
            'description' => 'Ekstrakurikuler pengayaan matematika',
            'is_active' => true,
            'monthly_fee_amount' => 35000.00,
            'created_at' => now(),
            'updated_at' => now(),
        ];
        
        $eschools[] = [
            'school_id' => 2, // Sekolah B
            'name' => 'Musik',
            'schedule_days' => 'Selasa, Kamis',
            'description' => 'Ekstrakurikuler seni musik',
            'is_active' => true,
            'monthly_fee_amount' => 40000.00,
            'created_at' => now(),
            'updated_at' => now(),
        ];
        
        // Eschool untuk Sekolah C
        $eschools[] = [
            'school_id' => 3, // Sekolah C
            'name' => 'Robotika',
            'schedule_days' => 'Senin, Kamis',
            'description' => 'Ekstrakurikuler teknologi robotika',
            'is_active' => true,
            'monthly_fee_amount' => 55000.00,
            'created_at' => now(),
            'updated_at' => now(),
        ];
        
        $eschools[] = [
            'school_id' => 3, // Sekolah C
            'name' => 'Teater',
            'schedule_days' => 'Rabu, Sabtu',
            'description' => 'Ekstrakurikuler seni teater',
            'is_active' => true,
            'monthly_fee_amount' => 35000.00,
            'created_at' => now(),
            'updated_at' => now(),
        ];
        
        DB::table('eschools')->insert($eschools);
    }
}