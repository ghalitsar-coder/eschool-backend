<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Eschool;
use App\Models\School;
use App\Models\User;

class EschoolSeeder extends Seeder
{
    public function run(): void
    {
        // $schools = School::all();
        // $coordinators = User::where('role', 'koordinator')->get();
        // $treasurers = User::where('role', 'bendahara')->get();

        // if ($schools->isEmpty() || $coordinators->isEmpty() || $treasurers->isEmpty()) {
        //     $this->command->warn('Make sure Schools, Coordinators, and Treasurers exist before running this seeder.');
        //     return;
        // }

        // // Array of realistic extracurricular activities
        // $extracurricularActivities = [
        //     'Pramuka' => 'Kegiatan kepramukaan untuk pembentukan karakter dan keterampilan hidup',
        //     'Olahraga Basket' => 'Ekstrakurikuler olahraga basket untuk meningkatkan kemampuan atletik',
        //     'Paduan Suara' => 'Kelompok vokal yang mengembangkan kemampuan bernyanyi dan harmonisasi',
        //     'Teater' => 'Kegiatan seni peran untuk mengembangkan bakat akting dan seni pertunjukan',
        //     'Robotika' => 'Klub teknologi yang fokus pada pembuatan dan pemrograman robot',
        //     'English Club' => 'Komunitas belajar bahasa Inggris dengan berbagai aktivitas menarik',
        //     'Seni Lukis' => 'Komunitas seni rupa yang mengembangkan kemampuan menggambar dan melukis',
        //     'Debat' => 'Klub debat untuk meningkatkan kemampuan berbicara dan argumentasi',
        //     'Jurnalistik' => 'Klub jurnalistik untuk mengembangkan kemampuan menulis dan meliput berita',
        //     'Komputer' => 'Ekstrakurikuler teknologi informasi dan pemrograman',
        //     'Tari Tradisional' => 'Klub seni tari yang melestarikan budaya Indonesia',
        //     'Musik' => 'Klub musik yang mengembangkan kemampuan bermain alat musik',
        //     'PMR' => 'Palang Merah Remaja untuk pembentukan karakter dan keterampilan medis dasar',
        //     'Olimpiade Matematika' => 'Kompetisi dan pelatihan matematika untuk olimpiade',
        //     'KIR' => 'Karya Ilmiah Remaja untuk pengembangan riset dan inovasi siswa',
        //     'Futsal' => 'Ekstrakurikuler olahraga futsal untuk meningkatkan kebugaran dan kerja tim',
        //     'Bola Voli' => 'Kegiatan olahraga bola voli untuk meningkatkan koordinasi dan kekuatan fisik',
        //     'Taekwondo' => 'Seni bela diri taekwondo untuk pembentukan disiplin dan pertahanan diri',
        //     'Seni Kaligrafi' => 'Klub seni kaligrafi untuk melestarikan seni tulisan indah',
        //     'Pecinta Alam' => 'Komunitas pecinta alam untuk menjaga lingkungan dan petualangan alam',
        //     'Bahasa Jepang' => 'Klub bahasa Jepang untuk mempelajari budaya dan bahasa Jepang',
        //     'Seni Fotografi' => 'Klub fotografi untuk mengembangkan kemampuan mengambil gambar artistik',
        //     'Kewirausahaan' => 'Komunitas kewirausahaan untuk mengembangkan jiwa entrepreneur',
        //     'Olimpiade Fisika' => 'Kompetisi dan pelatihan fisika untuk olimpiade sains',
        //     'Seni Ukir' => 'Klub seni ukir tradisional untuk melestarikan keterampilan kerajinan tangan'
        // ];

        // $eschoolsData = [];
        // $eschoolsCreated = 0;
        
        // // Create multiple eschools per school
        // foreach ($schools as $index => $school) {
        //     // Reset activities for each school to allow reuse
        //     $availableActivities = $extracurricularActivities;
        //     $activityKeys = array_keys($availableActivities);
            
        //     // Each school will have 4-6 eschools
        //     $eschoolCount = min(rand(4, 6), count($activityKeys));
            
        //     for ($i = 0; $i < $eschoolCount; $i++) {
        //         // Get a random activity
        //         if (empty($activityKeys)) {
        //             break;
        //         }
                
        //         $randomIndex = array_rand($activityKeys);
        //         $activityKey = $activityKeys[$randomIndex];
        //         $description = $availableActivities[$activityKey];
                
        //         // Remove used activity to avoid duplicates in same school
        //         unset($activityKeys[$randomIndex]);
        //         $activityKeys = array_values($activityKeys);

        //         $eschoolsData[] = [
        //             'school_id' => $school->id,
        //             'coordinator_id' => $coordinators->get($index % $coordinators->count())->id,
        //             'treasurer_id' => $treasurers->get($index % $treasurers->count())->id,
        //             'name' => $activityKey . ' - ' . $school->name,
        //             'description' => $description,
        //             'monthly_kas_amount' => rand(15000, 30000),
        //             'schedule_days' => json_encode(['monday', 'wednesday', 'friday']),
        //             'total_schedule_days' => 3,
        //             'is_active' => true,
        //             'created_at' => now(),
        //             'updated_at' => now(),
        //         ];
                
        //         $eschoolsCreated++;
        //     }
        // }

         $eschools = [
            // Eschools untuk Sekolah 1 (SMA Negeri 1 Jakarta)
            [
                'school_id' => 1,
                'coordinator_id' => 3, // Pak Joko Susilo
                'treasurer_id' => 6,   // Andi Pratama
                'name' => 'Basket',
                'description' => 'Ekstrakurikuler Bola Basket untuk mengembangkan kemampuan olahraga dan kerjasama tim',
                'monthly_kas_amount' => 25000,
                'schedule_days' => json_encode(['Selasa', 'Kamis', 'Sabtu']),
                'total_schedule_days' => 3,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'school_id' => 1,
                'coordinator_id' => 4, // Bu Rina Sari
                'treasurer_id' => 7,   // Eka Putri
                'name' => 'Voli',
                'description' => 'Ekstrakurikuler Bola Voli untuk meningkatkan koordinasi dan sportivitas',
                'monthly_kas_amount' => 20000,
                'schedule_days' => json_encode(['Senin', 'Rabu', 'Jumat']),
                'total_schedule_days' => 3,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'school_id' => 1,
                'coordinator_id' => 5, // Pak Heru Prasetyo
                'treasurer_id' => 8,   // Hani Sari
                'name' => 'Lukis',
                'description' => 'Ekstrakurikuler Seni Lukis untuk mengembangkan kreativitas dan bakat seni',
                'monthly_kas_amount' => 30000,
                'schedule_days' => json_encode(['Kamis', 'Sabtu']),
                'total_schedule_days' => 2,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // Eschools untuk Sekolah 2 (SMA Negeri 2 Jakarta)
            [
                'school_id' => 2,
                'coordinator_id' => 15, // Pak Ahmad Fauzi
                'treasurer_id' => 17,   // Rina Permata
                'name' => 'Matematika',
                'description' => 'Ekstrakurikuler Olimpiade Matematika untuk mengasah kemampuan logika dan pemecahan masalah',
                'monthly_kas_amount' => 15000,
                'schedule_days' => json_encode(['Selasa', 'Kamis']),
                'total_schedule_days' => 2,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'school_id' => 2,
                'coordinator_id' => 16, // Bu Lina Marlina
                'treasurer_id' => 18,   // Doni Setiawan
                'name' => 'Teater',
                'description' => 'Ekstrakurikuler Teater untuk mengembangkan kepercayaan diri dan kemampuan berakting',
                'monthly_kas_amount' => 35000,
                'schedule_days' => json_encode(['Rabu', 'Jumat', 'Sabtu']),
                'total_schedule_days' => 3,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($eschools as $eschool) {
            Eschool::create($eschool);
        }
        
        // $this->command->info("Created {$this->formatNumber($eschoolsCreated)} eschools.");
    }

    private function formatNumber($number) {
        return number_format($number, 0, ',', '.');
    }
}