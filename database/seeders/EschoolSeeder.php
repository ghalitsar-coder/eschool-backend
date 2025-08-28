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

        // Pastikan setiap bendahara memiliki 1 eschool untuk MVP testing
        $eschoolsData = [
            [
                'school_id' => $schools->first()->id,
                'coordinator_id' => $coordinators->first()->id,
                'treasurer_id' => $treasurers->get(0)->id, // bendahara1@example.com
                'name' => 'Kelas XII IPA 1',
                'description' => 'Ekstrakurikuler untuk siswa kelas XII IPA 1',
                'monthly_kas_amount' => 25000,
                'schedule_days' => json_encode(['monday', 'wednesday', 'friday']),
                'total_schedule_days' => 3,
                'is_active' => true,
            ],
            [
                'school_id' => $schools->count() > 1 ? $schools->get(1)->id : $schools->first()->id,
                'coordinator_id' => $coordinators->count() > 1 ? $coordinators->get(1)->id : $coordinators->first()->id,
                'treasurer_id' => $treasurers->get(1)->id, // bendahara2@example.com
                'name' => 'Kelas XI IPS 2',
                'description' => 'Ekstrakurikuler untuk siswa kelas XI IPS 2',
                'monthly_kas_amount' => 20000,
                'schedule_days' => json_encode(['tuesday', 'thursday']),
                'total_schedule_days' => 2,
                'is_active' => true,
            ],
            [
                'school_id' => $schools->count() > 2 ? $schools->get(2)->id : $schools->first()->id,
                'coordinator_id' => $coordinators->first()->id,
                'treasurer_id' => $treasurers->get(2)->id, // bendahara3@example.com
                'name' => 'Kelas X MIPA 3',
                'description' => 'Ekstrakurikuler untuk siswa kelas X MIPA 3',
                'monthly_kas_amount' => 15000,
                'schedule_days' => json_encode(['monday', 'thursday']),
                'total_schedule_days' => 2,
                'is_active' => true,
            ],
        ];

        foreach ($eschoolsData as $eschool) {
            Eschool::create($eschool);
        }
    }
}