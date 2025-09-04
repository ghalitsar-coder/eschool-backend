<?php

namespace Database\Seeders;

use App\Models\RolePermission;
use Illuminate\Database\Seeder;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            // Koordinator permissions
            ['role' => 'koordinator', 'permission' => 'manage_eschool', 'description' => 'Can manage eschool settings'],
            ['role' => 'koordinator', 'permission' => 'manage_members', 'description' => 'Can add/remove members'],
            ['role' => 'koordinator', 'permission' => 'manage_attendance', 'description' => 'Can take attendance'],
            ['role' => 'koordinator', 'permission' => 'view_all_reports', 'description' => 'Can view all reports'],
            ['role' => 'koordinator', 'permission' => 'assign_roles', 'description' => 'Can assign member roles'],
            ['role' => 'koordinator', 'permission' => 'view_analytics', 'description' => 'Can view analytics'],
            
            // Bendahara permissions
            ['role' => 'bendahara', 'permission' => 'manage_kas', 'description' => 'Can manage kas transactions'],
            ['role' => 'bendahara', 'permission' => 'view_all_kas', 'description' => 'Can view all kas records'],
            ['role' => 'bendahara', 'permission' => 'approve_transactions', 'description' => 'Can approve kas transactions'],
            ['role' => 'bendahara', 'permission' => 'view_kas_reports', 'description' => 'Can view kas reports'],
            ['role' => 'bendahara', 'permission' => 'view_members', 'description' => 'Can view member list'],
            ['role' => 'bendahara', 'permission' => 'export_kas_data', 'description' => 'Can export kas data'],
            
            // Member permissions
            ['role' => 'member', 'permission' => 'view_own_kas', 'description' => 'Can view own kas balance'],
            ['role' => 'member', 'permission' => 'view_attendance', 'description' => 'Can view attendance records'],
            ['role' => 'member', 'permission' => 'view_own_profile', 'description' => 'Can view own profile'],
            ['role' => 'member', 'permission' => 'submit_attendance', 'description' => 'Can submit attendance'],
            ['role' => 'member', 'permission' => 'view_eschool_info', 'description' => 'Can view eschool information'],
        ];
        
        foreach ($permissions as $permission) {
            RolePermission::create($permission);
        }
        
        $this->command->info('Role permissions seeded successfully!');
    }
}
