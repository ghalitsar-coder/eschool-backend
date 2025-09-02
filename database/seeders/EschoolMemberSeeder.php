<?php


namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB; // <- ini yang benar
// use App\Models\EschoolMember;
use App\Models\School;
use App\Models\User;

class EschoolMemberSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        $eschoolMembers = [
            // Eschool Basket (ID: 1) - Sekolah 1
            ['eschool_id' => 1, 'member_id' => 1], // Andi (bendahara + member)
            ['eschool_id' => 1, 'member_id' => 4], // Budi 
            ['eschool_id' => 1, 'member_id' => 5], // Cici
            ['eschool_id' => 1, 'member_id' => 6], // Dedi

            // Eschool Voli (ID: 2) - Sekolah 1  
            ['eschool_id' => 2, 'member_id' => 2], // Eka (bendahara + member)
            ['eschool_id' => 2, 'member_id' => 7], // Fani
            ['eschool_id' => 2, 'member_id' => 8], // Gita
            ['eschool_id' => 2, 'member_id' => 1], // Andi (ikut sebagai member biasa)

            // Eschool Lukis (ID: 3) - Sekolah 1
            ['eschool_id' => 3, 'member_id' => 3], // Hani (bendahara + member)
            ['eschool_id' => 3, 'member_id' => 9], // Iko
            ['eschool_id' => 3, 'member_id' => 4], // Budi (ikut sebagai member di eschool lain)

            // Eschool Matematika (ID: 4) - Sekolah 2
            ['eschool_id' => 4, 'member_id' => 10], // Rina Permata (bendahara + member)
            ['eschool_id' => 4, 'member_id' => 12], // Lisa
            ['eschool_id' => 4, 'member_id' => 13], // Raka

            // Eschool Teater (ID: 5) - Sekolah 2
            ['eschool_id' => 5, 'member_id' => 11], // Doni (bendahara + member)
            ['eschool_id' => 5, 'member_id' => 12], // Lisa (ikut sebagai member di eschool lain)
            ['eschool_id' => 5, 'member_id' => 10], // Rina (ikut sebagai member biasa)
        ];

        foreach ($eschoolMembers as &$eschoolMember) {
            $eschoolMember['created_at'] = now();
            $eschoolMember['updated_at'] = now();
        }
          DB::table('eschool_member')->insert($eschoolMembers);
    }
}
