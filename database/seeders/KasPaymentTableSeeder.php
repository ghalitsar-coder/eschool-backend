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
        $kasPayments = [];
        
        // Pembayaran iuran untuk Basket (eschool_id: 1)
        // Mencari kas_record untuk iuran bulanan Basket
        $basketIncomeRecord = DB::table('kas_record')->where('eschool_id', 1)->where('category', 'income')->first();
        
        if ($basketIncomeRecord) {
            // Budi (user_id: 8) sebagai member
            $budiRole1 = DB::table('user_eschool_roles')->where('user_id', 8)->where('eschool_id', 1)->where('role', 'member')->first();
            if ($budiRole1) {
                $kasPayments[] = [
                    'kas_record_id' => $basketIncomeRecord->id,
                    'member_id' => $budiRole1->id,
                    'amount' => 50000.00,
                    'month' => 'September',
                    'year' => 2025,
                    'is_paid' => true,
                    'paid_date' => now()->subDays(5),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
            
            // Cici (user_id: 9) sebagai member
            $ciciRole1 = DB::table('user_eschool_roles')->where('user_id', 9)->where('eschool_id', 1)->where('role', 'member')->first();
            if ($ciciRole1) {
                $kasPayments[] = [
                    'kas_record_id' => $basketIncomeRecord->id,
                    'member_id' => $ciciRole1->id,
                    'amount' => 50000.00,
                    'month' => 'September',
                    'year' => 2025,
                    'is_paid' => true,
                    'paid_date' => now()->subDays(5),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
            
            // Dedi (user_id: 10) sebagai member
            $dediRole = DB::table('user_eschool_roles')->where('user_id', 10)->where('eschool_id', 1)->where('role', 'member')->first();
            if ($dediRole) {
                $kasPayments[] = [
                    'kas_record_id' => $basketIncomeRecord->id,
                    'member_id' => $dediRole->id,
                    'amount' => 50000.00,
                    'month' => 'September',
                    'year' => 2025,
                    'is_paid' => true,
                    'paid_date' => now()->subDays(5),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
            
            // Andi (user_id: 5) sebagai bendahara
            $andiRole = DB::table('user_eschool_roles')->where('user_id', 5)->where('eschool_id', 1)->where('role', 'treasurer')->first();
            if ($andiRole) {
                $kasPayments[] = [
                    'kas_record_id' => $basketIncomeRecord->id,
                    'member_id' => $andiRole->id,
                    'amount' => 50000.00,
                    'month' => 'September',
                    'year' => 2025,
                    'is_paid' => true,
                    'paid_date' => now()->subDays(5),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }
        
        // Pembayaran iuran untuk Lukis (eschool_id: 3)
        // Mencari kas_record untuk iuran bulanan Lukis
        $lukisIncomeRecord = DB::table('kas_record')->where('eschool_id', 3)->where('category', 'income')->first();
        
        if ($lukisIncomeRecord) {
            // Budi (user_id: 8) sebagai member
            $budiRole3 = DB::table('user_eschool_roles')->where('user_id', 8)->where('eschool_id', 3)->where('role', 'member')->first();
            if ($budiRole3) {
                $kasPayments[] = [
                    'kas_record_id' => $lukisIncomeRecord->id,
                    'member_id' => $budiRole3->id,
                    'amount' => 30000.00,
                    'month' => 'September',
                    'year' => 2025,
                    'is_paid' => true,
                    'paid_date' => now()->subDays(8),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
            
            // Cici (user_id: 9) sebagai member
            $ciciRole3 = DB::table('user_eschool_roles')->where('user_id', 9)->where('eschool_id', 3)->where('role', 'member')->first();
            if ($ciciRole3) {
                $kasPayments[] = [
                    'kas_record_id' => $lukisIncomeRecord->id,
                    'member_id' => $ciciRole3->id,
                    'amount' => 30000.00,
                    'month' => 'September',
                    'year' => 2025,
                    'is_paid' => true,
                    'paid_date' => now()->subDays(8),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
            
            // Hani (user_id: 7) sebagai bendahara
            $haniRole = DB::table('user_eschool_roles')->where('user_id', 7)->where('eschool_id', 3)->where('role', 'treasurer')->first();
            if ($haniRole) {
                $kasPayments[] = [
                    'kas_record_id' => $lukisIncomeRecord->id,
                    'member_id' => $haniRole->id,
                    'amount' => 30000.00,
                    'month' => 'September',
                    'year' => 2025,
                    'is_paid' => true,
                    'paid_date' => now()->subDays(8),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }
        
        // Pembayaran iuran untuk Matematika (eschool_id: 4)
        // Mencari kas_record untuk iuran bulanan Matematika
        $matematikaIncomeRecord = DB::table('kas_record')->where('eschool_id', 4)->where('category', 'income')->first();
        
        if ($matematikaIncomeRecord) {
            // Fani (user_id: 11) sebagai member
            $faniRole1 = DB::table('user_eschool_roles')->where('user_id', 11)->where('eschool_id', 4)->where('role', 'member')->first();
            if ($faniRole1) {
                $kasPayments[] = [
                    'kas_record_id' => $matematikaIncomeRecord->id,
                    'member_id' => $faniRole1->id,
                    'amount' => 35000.00,
                    'month' => 'September',
                    'year' => 2025,
                    'is_paid' => true,
                    'paid_date' => now()->subDays(3),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
            
            // Gita (user_id: 12) sebagai member
            $gitaRole1 = DB::table('user_eschool_roles')->where('user_id', 12)->where('eschool_id', 4)->where('role', 'member')->first();
            if ($gitaRole1) {
                $kasPayments[] = [
                    'kas_record_id' => $matematikaIncomeRecord->id,
                    'member_id' => $gitaRole1->id,
                    'amount' => 35000.00,
                    'month' => 'September',
                    'year' => 2025,
                    'is_paid' => true,
                    'paid_date' => now()->subDays(3),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
            
            // Eka (user_id: 6) sebagai bendahara
            $ekaRole = DB::table('user_eschool_roles')->where('user_id', 6)->where('eschool_id', 4)->where('role', 'treasurer')->first();
            if ($ekaRole) {
                $kasPayments[] = [
                    'kas_record_id' => $matematikaIncomeRecord->id,
                    'member_id' => $ekaRole->id,
                    'amount' => 35000.00,
                    'month' => 'September',
                    'year' => 2025,
                    'is_paid' => true,
                    'paid_date' => now()->subDays(3),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }
        
        if (!empty($kasPayments)) {
            DB::table('kas_payment')->insert($kasPayments);
        }
    }
}