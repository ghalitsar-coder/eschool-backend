<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     * Updated for Multi-Role System
     */
    public function run(): void
    {
        $this->command->info('🔄 Using NEW Multi-Role Database Seeder...');
        $this->command->line('');
        
        // Call the new comprehensive seeder
        $this->call([
            NewDatabaseSeeder::class,
        ]);
    }
}
