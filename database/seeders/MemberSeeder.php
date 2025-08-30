<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Member;
use App\Models\Eschool;
use App\Models\User;
use App\Models\School;

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

        // Array of realistic Indonesian names
        $indonesianNames = [
            'Aditya Pratama', 'Budi Santoso', 'Citra Dewi', 'Dian Permata', 'Eko Prasetyo',
            'Fitri Handayani', 'Galih Ramadhan', 'Hana Putri', 'Indra Kusuma', 'Jenny Wijaya',
            'Kevin Sanjaya', 'Lina Marlina', 'Mega Sari', 'Nanda Kurnia', 'Oka Pradana',
            'Putri Ayu', 'Rendi Saputra', 'Sari Indah', 'Taufik Hidayat', 'Umi Kalsum',
            'Vina Anggraini', 'Wawan Setiawan', 'Yani Susanti', 'Zainal Abidin', 'Ayu Lestari',
            'Bambang Widodo', 'Cinta Nurul', 'Dodi Firmansyah', 'Elisa Damayanti', 'Fajar Nugroho'
        ];

        // Array of realistic addresses
        $addresses = [
            'Jl. Merdeka No. 123, Jakarta Pusat',
            'Jl. Sudirman No. 45, Bandung',
            'Jl. Diponegoro No. 67, Surabaya',
            'Jl. Thamrin No. 89, Medan',
            'Jl. Gatot Subroto No. 101, Yogyakarta',
            'Jl. Ahmad Yani No. 23, Semarang',
            'Jl. Pahlawan No. 45, Malang',
            'Jl. Kartini No. 67, Solo',
            'Jl. Basuki Rahmat No. 89, Denpasar',
            'Jl. Imam Bonjol No. 101, Makassar'
        ];

        $studentIndex = 0;

        foreach ($eschools as $eschool) {
            // Setiap eschool memiliki 5-8 member
            $memberCount = rand(5, 8);
            
            for ($i = 0; $i < $memberCount; $i++) {
                if ($studentIndex >= $students->count()) {
                    break;
                }

                $student = $students[$studentIndex];
                
                // Generate realistic member data
                $gender = (rand(0, 1) == 0) ? 'L' : 'P';
                $birthYear = rand(2005, 2008); // Typical high school student age
                $birthMonth = rand(1, 12);
                $birthDay = rand(1, 28); // To avoid issues with February
                
                $memberData = [
                    'school_id' => $student->school_id, // Now using school_id from user
                    'user_id' => $student->id,
                    'nip' => 'NIP' . str_pad($studentIndex + 1, 5, '0', STR_PAD_LEFT),
                    'name' => $student->name, // Will be synced from user, but we set it explicitly for clarity
                    'student_id' => 'STD' . str_pad($studentIndex + 1, 5, '0', STR_PAD_LEFT),
                    'date_of_birth' => "{$birthYear}-{$birthMonth}-{$birthDay}",
                    'gender' => $gender,
                    'address' => $addresses[array_rand($addresses)],
                    'phone' => '08' . rand(1000000000, 9999999999),
                    'status' => 'active',
                    'is_active' => rand(0, 10) > 1, // 90% aktif
                    'created_at' => now(),
                    'updated_at' => now(),
                ];

                $member = Member::create($memberData);
                
                // Attach member to eschool using many-to-many relationship
                $member->eschools()->attach($eschool->id);
                
                $studentIndex++;
            }
        }
    }
}