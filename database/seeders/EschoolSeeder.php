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
        $schools = School::all();
        $coordinators = User::where('role', 'koordinator')->get();
        $treasurers = User::where('role', 'bendahara')->get();

        if ($schools->isEmpty() || $coordinators->isEmpty() || $treasurers->isEmpty()) {
            $this->command->warn('Make sure Schools, Coordinators, and Treasurers exist before running this seeder.');
            return;
        }

        // Array of realistic extracurricular activities
        $extracurricularActivities = [
            'Pramuka' => 'Kegiatan kepramukaan untuk pembentukan karakter dan keterampilan hidup',
            'Olahraga Basket' => 'Ekstrakurikuler olahraga basket untuk meningkatkan kemampuan atletik',
            'Paduan Suara' => 'Kelompok vokal yang mengembangkan kemampuan bernyanyi dan harmonisasi',
            'Teater' => 'Kegiatan seni peran untuk mengembangkan bakat akting dan seni pertunjukan',
            'Robotika' => 'Klub teknologi yang fokus pada pembuatan dan pemrograman robot',
            'English Club' => 'Komunitas belajar bahasa Inggris dengan berbagai aktivitas menarik',
            'Seni Lukis' => 'Komunitas seni rupa yang mengembangkan kemampuan menggambar dan melukis',
            'Debat' => 'Klub debat untuk meningkatkan kemampuan berbicara dan argumentasi',
            'Jurnalistik' => 'Klub jurnalistik untuk mengembangkan kemampuan menulis dan meliput berita',
            'Komputer' => 'Ekstrakurikuler teknologi informasi dan pemrograman',
            'Tari Tradisional' => 'Klub seni tari yang melestarikan budaya Indonesia',
            'Musik' => 'Klub musik yang mengembangkan kemampuan bermain alat musik',
            'PMR' => 'Palang Merah Remaja untuk pembentukan karakter dan keterampilan medis dasar',
            'Olimpiade Matematika' => 'Kompetisi dan pelatihan matematika untuk olimpiade',
            'KIR' => 'Karya Ilmiah Remaja untuk pengembangan riset dan inovasi siswa'
        ];

        $eschoolsData = [];
        
        // Create multiple eschools per school
        foreach ($schools as $index => $school) {
            // Reset activities for each school to allow reuse
            $availableActivities = $extracurricularActivities;
            $activityKeys = array_keys($availableActivities);
            
            // Each school will have 3-5 eschools
            $eschoolCount = min(rand(3, 5), count($activityKeys));
            
            for ($i = 0; $i < $eschoolCount; $i++) {
                // Get a random activity
                $randomIndex = array_rand($activityKeys);
                $activityKey = $activityKeys[$randomIndex];
                $description = $availableActivities[$activityKey];
                
                // Remove used activity to avoid duplicates in same school
                unset($activityKeys[$randomIndex]);
                $activityKeys = array_values($activityKeys);
                
                // If we run out of activities, break
                if (empty($activityKeys)) {
                    break;
                }

                $eschoolsData[] = [
                    'school_id' => $school->id,
                    'coordinator_id' => $coordinators->get($index % $coordinators->count())->id,
                    'treasurer_id' => $treasurers->get($index % $treasurers->count())->id,
                    'name' => $activityKey . ' - ' . $school->name,
                    'description' => $description,
                    'monthly_kas_amount' => rand(15000, 30000),
                    'schedule_days' => json_encode(['monday', 'wednesday', 'friday']),
                    'total_schedule_days' => 3,
                    'is_active' => true,
                ];
            }
        }

        foreach ($eschoolsData as $eschool) {
            Eschool::create($eschool);
        }
    }
}