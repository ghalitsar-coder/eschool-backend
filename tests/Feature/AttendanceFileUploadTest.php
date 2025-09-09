<?php

namespace Tests\Feature;

use App\Models\AttendanceRecord;
use App\Models\Eschool;
use App\Models\User;
use App\Models\UserEschoolRole;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AttendanceFileUploadTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $eschool;
    protected $member;
    protected $userEschoolRole;
    protected $memberEschoolRole;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Use fake storage for testing
        Storage::fake('public');

        // Create test data
        $this->user = User::factory()->create();
        $this->member = User::factory()->create();
        $this->eschool = Eschool::factory()->create();

        // Create user roles
        $this->userEschoolRole = UserEschoolRole::factory()->create([
            'user_id' => $this->user->id,
            'eschool_id' => $this->eschool->id,
            'role' => 'coordinator'
        ]);

        $this->memberEschoolRole = UserEschoolRole::factory()->create([
            'user_id' => $this->member->id,
            'eschool_id' => $this->eschool->id,
            'role' => 'member'
        ]);
    }

    public function test_can_create_attendance_with_proof_document_for_absent_member()
    {
        // Create a fake proof document
        $proofDocument = UploadedFile::fake()->image('proof.jpg', 100, 100)->size(1024);

        $response = $this->actingAs($this->user, 'api')
            ->postJson("/api/eschool/{$this->eschool->id}/attendance/records", [
                'date' => '2025-01-15',
                'members' => [
                    [
                        'member_id' => $this->member->id,
                        'is_present' => false,
                        'notes' => 'Sick leave',
                        'proof_document' => $proofDocument
                    ]
                ]
            ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => '1 attendance record(s) created successfully.'
            ]);

        // Verify attendance record was created
        $this->assertDatabaseHas('attendance_record', [
            'user_eschool_role_id' => $this->memberEschoolRole->id,
            'date' => '2025-01-15',
            'status' => 'absent',
            'notes' => 'Sick leave'
        ]);

        // Verify file was uploaded
        $attendanceRecord = AttendanceRecord::where('user_eschool_role_id', $this->memberEschoolRole->id)->first();
        $this->assertNotNull($attendanceRecord->proof_document);
        Storage::disk('public')->assertExists($attendanceRecord->proof_document);

        // Verify response includes proof document URL
        $responseData = $response->json('data')[0];
        $this->assertNotNull($responseData['proof_document']);
        $this->assertTrue(str_contains($responseData['proof_document'], 'storage/attendance/proofs/'));
    }

    public function test_requires_proof_document_for_absent_member()
    {
        $response = $this->actingAs($this->user, 'api')
            ->postJson("/api/eschool/{$this->eschool->id}/attendance/records", [
                'date' => '2025-01-15',
                'members' => [
                    [
                        'member_id' => $this->member->id,
                        'is_present' => false,
                        'notes' => 'Sick leave'
                        // No proof_document provided
                    ]
                ]
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['members.0.proof_document']);
    }

    public function test_does_not_require_proof_document_for_present_member()
    {
        $response = $this->actingAs($this->user, 'api')
            ->postJson("/api/eschool/{$this->eschool->id}/attendance/records", [
                'date' => '2025-01-15',
                'members' => [
                    [
                        'member_id' => $this->member->id,
                        'is_present' => true,
                        'notes' => 'Present'
                        // No proof_document needed
                    ]
                ]
            ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => '1 attendance record(s) created successfully.'
            ]);

        // Verify attendance record was created without proof document
        $this->assertDatabaseHas('attendance_record', [
            'user_eschool_role_id' => $this->memberEschoolRole->id,
            'date' => '2025-01-15',
            'status' => 'present',
            'proof_document' => null
        ]);
    }

    public function test_validates_file_type_for_proof_document()
    {
        // Create an invalid file type
        $invalidFile = UploadedFile::fake()->create('document.txt', 100);

        $response = $this->actingAs($this->user, 'api')
            ->postJson("/api/eschool/{$this->eschool->id}/attendance/records", [
                'date' => '2025-01-15',
                'members' => [
                    [
                        'member_id' => $this->member->id,
                        'is_present' => false,
                        'notes' => 'Sick leave',
                        'proof_document' => $invalidFile
                    ]
                ]
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['members.0.proof_document']);
    }

    public function test_validates_file_size_for_proof_document()
    {
        // Create a file that's too large (6MB)
        $largeFile = UploadedFile::fake()->image('large.jpg')->size(6144);

        $response = $this->actingAs($this->user, 'api')
            ->postJson("/api/eschool/{$this->eschool->id}/attendance/records", [
                'date' => '2025-01-15',
                'members' => [
                    [
                        'member_id' => $this->member->id,
                        'is_present' => false,
                        'notes' => 'Sick leave',
                        'proof_document' => $largeFile
                    ]
                ]
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['members.0.proof_document']);
    }

    public function test_can_update_attendance_with_new_proof_document()
    {
        // Create initial attendance record
        $attendanceRecord = AttendanceRecord::factory()->create([
            'user_eschool_role_id' => $this->memberEschoolRole->id,
            'date' => '2025-01-15',
            'status' => 'absent',
            'proof_document' => null
        ]);

        // Create new proof document
        $newProofDocument = UploadedFile::fake()->image('new_proof.jpg');

        $response = $this->actingAs($this->user, 'api')
            ->putJson("/api/eschool/{$this->eschool->id}/attendance/records/{$attendanceRecord->id}", [
                'status' => 'absent',
                'notes' => 'Updated with proof',
                'proof_document' => $newProofDocument
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Attendance record updated successfully.'
            ]);

        // Verify proof document was added
        $attendanceRecord->refresh();
        $this->assertNotNull($attendanceRecord->proof_document);
        Storage::disk('public')->assertExists($attendanceRecord->proof_document);
    }

    public function test_deletes_proof_document_when_attendance_record_is_deleted()
    {
        // Create attendance record with proof document
        $proofDocument = UploadedFile::fake()->image('proof.jpg');
        $filePath = 'attendance/proofs/test_proof.jpg';
        Storage::disk('public')->put($filePath, $proofDocument->getContent());

        $attendanceRecord = AttendanceRecord::factory()->create([
            'user_eschool_role_id' => $this->memberEschoolRole->id,
            'date' => '2025-01-15',
            'status' => 'absent',
            'proof_document' => $filePath
        ]);

        // Verify file exists
        Storage::disk('public')->assertExists($filePath);

        // Delete attendance record
        $response = $this->actingAs($this->user, 'api')
            ->deleteJson("/api/eschool/{$this->eschool->id}/attendance/records/{$attendanceRecord->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Attendance record deleted successfully.'
            ]);

        // Verify file was deleted
        Storage::disk('public')->assertMissing($filePath);
    }
}