<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\School;
use App\Models\Eschool;
use App\Models\Member;

class MemberControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that coordinators can only see members from their own school.
     *
     * @return void
     */
    public function test_coordinator_can_only_see_members_from_their_school()
    {
        // Create two schools
        $school1 = School::factory()->create(['name' => 'School 1']);
        $school2 = School::factory()->create(['name' => 'School 2']);

        // Create users
        $user1 = User::factory()->create([
            'name' => 'User 1',
            'email' => 'user1@test.com',
            'role' => 'siswa'
        ]);
        
        $user2 = User::factory()->create([
            'name' => 'User 2',
            'email' => 'user2@test.com',
            'role' => 'siswa'
        ]);

        // Create eschools for each school
        $eschool1 = Eschool::factory()->create([
            'school_id' => $school1->id,
            'name' => 'Eschool 1'
        ]);
        
        $eschool2 = Eschool::factory()->create([
            'school_id' => $school2->id,
            'name' => 'Eschool 2'
        ]);

        // Create a coordinator for school 1
        $coordinator = User::factory()->create([
            'name' => 'Coordinator',
            'email' => 'coordinator@test.com',
            'role' => 'koordinator'
        ]);
        
        // Assign coordinator to eschool1
        $eschool1->update(['coordinator_id' => $coordinator->id]);

        // Create members for both schools
        $member1 = Member::factory()->create([
            'school_id' => $school1->id,
            'user_id' => $user1->id,
            'name' => 'Member 1'
        ]);
        
        $member2 = Member::factory()->create([
            'school_id' => $school2->id,
            'user_id' => $user2->id,
            'name' => 'Member 2'
        ]);

        // Attach members to their respective eschools
        $member1->eschools()->attach($eschool1);
        $member2->eschools()->attach($eschool2);

        // Acting as coordinator
        $response = $this->actingAs($coordinator)->get('/api/members');

        $response->assertStatus(200);
        
        // Assert that coordinator only sees member from their school
        $response->assertJsonFragment(['name' => 'Member 1']);
        $response->assertJsonMissing(['name' => 'Member 2']);
    }

    /**
     * Test that staff can only see members from their assigned school.
     *
     * @return void
     */
    public function test_staff_can_only_see_members_from_their_assigned_school()
    {
        // Create two schools
        $school1 = School::factory()->create(['name' => 'School 1']);
        $school2 = School::factory()->create(['name' => 'School 2']);

        // Create users
        $user1 = User::factory()->create([
            'name' => 'User 1',
            'email' => 'user1@test.com',
            'role' => 'siswa'
        ]);
        
        $user2 = User::factory()->create([
            'name' => 'User 2',
            'email' => 'user2@test.com',
            'role' => 'siswa'
        ]);

        // Create staff for school 1
        $staff = User::factory()->create([
            'name' => 'Staff',
            'email' => 'staff@test.com',
            'role' => 'staff',
            'school_id' => $school1->id
        ]);

        // Create members for both schools
        $member1 = Member::factory()->create([
            'school_id' => $school1->id,
            'user_id' => $user1->id,
            'name' => 'Member 1'
        ]);
        
        $member2 = Member::factory()->create([
            'school_id' => $school2->id,
            'user_id' => $user2->id,
            'name' => 'Member 2'
        ]);

        // Acting as staff
        $response = $this->actingAs($staff)->get('/api/members');

        $response->assertStatus(200);
        
        // Assert that staff only sees member from their assigned school
        $response->assertJsonFragment(['name' => 'Member 1']);
        $response->assertJsonMissing(['name' => 'Member 2']);
    }
}