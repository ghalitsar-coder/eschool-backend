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
        $kasRecords = [];
        
        // Transaksi kas untuk Basket (eschool_id: 1)
        // Andi sebagai bendahara
        $andiRole = DB::table('user_eschool_roles')->where('user_id', 5)->where('eschool_id', 1)->where('role', 'treasurer')->first();
        if ($andiRole) {
            $kasRecords[] = [
                'eschool_id' => 1, // Basket
                'description' => 'Pembelian bola basket',
                'category' => 'expense',
                'amount' => 150000.00,
                'date' => now()->subDays(10),
                'recorder_id' => $andiRole->id, // Andi (bendahara)
                'created_at' => now(),
                'updated_at' => now(),
            ];
            
            $kasRecords[] = [
                'eschool_id' => 1, // Basket
                'description' => 'Iuran bulanan anggota',
                'category' => 'income',
                'amount' => 250000.00,
                'date' => now()->subDays(5),
                'recorder_id' => $andiRole->id, // Andi (bendahara)
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        
        // Transaksi kas untuk Voli (eschool_id: 2)
        // Tidak ada bendahara untuk Voli dalam contoh ini
        
        // Transaksi kas untuk Lukis (eschool_id: 3)
        // Hani sebagai bendahara
        $haniRole = DB::table('user_eschool_roles')->where('user_id', 7)->where('eschool_id', 3)->where('role', 'treasurer')->first();
        if ($haniRole) {
            $kasRecords[] = [
                'eschool_id' => 3, // Lukis
                'description' => 'Pembelian kanvas dan cat',
                'category' => 'expense',
                'amount' => 200000.00,
                'date' => now()->subDays(15),
                'recorder_id' => $haniRole->id, // Hani (bendahara)
                'created_at' => now(),
                'updated_at' => now(),
            ];
            
            $kasRecords[] = [
                'eschool_id' => 3, // Lukis
                'description' => 'Iuran bulanan anggota',
                'category' => 'income',
                'amount' => 150000.00,
                'date' => now()->subDays(8),
                'recorder_id' => $haniRole->id, // Hani (bendahara)
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        
        // Transaksi kas untuk Matematika (eschool_id: 4)
        // Eka sebagai bendahara
        $ekaRole = DB::table('user_eschool_roles')->where('user_id', 6)->where('eschool_id', 4)->where('role', 'treasurer')->first();
        if ($ekaRole) {
            $kasRecords[] = [
                'eschool_id' => 4, // Matematika
                'description' => 'Pembelian modul pembelajaran',
                'category' => 'expense',
                'amount' => 100000.00,
                'date' => now()->subDays(12),
                'recorder_id' => $ekaRole->id, // Eka (bendahara)
                'created_at' => now(),
                'updated_at' => now(),
            ];
            
            $kasRecords[] = [
                'eschool_id' => 4, // Matematika
                'description' => 'Iuran bulanan anggota',
                'category' => 'income',
                'amount' => 175000.00,
                'date' => now()->subDays(3),
                'recorder_id' => $ekaRole->id, // Eka (bendahara)
                'created_at' => now(),
                'updated_at' => now(),
            ];
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