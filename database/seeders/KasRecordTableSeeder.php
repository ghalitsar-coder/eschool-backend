<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class KasRecordTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clear existing data
        DB::table('kas_record')->delete();
        
        // Get all users with their profiles
        $users = DB::table('users')
            ->join('profiles', 'users.profile_id', '=', 'profiles.id')
            ->select('users.id as user_id', 'profiles.name as profile_name')
            ->get();
        
        // Get all eschools with their names
        $eschools = DB::table('eschools')
            ->select('id', 'name')
            ->get();
        
        // Create a mapping of user names to user IDs
        $userMap = [];
        foreach ($users as $user) {
            $userMap[$user->profile_name] = $user->user_id;
        }
        
        // Create a mapping of eschool names to eschool IDs
        $eschoolMap = [];
        foreach ($eschools as $eschool) {
            $eschoolMap[$eschool->name] = $eschool->id;
        }
        
        $kasRecords = [];
        
        // Transaksi kas untuk Basket (eschool_id: 1)
        if (isset($userMap['Andi'])) {
            $andiRole = DB::table('user_eschool_roles')
                ->where('user_id', $userMap['Andi'])
                ->where('eschool_id', $eschoolMap['Basket'] ?? 1)
                ->where('role', 'treasurer')
                ->first();
                
            if ($andiRole) {
                $kasRecords[] = [
                    'eschool_id' => $eschoolMap['Basket'] ?? 1, // Basket
                    'description' => 'Pembelian bola basket',
                    'category' => 'expense',
                    'amount' => 150000.00,
                    'date' => now()->subDays(10)->format('Y-m-d'),
                    'recorder_id' => $andiRole->id, // Andi (bendahara)
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
                
                $kasRecords[] = [
                    'eschool_id' => $eschoolMap['Basket'] ?? 1, // Basket
                    'description' => 'Iuran bulanan anggota',
                    'category' => 'income',
                    'amount' => 250000.00,
                    'date' => now()->subDays(5)->format('Y-m-d'),
                    'recorder_id' => $andiRole->id, // Andi (bendahara)
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }
        
        // Transaksi kas untuk Voli (eschool_id: 2)
        // Tidak ada bendahara untuk Voli dalam contoh ini
        
        // Transaksi kas untuk Lukis (eschool_id: 3)
        if (isset($userMap['Hani'])) {
            $haniRole = DB::table('user_eschool_roles')
                ->where('user_id', $userMap['Hani'])
                ->where('eschool_id', $eschoolMap['Lukis'] ?? 3)
                ->where('role', 'treasurer')
                ->first();
                
            if ($haniRole) {
                $kasRecords[] = [
                    'eschool_id' => $eschoolMap['Lukis'] ?? 3, // Lukis
                    'description' => 'Pembelian kanvas dan cat',
                    'category' => 'expense',
                    'amount' => 200000.00,
                    'date' => now()->subDays(15)->format('Y-m-d'),
                    'recorder_id' => $haniRole->id, // Hani (bendahara)
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
                
                $kasRecords[] = [
                    'eschool_id' => $eschoolMap['Lukis'] ?? 3, // Lukis
                    'description' => 'Iuran bulanan anggota',
                    'category' => 'income',
                    'amount' => 150000.00,
                    'date' => now()->subDays(8)->format('Y-m-d'),
                    'recorder_id' => $haniRole->id, // Hani (bendahara)
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }
        
        // Transaksi kas untuk Matematika (eschool_id: 4)
        if (isset($userMap['Eka'])) {
            $ekaRole = DB::table('user_eschool_roles')
                ->where('user_id', $userMap['Eka'])
                ->where('eschool_id', $eschoolMap['Matematika'] ?? 4)
                ->where('role', 'treasurer')
                ->first();
                
            if ($ekaRole) {
                $kasRecords[] = [
                    'eschool_id' => $eschoolMap['Matematika'] ?? 4, // Matematika
                    'description' => 'Pembelian modul pembelajaran',
                    'category' => 'expense',
                    'amount' => 100000.00,
                    'date' => now()->subDays(12)->format('Y-m-d'),
                    'recorder_id' => $ekaRole->id, // Eka (bendahara)
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
                
                $kasRecords[] = [
                    'eschool_id' => $eschoolMap['Matematika'] ?? 4, // Matematika
                    'description' => 'Iuran bulanan anggota',
                    'category' => 'income',
                    'amount' => 175000.00,
                    'date' => now()->subDays(3)->format('Y-m-d'),
                    'recorder_id' => $ekaRole->id, // Eka (bendahara)
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }
        
        // Transaksi kas untuk Musik (eschool_id: 5)
        // Tidak ada bendahara untuk Musik dalam contoh ini
        
        // Transaksi kas untuk Robotika (eschool_id: 6)
        // Tidak ada bendahara untuk Robotika dalam contoh ini
        
        // Transaksi kas untuk Teater (eschool_id: 7)
        // Tidak ada bendahara untuk Teater dalam contoh ini
        
        if (!empty($kasRecords)) {
            DB::table('kas_record')->insert($kasRecords);
        }
    }
}