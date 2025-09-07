<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Profile;
use App\Models\Eschool;
use App\Models\UserEschoolRole;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

class MultiRoleProfileTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_fetch_multi_role_profile_data()
    {
        // Create a profile
        $profile = Profile::factory()->create();
        
        // Create a user with profile
        $user = User::create([
            'profile_id' => $profile->id,
            'name' => $profile->name,
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
        ]);
        
        // Create eschools
        $eschool1 = Eschool::factory()->create();
        $eschool2 = Eschool::factory()->create();
        
        // Assign roles to user
        UserEschoolRole::factory()->create([
            'user_id' => $user->id,
            'eschool_id' => $eschool1->id,
            'role' => 'member'
        ]);
        
        UserEschoolRole::factory()->create([
            'user_id' => $user->id,
            'eschool_id' => $eschool2->id,
            'role' => 'treasurer'
        ]);
        
        // Authenticate the user with token
        $token = auth('api')->login($user);
        
        // Call the multi-role profile endpoint
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/profile/multi-role');
        
        $response->assertStatus(200);
        
        $response->assertJsonStructure([
            'user' => [
                'id',
                'name',
                'email',
                'base_role',
                'is_system_admin'
            ],
            'eschool_roles' => [
                '*' => [
                    'eschool_id',
                    'eschool_name',
                    'school_id',
                    'school_name',
                    'role_in_eschool',
                    'permissions',
                    'assigned_at',
                    'status',
                    'kas_summary',
                    'attendance_summary'
                ]
            ],
            'overall_summary' => [
                'total_eschools',
                'roles' => [
                    'koordinator',
                    'bendahara',
                    'member'
                ],
                'performance' => [
                    'avg_attendance_rate',
                    'total_kas_managed',
                    'total_personal_kas',
                    'overall_activity_score'
                ]
            ],
            'recent_activities' => [
                '*' => [
                    'type',
                    'eschool_name',
                    'description',
                    'date',
                    'role_context'
                ]
            ]
        ]);
        
        $response->assertJson([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'base_role' => 'siswa',
                'is_system_admin' => false
            ],
            'overall_summary' => [
                'total_eschools' => 2,
                'roles' => [
                    'koordinator' => 0,
                    'bendahara' => 0,
                    'member' => 1
                ]
            ]
        ]);
    }
    
    /** @test */
    public function it_returns_empty_data_for_user_with_no_roles()
    {
        // Create a profile
        $profile = Profile::factory()->create();
        
        // Create a user with profile
        $user = User::create([
            'profile_id' => $profile->id,
            'name' => $profile->name,
            'email' => 'test2@example.com',
            'password' => Hash::make('password123'),
        ]);
        
        // Authenticate the user with token
        $token = auth('api')->login($user);
        
        // Call the multi-role profile endpoint
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/profile/multi-role');
        
        $response->assertStatus(200);
        
        $response->assertJson([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'base_role' => 'siswa',
                'is_system_admin' => false
            ],
            'eschool_roles' => [],
            'overall_summary' => [
                'total_eschools' => 0,
                'roles' => [
                    'koordinator' => 0,
                    'bendahara' => 0,
                    'member' => 0
                ]
            ],
            'recent_activities' => []
        ]);
    }
}