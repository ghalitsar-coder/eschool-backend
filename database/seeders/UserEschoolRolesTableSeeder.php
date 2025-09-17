<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UserEschoolRolesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clear existing data in correct order to avoid foreign key constraint issues
        DB::table('attendance_record')->delete();
        DB::table('kas_payment')->delete();
        DB::table('kas_record')->delete();
        DB::table('user_eschool_roles')->delete();
        
        // Get all users with their profiles
        $users = DB::table('users')
            ->join('profiles', 'users.profile_id', '=', 'profiles.id')
            ->select('users.id as user_id', 'profiles.name as profile_name')
            ->get();
        
        // Get all eschools with their names and school_ids
        $eschools = DB::table('eschools')
            ->select('id', 'name', 'school_id')
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
        
        // Define roles based on the QWEN.md requirements
        $roles = [];
        
        // Staff (Supervisor) - maksimal 2 per sekolah
        if (isset($userMap['Bu Sari'])) {
            $roles[] = [
                'user_id' => $userMap['Bu Sari'],
                'eschool_id' => null,
                'role' => 'supervisor',
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        
        if (isset($userMap['Bu Rina'])) {
            $roles[] = [
                'user_id' => $userMap['Bu Rina'],
                'eschool_id' => null,
                'role' => 'supervisor',
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        
        if (isset($userMap['Pak Heru'])) {
            $roles[] = [
                'user_id' => $userMap['Pak Heru'],
                'eschool_id' => null,
                'role' => 'supervisor',
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        
        // Koordinator - 1 per eschool
        if (isset($userMap['Pak Joko']) && isset($eschoolMap['Basket'])) {
            $roles[] = [
                'user_id' => $userMap['Pak Joko'],
                'eschool_id' => $eschoolMap['Basket'],
                'role' => 'coordinator',
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        
        if (isset($userMap['Pak Dudung']) && isset($eschoolMap['Voli'])) {
            $roles[] = [
                'user_id' => $userMap['Pak Dudung'],
                'eschool_id' => $eschoolMap['Voli'],
                'role' => 'coordinator',
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        
        if (isset($userMap['Pak Jajang']) && isset($eschoolMap['Lukis'])) {
            $roles[] = [
                'user_id' => $userMap['Pak Jajang'],
                'eschool_id' => $eschoolMap['Lukis'],
                'role' => 'coordinator',
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        
        // Bendahara - 1 per eschool
        if (isset($userMap['Andi']) && isset($eschoolMap['Basket'])) {
            $roles[] = [
                'user_id' => $userMap['Andi'],
                'eschool_id' => $eschoolMap['Basket'],
                'role' => 'treasurer',
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        
        if (isset($userMap['Eka']) && isset($eschoolMap['Matematika'])) {
            $roles[] = [
                'user_id' => $userMap['Eka'],
                'eschool_id' => $eschoolMap['Matematika'],
                'role' => 'treasurer',
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        
        if (isset($userMap['Hani']) && isset($eschoolMap['Lukis'])) {
            $roles[] = [
                'user_id' => $userMap['Hani'],
                'eschool_id' => $eschoolMap['Lukis'],
                'role' => 'treasurer',
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        
        // Members - bisa join multiple eschool
        // Sekolah A:
        if (isset($userMap['Budi']) && isset($eschoolMap['Basket'])) {
            $roles[] = [
                'user_id' => $userMap['Budi'],
                'eschool_id' => $eschoolMap['Basket'],
                'role' => 'member',
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        
        if (isset($userMap['Budi']) && isset($eschoolMap['Voli'])) {
            $roles[] = [
                'user_id' => $userMap['Budi'],
                'eschool_id' => $eschoolMap['Voli'],
                'role' => 'member',
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        
        if (isset($userMap['Budi']) && isset($eschoolMap['Lukis'])) {
            $roles[] = [
                'user_id' => $userMap['Budi'],
                'eschool_id' => $eschoolMap['Lukis'],
                'role' => 'member',
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        
        if (isset($userMap['Cici']) && isset($eschoolMap['Basket'])) {
            $roles[] = [
                'user_id' => $userMap['Cici'],
                'eschool_id' => $eschoolMap['Basket'],
                'role' => 'member',
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        
        if (isset($userMap['Cici']) && isset($eschoolMap['Voli'])) {
            $roles[] = [
                'user_id' => $userMap['Cici'],
                'eschool_id' => $eschoolMap['Voli'],
                'role' => 'member',
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        
        if (isset($userMap['Dedi']) && isset($eschoolMap['Basket'])) {
            $roles[] = [
                'user_id' => $userMap['Dedi'],
                'eschool_id' => $eschoolMap['Basket'],
                'role' => 'member',
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        
        // Sekolah B:
        if (isset($userMap['Andi']) && isset($eschoolMap['Voli'])) {
            $roles[] = [
                'user_id' => $userMap['Andi'],
                'eschool_id' => $eschoolMap['Voli'],
                'role' => 'member',
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        
        if (isset($userMap['Fani']) && isset($eschoolMap['Matematika'])) {
            $roles[] = [
                'user_id' => $userMap['Fani'],
                'eschool_id' => $eschoolMap['Matematika'],
                'role' => 'member',
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        
        if (isset($userMap['Fani']) && isset($eschoolMap['Musik'])) {
            $roles[] = [
                'user_id' => $userMap['Fani'],
                'eschool_id' => $eschoolMap['Musik'],
                'role' => 'member',
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        
        if (isset($userMap['Gita']) && isset($eschoolMap['Musik'])) {
            $roles[] = [
                'user_id' => $userMap['Gita'],
                'eschool_id' => $eschoolMap['Musik'],
                'role' => 'member',
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        
        // Sekolah C:
        if (isset($userMap['Budi']) && isset($eschoolMap['Robotika'])) {
            $roles[] = [
                'user_id' => $userMap['Budi'],
                'eschool_id' => $eschoolMap['Robotika'],
                'role' => 'member',
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        
        if (isset($userMap['Iko']) && isset($eschoolMap['Robotika'])) {
            $roles[] = [
                'user_id' => $userMap['Iko'],
                'eschool_id' => $eschoolMap['Robotika'],
                'role' => 'member',
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        
        if (isset($userMap['Iko']) && isset($eschoolMap['Teater'])) {
            $roles[] = [
                'user_id' => $userMap['Iko'],
                'eschool_id' => $eschoolMap['Teater'],
                'role' => 'member',
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        
        if (isset($userMap['Joni']) && isset($eschoolMap['Teater'])) {
            $roles[] = [
                'user_id' => $userMap['Joni'],
                'eschool_id' => $eschoolMap['Teater'],
                'role' => 'member',
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        
        // Insert all roles
        if (!empty($roles)) {
            DB::table('user_eschool_roles')->insert($roles);
        }
    }
}