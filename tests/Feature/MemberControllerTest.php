<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Profile;
use App\Models\School;
use App\Models\Eschool;
use App\Models\Student;
use Illuminate\Support\Facades\Hash;

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

        // Create profiles
        $profile1 = Profile::factory()->create(['name' => 'User 1']);
        $profile2 = Profile::factory()->create(['name' => 'User 2']);
        $profile3 = Profile::factory()->create(['name' => 'Coordinator']);

        // Create users
        $user1 = User::create([
            'profile_id' => $profile1->id,
            'name' => 'User 1',
            'email' => 'user1@test.com',
            'password' => Hash::make('password123'),
        ]);
        
        $user2 = User::create([
            'profile_id' => $profile2->id,
            'name' => 'User 2',
            'email' => 'user2@test.com',
            'password' => Hash::make('password123'),
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
        $coordinator = User::create([
            'profile_id' => $profile3->id,
            'name' => 'Coordinator',
            'email' => 'coordinator@test.com',
            'password' => Hash::make('password123'),
        ]);
        
        // Assign coordinator to eschool1
        $eschool1->update(['coordinator_id' => $coordinator->id]);

        // Create students for both schools
        $student1 = Student::factory()->create([
            'school_id' => $school1->id,
            'user_id' => $user1->id,
            'student_id' => 'STU001',
            'grade_level' => 'X'
        ]);
        
        $student2 = Student::factory()->create([
            'school_id' => $school2->id,
            'user_id' => $user2->id,
            'student_id' => 'STU002',
            'grade_level' => 'XI'
        ]);

        // Attach students to their respective eschools
        $student1->eschools()->attach($eschool1);
        $student2->eschools()->attach($eschool2);

        // Acting as coordinator
        $response = $this->actingAs($coordinator)->get('/api/members');

        $response->assertStatus(200);
        
        // Assert that coordinator only sees student from their school
        $response->assertJsonFragment(['name' => $user1->name]);
        $response->assertJsonMissing(['name' => $user2->name]);
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

        // Create profiles
        $profile1 = Profile::factory()->create(['name' => 'User 1']);
        $profile2 = Profile::factory()->create(['name' => 'User 2']);
        $profile3 = Profile::factory()->create(['name' => 'Staff']);

        // Create users
        $user1 = User::create([
            'profile_id' => $profile1->id,
            'name' => 'User 1',
            'email' => 'user1@test.com',
            'password' => Hash::make('password123'),
        ]);
        
        $user2 = User::create([
            'profile_id' => $profile2->id,
            'name' => 'User 2',
            'email' => 'user2@test.com',
            'password' => Hash::make('password123'),
        ]);

        // Create staff for school 1
        $staff = User::create([
            'profile_id' => $profile3->id,
            'name' => 'Staff',
            'email' => 'staff@test.com',
            'password' => Hash::make('password123'),
        ]);

        // Create students for both schools
        $student1 = Student::factory()->create([
            'school_id' => $school1->id,
            'user_id' => $user1->id,
            'student_id' => 'STU001',
            'grade_level' => 'X'
        ]);
        
        $student2 = Student::factory()->create([
            'school_id' => $school2->id,
            'user_id' => $user2->id,
            'student_id' => 'STU002',
            'grade_level' => 'XI'
        ]);

        // Acting as staff
        $response = $this->actingAs($staff)->get('/api/members');

        $response->assertStatus(200);
        
        // Assert that staff only sees student from their assigned school
        $response->assertJsonFragment(['name' => $user1->name]);
        $response->assertJsonMissing(['name' => $user2->name]);
    }
}