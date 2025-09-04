<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class NewDatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     * Master seeder untuk multi-role system yang baru
     */
    public function run(): void
    {
        $this->command->info('🚀 Starting Multi-Role E-School Database Seeding...');
        $this->command->line('');

        // ========================
        // PHASE 1: FOUNDATION DATA
        // ========================
        $this->command->info('📋 Phase 1: Foundation Data');
        
        $this->call([
            NewSchoolSeeder::class,
            NewUserSeeder::class,
            NewEschoolSeeder::class,
            NewMemberSeeder::class,
        ]);

        $this->command->line('');

        // ========================
        // PHASE 2: MULTI-ROLE SYSTEM
        // ========================
        $this->command->info('🔗 Phase 2: Multi-Role System');
        
        $this->call([
            NewUserEschoolRoleSeeder::class,
            NewEschoolMemberSeeder::class, // Tambahkan seeder untuk tabel pivot
        ]);

        $this->command->line('');

        // ========================
        // PHASE 3: OPERATIONAL DATA
        // ========================
        $this->command->info('💰 Phase 3: Operational Data');
        
        $this->call([
            NewKasRecordSeeder::class,
            NewKasPaymentSeeder::class,
            NewAttendanceRecordSeeder::class,
        ]);

        $this->command->line('');

        // ========================
        // SUMMARY & LOGIN INFO
        // ========================
        $this->command->info('🎉 SEEDING COMPLETED SUCCESSFULLY!');
        $this->command->line('');
        $this->command->info('📊 Data Summary:');
        $this->command->line('🏫 Schools: 3 schools created');
        $this->command->line('👥 Users: 17 users with different roles');
        $this->command->line('🏛️ Eschools: 6 eschools (5 active, 1 inactive)');
        $this->command->line('🎓 Members: 9 member records');
        $this->command->line('🔗 Multi-Roles: 25+ role assignments');
        $this->command->line('💰 Kas Records: 17 income/expense records');
        $this->command->line('💳 Kas Payments: 27+ payment records');
        $this->command->line('📅 Attendance: 100+ attendance records');
        $this->command->line('');

        $this->command->info('🔐 LOGIN CREDENTIALS FOR TESTING:');
        $this->command->line('');
        
        $this->command->info('👨‍💼 STAFF ACCOUNTS:');
        $this->command->line('📧 staff.admin@eschool.com | password123 (System Admin)');
        $this->command->line('📧 staff.jakarta@eschool.com | password123 (Staff Jakarta)');
        $this->command->line('📧 staff.bandung@eschool.com | password123 (Staff Bandung)');
        $this->command->line('');
        
        $this->command->info('👨‍🏫 KOORDINATOR ACCOUNTS:');
        $this->command->line('📧 koordinator.karate@gmail.com | password123 (Karate + Taekwondo Jakarta)');
        $this->command->line('📧 koordinator.paskibra@gmail.com | password123 (Paskibra Jakarta)');
        $this->command->line('📧 koordinator.basket@gmail.com | password123 (Basket + Futsal Bandung)');
        $this->command->line('');
        
        $this->command->info('💰 BENDAHARA ACCOUNTS:');
        $this->command->line('📧 bendahara.karate@gmail.com | password123 (Karate + Taekwondo Jakarta)');
        $this->command->line('📧 bendahara.paskibra@gmail.com | password123 (Paskibra Jakarta)');
        $this->command->line('📧 bendahara.basket@gmail.com | password123 (Basket + Futsal Bandung)');
        $this->command->line('');
        
        $this->command->info('🎓 MEMBER ACCOUNTS (Multi-Role Examples):');
        $this->command->line('📧 ahmad.rizki@student.com | password123 (Karate + Paskibra)');
        $this->command->line('📧 siti.nurhaliza@student.com | password123 (Karate + Taekwondo)');
        $this->command->line('📧 multirole1@student.com | password123 (3 Eschools)');
        $this->command->line('📧 multirole2@student.com | password123 (Member + Bendahara)');
        $this->command->line('');

        $this->command->info('✨ MULTI-ROLE FEATURES READY TO TEST:');
        $this->command->line('🔄 Users with multiple roles across different eschools');
        $this->command->line('📊 Cross-eschool analytics and reporting');
        $this->command->line('💰 Multi-eschool kas management');
        $this->command->line('📅 Attendance tracking across multiple activities');
        $this->command->line('🎯 Role-based permissions and access control');
        $this->command->line('');

        $this->command->info('🛠️ NEXT STEPS:');
        $this->command->line('1. Test multi-role login functionality');
        $this->command->line('2. Verify API endpoints with different role contexts');
        $this->command->line('3. Test cross-eschool data access and permissions');
        $this->command->line('4. Validate attendance and kas operations');
        $this->command->line('');
        
        $this->command->info('🎯 Happy Testing! The multi-role e-school system is ready!');
    }
}
