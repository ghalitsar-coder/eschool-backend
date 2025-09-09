<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Profile;
use App\Models\Student;
use App\Models\Eschool;
use App\Models\School;
use App\Models\UserEschoolRole;
use Tymon\JWTAuth\Facades\JWTAuth;

class MembersControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $eschool;
    protected $token;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create a school
        $school = School::factory()->create();
        
        // Create an eschool
        $this->eschool = Eschool::factory()->create([
            'school_id' => $school->id
        ]);
        
        // Create a coordinator user for authentication
        $coordinatorProfile = Profile::factory()->create(['status' => 'active']);
        $this->user = User::factory()->create([
            'profile_id' => $coordinatorProfile->id
        ]);
        
        // Create coordinator role
        UserEschoolRole::factory()->create([
            'user_id' => $this->user->id,
            'eschool_id' => $this->eschool->id,
            'role' => 'coordinator'
        ]);
        
        // Generate JWT token
        $this->token = JWTAuth::fromUser($this->user);
    }

    public function test_can_get_members_list_for_eschool()
    {
        // Create some member users
        $memberProfile1 = Profile::factory()->create(['status' => 'active', 'name' => 'John Doe']);
        $memberUser1 = User::factory()->create(['profile_id' => $memberProfile1->id]);
        $student1 = Student::factory()->create([
            'profile_id' => $memberProfile1->id,
            'student_id' => 'STU001'
        ]);
        
        $memberProfile2 = Profile::factory()->create(['status' => 'active', 'name' => 'Jane Smith']);
        $memberUser2 = User::factory()->create(['profile_id' => $memberProfile2->id]);
        $student2 = Student::factory()->create([
            'profile_id' => $memberProfile2->id,
            'student_id' => 'STU002'
        ]);
        
        // Create member roles
        UserEschoolRole::factory()->create([
            'user_id' => $memberUser1->id,
            'eschool_id' => $this->eschool->id,
            'role' => 'member'
        ]);
        
        UserEschoolRole::factory()->create([
            'user_id' => $memberUser2->id,
            'eschool_id' => $this->eschool->id,
            'role' => 'treasurer'
        ]);
        
        // Make API request
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson("/api/eschool/{$this->eschool->id}/members/list");
        
        // Assert response
        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        '*' => [
                            'user_id',
                            'name',
                            'student_id'
                        ]
                    ]
                ]);
        
        $responseData = $response->json();
        $this->assertTrue($responseData['success']);
        $this->assertCount(2, $responseData['data']);
        
        // Check if the members are correctly formatted
        $memberNames = collect($responseData['data'])->pluck('name')->toArray();
        $this->assertContains('John Doe', $memberNames);
        $this->assertContains('Jane Smith', $memberNames);
        
        $studentIds = collect($responseData['data'])->pluck('student_id')->toArray();
        $this->assertContains('STU001', $studentIds);
        $this->assertContains('STU002', $studentIds);
    }

    public function test_only_returns_active_members()
    {
        // Create an active member
        $activeProfile = Profile::factory()->create(['status' => 'active', 'name' => 'Active Member']);
        $activeUser = User::factory()->create(['profile_id' => $activeProfile->id]);
        Student::factory()->create([
            'profile_id' => $activeProfile->id,
            'student_id' => 'ACTIVE001'
        ]);
        
        // Create an inactive member
        $inactiveProfile = Profile::factory()->create(['status' => 'inactive', 'name' => 'Inactive Member']);
        $inactiveUser = User::factory()->create(['profile_id' => $inactiveProfile->id]);
        Student::factory()->create([
            'profile_id' => $inactiveProfile->id,
            'student_id' => 'INACTIVE001'
        ]);
        
        // Create member roles for both
        UserEschoolRole::factory()->create([
            'user_id' => $activeUser->id,
            'eschool_id' => $this->eschool->id,
            'role' => 'member'
        ]);
        
        UserEschoolRole::factory()->create([
            'user_id' => $inactiveUser->id,
            'eschool_id' => $this->eschool->id,
            'role' => 'member'
        ]);
        
        // Make API request
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson("/api/eschool/{$this->eschool->id}/members/list");
        
        // Assert response
        $response->assertStatus(200);
        
        $responseData = $response->json();
        $this->assertTrue($responseData['success']);
        $this->assertCount(1, $responseData['data']); // Only active member should be returned
        
        $this->assertEquals('Active Member', $responseData['data'][0]['name']);
        $this->assertEquals('ACTIVE001', $responseData['data'][0]['student_id']);
    }

    public function test_handles_members_without_student_id()
    {
        // Create a member without student record
        $memberProfile = Profile::factory()->create(['status' => 'active', 'name' => 'Member Without Student ID']);
        $memberUser = User::factory()->create(['profile_id' => $memberProfile->id]);
        
        // Create member role (no student record created)
        UserEschoolRole::factory()->create([
            'user_id' => $memberUser->id,
            'eschool_id' => $this->eschool->id,
            'role' => 'member'
        ]);
        
        // Make API request
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson("/api/eschool/{$this->eschool->id}/members/list");
        
        // Assert response
        $response->assertStatus(200);
        
        $responseData = $response->json();
        $this->assertTrue($responseData['success']);
        $this->assertCount(1, $responseData['data']);
        
        $this->assertEquals('Member Without Student ID', $responseData['data'][0]['name']);
        $this->assertEquals('N/A', $responseData['data'][0]['student_id']); // Should default to 'N/A'
    }

    public function test_returns_empty_array_when_no_members()
    {
        // Make API request to eschool with no members
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson("/api/eschool/{$this->eschool->id}/members/list");
        
        // Assert response
        $response->assertStatus(200);
        
        $responseData = $response->json();
        $this->assertTrue($responseData['success']);
        $this->assertCount(0, $responseData['data']);
    }
}