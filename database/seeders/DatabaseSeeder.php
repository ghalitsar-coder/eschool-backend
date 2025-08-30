<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            SchoolSeeder::class, // Run SchoolSeeder first
            UserSeeder::class,   // Then UserSeeder (depends on schools)
            EschoolSeeder::class,
            MemberSeeder::class,
            KasRecordSeeder::class,
            KasPaymentSeeder::class,
        ]);
    }
}
