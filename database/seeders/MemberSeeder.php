<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Member;
use App\Models\Eschool;
use App\Models\User;

class MemberSeeder extends Seeder
{
    public function run(): void
    {
        $eschools = Eschool::all();
        $students = User::where('role', 'siswa')->get();

        if ($eschools->isEmpty() || $students->isEmpty()) {
            $this->command->warn('Make sure Eschools and Students exist before running this seeder.');
            return;
        }

        $members = [];
        $studentIndex = 0;

        foreach ($eschools as $eschool) {
            // Setiap eschool memiliki 5-8 member
            $memberCount = rand(5, 8);
            
            for ($i = 0; $i < $memberCount; $i++) {
                if ($studentIndex >= $students->count()) {
                    break;
                }

                $student = $students[$studentIndex];
                $members[] = [
                    'eschool_id' => $eschool->id,
                    'user_id' => $student->id,
                    'student_id' => 'STD' . str_pad($studentIndex + 1, 4, '0', STR_PAD_LEFT),
                    'phone' => '08' . rand(1000000000, 9999999999),
                    'is_active' => rand(0, 10) > 1, // 90% aktif
                    'created_at' => now(),
                    'updated_at' => now(),
                ];

                $studentIndex++;
            }
        }

        foreach ($members as $member) {
            Member::create($member);
        }
    }
}