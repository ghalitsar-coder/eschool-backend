<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class KasPaymentTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clear existing data
        DB::table('kas_payment')->delete();
        
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
        
        $kasPayments = [];
        
        // Pembayaran iuran untuk Basket (eschool_id: 1)
        // Mencari kas_record untuk iuran bulanan Basket
        $basketIncomeRecord = DB::table('kas_record')
            ->where('eschool_id', $eschoolMap['Basket'] ?? 1)
            ->where('category', 'income')
            ->first();
        
        if ($basketIncomeRecord) {
            // Budi (user_id: 8) sebagai member
            if (isset($userMap['Budi'])) {
                $budiRole1 = DB::table('user_eschool_roles')
                    ->where('user_id', $userMap['Budi'])
                    ->where('eschool_id', $eschoolMap['Basket'] ?? 1)
                    ->where('role', 'member')
                    ->first();
                    
                if ($budiRole1) {
                    $kasPayments[] = [
                        'kas_record_id' => $basketIncomeRecord->id,
                        'member_id' => $budiRole1->id,
                        'amount' => 50000.00,
                        'month' => '9',
                        'year' => 2025,
                        'is_paid' => true,
                        'paid_date' => now()->subDays(5)->format('Y-m-d'),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }
            
            // Cici (user_id: 9) sebagai member
            if (isset($userMap['Cici'])) {
                $ciciRole1 = DB::table('user_eschool_roles')
                    ->where('user_id', $userMap['Cici'])
                    ->where('eschool_id', $eschoolMap['Basket'] ?? 1)
                    ->where('role', 'member')
                    ->first();
                    
                if ($ciciRole1) {
                    $kasPayments[] = [
                        'kas_record_id' => $basketIncomeRecord->id,
                        'member_id' => $ciciRole1->id,
                        'amount' => 50000.00,
                        'month' => '9',
                        'year' => 2025,
                        'is_paid' => true,
                        'paid_date' => now()->subDays(5)->format('Y-m-d'),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }
            
            // Dedi (user_id: 10) sebagai member
            if (isset($userMap['Dedi'])) {
                $dediRole = DB::table('user_eschool_roles')
                    ->where('user_id', $userMap['Dedi'])
                    ->where('eschool_id', $eschoolMap['Basket'] ?? 1)
                    ->where('role', 'member')
                    ->first();
                    
                if ($dediRole) {
                    $kasPayments[] = [
                        'kas_record_id' => $basketIncomeRecord->id,
                        'member_id' => $dediRole->id,
                        'amount' => 50000.00,
                        'month' => '9',
                        'year' => 2025,
                        'is_paid' => true,
                        'paid_date' => now()->subDays(5)->format('Y-m-d'),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }
            
            // Andi (user_id: 5) sebagai bendahara
            if (isset($userMap['Andi'])) {
                $andiRole = DB::table('user_eschool_roles')
                    ->where('user_id', $userMap['Andi'])
                    ->where('eschool_id', $eschoolMap['Basket'] ?? 1)
                    ->where('role', 'treasurer')
                    ->first();
                    
                if ($andiRole) {
                    $kasPayments[] = [
                        'kas_record_id' => $basketIncomeRecord->id,
                        'member_id' => $andiRole->id,
                        'amount' => 50000.00,
                        'month' => '9',
                        'year' => 2025,
                        'is_paid' => true,
                        'paid_date' => now()->subDays(5)->format('Y-m-d'),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }
        }
        
        // Pembayaran iuran untuk Lukis (eschool_id: 3)
        // Mencari kas_record untuk iuran bulanan Lukis
        $lukisIncomeRecord = DB::table('kas_record')
            ->where('eschool_id', $eschoolMap['Lukis'] ?? 3)
            ->where('category', 'income')
            ->first();
        
        if ($lukisIncomeRecord) {
            // Budi (user_id: 8) sebagai member
            if (isset($userMap['Budi'])) {
                $budiRole3 = DB::table('user_eschool_roles')
                    ->where('user_id', $userMap['Budi'])
                    ->where('eschool_id', $eschoolMap['Lukis'] ?? 3)
                    ->where('role', 'member')
                    ->first();
                    
                if ($budiRole3) {
                    $kasPayments[] = [
                        'kas_record_id' => $lukisIncomeRecord->id,
                        'member_id' => $budiRole3->id,
                        'amount' => 30000.00,
                        'month' => '9',
                        'year' => 2025,
                        'is_paid' => true,
                        'paid_date' => now()->subDays(8)->format('Y-m-d'),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }
            
            // Cici (user_id: 9) sebagai member
            if (isset($userMap['Cici'])) {
                $ciciRole3 = DB::table('user_eschool_roles')
                    ->where('user_id', $userMap['Cici'])
                    ->where('eschool_id', $eschoolMap['Lukis'] ?? 3)
                    ->where('role', 'member')
                    ->first();
                    
                if ($ciciRole3) {
                    $kasPayments[] = [
                        'kas_record_id' => $lukisIncomeRecord->id,
                        'member_id' => $ciciRole3->id,
                        'amount' => 30000.00,
                        'month' => '9',
                        'year' => 2025,
                        'is_paid' => true,
                        'paid_date' => now()->subDays(8)->format('Y-m-d'),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }
            
            // Hani (user_id: 7) sebagai bendahara
            if (isset($userMap['Hani'])) {
                $haniRole = DB::table('user_eschool_roles')
                    ->where('user_id', $userMap['Hani'])
                    ->where('eschool_id', $eschoolMap['Lukis'] ?? 3)
                    ->where('role', 'treasurer')
                    ->first();
                    
                if ($haniRole) {
                    $kasPayments[] = [
                        'kas_record_id' => $lukisIncomeRecord->id,
                        'member_id' => $haniRole->id,
                        'amount' => 30000.00,
                        'month' => '9',
                        'year' => 2025,
                        'is_paid' => true,
                        'paid_date' => now()->subDays(8)->format('Y-m-d'),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }
        }
        
        // Pembayaran iuran untuk Matematika (eschool_id: 4)
        // Mencari kas_record untuk iuran bulanan Matematika
        $matematikaIncomeRecord = DB::table('kas_record')
            ->where('eschool_id', $eschoolMap['Matematika'] ?? 4)
            ->where('category', 'income')
            ->first();
        
        if ($matematikaIncomeRecord) {
            // Fani (user_id: 11) sebagai member
            if (isset($userMap['Fani'])) {
                $faniRole1 = DB::table('user_eschool_roles')
                    ->where('user_id', $userMap['Fani'])
                    ->where('eschool_id', $eschoolMap['Matematika'] ?? 4)
                    ->where('role', 'member')
                    ->first();
                    
                if ($faniRole1) {
                    $kasPayments[] = [
                        'kas_record_id' => $matematikaIncomeRecord->id,
                        'member_id' => $faniRole1->id,
                        'amount' => 35000.00,
                        'month' => '9',
                        'year' => 2025,
                        'is_paid' => true,
                        'paid_date' => now()->subDays(3)->format('Y-m-d'),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }
            
            // Gita (user_id: 12) sebagai member
            if (isset($userMap['Gita'])) {
                $gitaRole1 = DB::table('user_eschool_roles')
                    ->where('user_id', $userMap['Gita'])
                    ->where('eschool_id', $eschoolMap['Matematika'] ?? 4)
                    ->where('role', 'member')
                    ->first();
                    
                if ($gitaRole1) {
                    $kasPayments[] = [
                        'kas_record_id' => $matematikaIncomeRecord->id,
                        'member_id' => $gitaRole1->id,
                        'amount' => 35000.00,
                        'month' => '9',
                        'year' => 2025,
                        'is_paid' => true,
                        'paid_date' => now()->subDays(3)->format('Y-m-d'),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }
            
            // Eka (user_id: 6) sebagai bendahara
            if (isset($userMap['Eka'])) {
                $ekaRole = DB::table('user_eschool_roles')
                    ->where('user_id', $userMap['Eka'])
                    ->where('eschool_id', $eschoolMap['Matematika'] ?? 4)
                    ->where('role', 'treasurer')
                    ->first();
                    
                if ($ekaRole) {
                    $kasPayments[] = [
                        'kas_record_id' => $matematikaIncomeRecord->id,
                        'member_id' => $ekaRole->id,
                        'amount' => 35000.00,
                        'month' => '9',
                        'year' => 2025,
                        'is_paid' => true,
                        'paid_date' => now()->subDays(3)->format('Y-m-d'),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }
        }
        
        if (!empty($kasPayments)) {
            DB::table('kas_payment')->insert($kasPayments);
        }
    }
}