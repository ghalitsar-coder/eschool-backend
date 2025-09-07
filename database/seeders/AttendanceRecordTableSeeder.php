<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AttendanceRecordTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Kita perlu mendapatkan ID yang sebenarnya dari user_eschool_roles
        // berdasarkan kombinasi user_id dan eschool_id
        
        $attendances = [];
        
        // Absensi untuk Basket (eschool_id: 1)
        // Pak Joko (user_id: 2) sebagai koordinator
        // Andi (user_id: 5) sebagai bendahara
        // Budi (user_id: 8) sebagai member
        // Cici (user_id: 9) sebagai member
        // Dedi (user_id: 10) sebagai member
        
        $pakJokoRole = DB::table('user_eschool_roles')->where('user_id', 2)->where('eschool_id', 1)->first();
        if ($pakJokoRole) {
            $attendances[] = [
                'user_eschool_role_id' => $pakJokoRole->id,
                'status' => 'present',
                'notes' => 'Latihan rutin',
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        
        $andiRole = DB::table('user_eschool_roles')->where('user_id', 5)->where('eschool_id', 1)->first();
        if ($andiRole) {
            $attendances[] = [
                'user_eschool_role_id' => $andiRole->id,
                'status' => 'present',
                'notes' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        
        $budiRole1 = DB::table('user_eschool_roles')->where('user_id', 8)->where('eschool_id', 1)->first();
        if ($budiRole1) {
            $attendances[] = [
                'user_eschool_role_id' => $budiRole1->id,
                'status' => 'present',
                'notes' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        
        $ciciRole1 = DB::table('user_eschool_roles')->where('user_id', 9)->where('eschool_id', 1)->first();
        if ($ciciRole1) {
            $attendances[] = [
                'user_eschool_role_id' => $ciciRole1->id,
                'status' => 'absent',
                'notes' => 'Sakit',
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        
        $dediRole = DB::table('user_eschool_roles')->where('user_id', 10)->where('eschool_id', 1)->first();
        if ($dediRole) {
            $attendances[] = [
                'user_eschool_role_id' => $dediRole->id,
                'status' => 'late',
                'notes' => 'Terlambat 15 menit',
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        
        // Absensi untuk Voli (eschool_id: 2)
        // Budi (user_id: 8) sebagai member
        // Cici (user_id: 9) sebagai member
        // Andi (user_id: 5) sebagai member
        
        $budiRole2 = DB::table('user_eschool_roles')->where('user_id', 8)->where('eschool_id', 2)->first();
        if ($budiRole2) {
            $attendances[] = [
                'user_eschool_role_id' => $budiRole2->id,
                'status' => 'present',
                'notes' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        
        $ciciRole2 = DB::table('user_eschool_roles')->where('user_id', 9)->where('eschool_id', 2)->first();
        if ($ciciRole2) {
            $attendances[] = [
                'user_eschool_role_id' => $ciciRole2->id,
                'status' => 'present',
                'notes' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        
        $andiRole2 = DB::table('user_eschool_roles')->where('user_id', 5)->where('eschool_id', 2)->first();
        if ($andiRole2) {
            $attendances[] = [
                'user_eschool_role_id' => $andiRole2->id,
                'status' => 'late',
                'notes' => 'Terlambat 10 menit',
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        
        // Absensi untuk Lukis (eschool_id: 3)
        // Hani (user_id: 7) sebagai bendahara
        // Budi (user_id: 8) sebagai member
        // Cici (user_id: 9) sebagai member
        
        $haniRole = DB::table('user_eschool_roles')->where('user_id', 7)->where('eschool_id', 3)->first();
        if ($haniRole) {
            $attendances[] = [
                'user_eschool_role_id' => $haniRole->id,
                'status' => 'present',
                'notes' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        
        $budiRole3 = DB::table('user_eschool_roles')->where('user_id', 8)->where('eschool_id', 3)->first();
        if ($budiRole3) {
            $attendances[] = [
                'user_eschool_role_id' => $budiRole3->id,
                'status' => 'present',
                'notes' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        
        $ciciRole3 = DB::table('user_eschool_roles')->where('user_id', 9)->where('eschool_id', 3)->first();
        if ($ciciRole3) {
            $attendances[] = [
                'user_eschool_role_id' => $ciciRole3->id,
                'status' => 'absent',
                'notes' => 'Izin',
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        
        // Absensi untuk Matematika (eschool_id: 4)
        // Eka (user_id: 6) sebagai bendahara
        // Fani (user_id: 11) sebagai member
        // Gita (user_id: 12) sebagai member
        
        $ekaRole = DB::table('user_eschool_roles')->where('user_id', 6)->where('eschool_id', 4)->first();
        if ($ekaRole) {
            $attendances[] = [
                'user_eschool_role_id' => $ekaRole->id,
                'status' => 'present',
                'notes' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        
        $faniRole1 = DB::table('user_eschool_roles')->where('user_id', 11)->where('eschool_id', 4)->first();
        if ($faniRole1) {
            $attendances[] = [
                'user_eschool_role_id' => $faniRole1->id,
                'status' => 'present',
                'notes' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        
        $gitaRole1 = DB::table('user_eschool_roles')->where('user_id', 12)->where('eschool_id', 4)->first();
        if ($gitaRole1) {
            $attendances[] = [
                'user_eschool_role_id' => $gitaRole1->id,
                'status' => 'late',
                'notes' => 'Terlambat 5 menit',
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        
        // Absensi untuk Musik (eschool_id: 5)
        // Fani (user_id: 11) sebagai member
        // Gita (user_id: 12) sebagai member
        
        $faniRole2 = DB::table('user_eschool_roles')->where('user_id', 11)->where('eschool_id', 5)->first();
        if ($faniRole2) {
            $attendances[] = [
                'user_eschool_role_id' => $faniRole2->id,
                'status' => 'present',
                'notes' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        
        $gitaRole2 = DB::table('user_eschool_roles')->where('user_id', 12)->where('eschool_id', 5)->first();
        if ($gitaRole2) {
            $attendances[] = [
                'user_eschool_role_id' => $gitaRole2->id,
                'status' => 'present',
                'notes' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        
        // Absensi untuk Robotika (eschool_id: 6)
        // Budi (user_id: 8) sebagai member
        // Iko (user_id: 13) sebagai member
        
        $budiRole4 = DB::table('user_eschool_roles')->where('user_id', 8)->where('eschool_id', 6)->first();
        if ($budiRole4) {
            $attendances[] = [
                'user_eschool_role_id' => $budiRole4->id,
                'status' => 'present',
                'notes' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        
        $ikoRole1 = DB::table('user_eschool_roles')->where('user_id', 13)->where('eschool_id', 6)->first();
        if ($ikoRole1) {
            $attendances[] = [
                'user_eschool_role_id' => $ikoRole1->id,
                'status' => 'absent',
                'notes' => 'Sakit',
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        
        // Absensi untuk Teater (eschool_id: 7)
        // Iko (user_id: 13) sebagai member
        // Joni (user_id: 14) sebagai member
        
        $ikoRole2 = DB::table('user_eschool_roles')->where('user_id', 13)->where('eschool_id', 7)->first();
        if ($ikoRole2) {
            $attendances[] = [
                'user_eschool_role_id' => $ikoRole2->id,
                'status' => 'present',
                'notes' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        
        $joniRole = DB::table('user_eschool_roles')->where('user_id', 14)->where('eschool_id', 7)->first();
        if ($joniRole) {
            $attendances[] = [
                'user_eschool_role_id' => $joniRole->id,
                'status' => 'present',
                'notes' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        
        if (!empty($attendances)) {
            DB::table('attendance_record')->insert($attendances);
        }
    }
}