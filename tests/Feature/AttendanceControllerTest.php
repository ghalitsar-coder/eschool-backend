<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use App\Models\User;
use App\Models\Eschool;
use App\Models\UserEschoolRole;
use App\Models\AttendanceRecord;
use Tymon\JWTAuth\Facades\JWTAuth;

class AttendanceControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $user;
    protected $eschool;
    protected $userEschoolRole;
    protected $token;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Use fake storage for testing
        Storage::fake('public');
        
        // Create test user
        $this->user = User::factory()->create();
        
        // Create test eschool
        $this->eschool = Eschool::factory()->create();
        
        // Create user eschool role
        $this->userEschoolRole = UserEschoolRole::factory()->create([
            'user_id' => $this->user->id,
            'eschool_id' => $this->eschool->id,
            'role' => 'coordinator'
        ]);
        
        // Generate JWT token
        $this->token = JWTAuth::fromUser($this->user);
    }

    public function test_index_returns_attendance_records_with_pagination()
    {
        // Create test attendance records with different dates to avoid unique constraint violation
        for ($i = 1; $i <= 15; $i++) {
            AttendanceRecord::factory()->create([
                'user_eschool_role_id' => $this->userEschoolRole->id,
                'date' => now()->subDays($i),
                'status' => 'present'
            ]);
        }

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson("/api/eschool/{$this->eschool->id}/attendance/records");

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'message',
                    'data' => [
                        '*' => [
                            'id',
                            'date',
                            'member' => [
                                'user_id',
                                'name',
                                'student_id'
                            ],
                            'is_present',
                            'status',
                            'notes',
                            'proof_document',
                            'created_at',
                            'updated_at'
                        ]
                    ],
                    'meta' => [
                        'total',
                        'per_page',
                        'current_page',
                        'last_page',
                        'from',
                        'to',
                        'has_next_page',
                        'has_prev_page'
                    ]
                ]);

        $this->assertTrue($response->json('success'));
        $this->assertCount(10, $response->json('data')); // Default pagination
    }

    public function test_show_returns_single_attendance_record()
    {
        $attendanceRecord = AttendanceRecord::factory()->create([
            'user_eschool_role_id' => $this->userEschoolRole->id,
            'date' => now()->subDays(1),
            'status' => 'present'
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson("/api/eschool/{$this->eschool->id}/attendance/records/{$attendanceRecord->id}");

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'message',
                    'data' => [
                        'id',
                        'date',
                        'member',
                        'is_present',
                        'status',
                        'notes',
                        'proof_document',
                        'created_at',
                        'updated_at'
                    ]
                ]);

        $this->assertTrue($response->json('success'));
        $this->assertEquals($attendanceRecord->id, $response->json('data.id'));
    }

    public function test_unauthorized_access_returns_403()
    {
        // Create another eschool that user doesn't have access to
        $otherEschool = Eschool::factory()->create();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson("/api/eschool/{$otherEschool->id}/attendance/records");

        $response->assertStatus(403)
                ->assertJson([
                    'success' => false,
                    'message' => 'You do not have permission to view attendance records for this eschool.'
                ]);
    }

    public function test_unauthenticated_access_returns_401()
    {
        $response = $this->getJson("/api/eschool/{$this->eschool->id}/attendance/records");

        $response->assertStatus(401);
    }

    public function test_index_with_search_filter()
    {
        // This test will be expanded when user profiles are properly set up
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson("/api/eschool/{$this->eschool->id}/attendance/records?search=test");

        $response->assertStatus(200);
        $this->assertTrue($response->json('success'));
    }

    public function test_index_with_date_filter()
    {
        $testDate = now()->subDays(1)->format('Y-m-d');
        
        AttendanceRecord::factory()->create([
            'user_eschool_role_id' => $this->userEschoolRole->id,
            'date' => $testDate,
            'status' => 'present'
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson("/api/eschool/{$this->eschool->id}/attendance/records?date={$testDate}");

        $response->assertStatus(200);
        $this->assertTrue($response->json('success'));
    }

    public function test_index_with_status_filter()
    {
        AttendanceRecord::factory()->create([
            'user_eschool_role_id' => $this->userEschoolRole->id,
            'date' => now()->subDays(1),
            'status' => 'absent'
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson("/api/eschool/{$this->eschool->id}/attendance/records?status=absent");

        $response->assertStatus(200);
        $this->assertTrue($response->json('success'));
    }

    public function test_store_creates_multiple_attendance_records()
    {
        // Create additional users and roles for testing multi-member creation
        $user2 = User::factory()->create();
        $user3 = User::factory()->create();
        
        $userRole2 = UserEschoolRole::factory()->create([
            'user_id' => $user2->id,
            'eschool_id' => $this->eschool->id,
            'role' => 'member'
        ]);
        
        $userRole3 = UserEschoolRole::factory()->create([
            'user_id' => $user3->id,
            'eschool_id' => $this->eschool->id,
            'role' => 'member'
        ]);

        $requestData = [
            'date' => now()->format('Y-m-d'),
            'members' => [
                [
                    'member_id' => $user2->id,
                    'is_present' => true,
                    'notes' => 'Present today'
                ],
                [
                    'member_id' => $user3->id,
                    'is_present' => false,
                    'notes' => 'Sick leave'
                ]
            ]
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson("/api/eschool/{$this->eschool->id}/attendance/records", $requestData);

        $response->assertStatus(201)
                ->assertJsonStructure([
                    'success',
                    'message',
                    'data' => [
                        '*' => [
                            'id',
                            'date',
                            'member' => [
                                'user_id',
                                'name',
                                'student_id'
                            ],
                            'is_present',
                            'status',
                            'notes',
                            'proof_document',
                            'created_at',
                            'updated_at'
                        ]
                    ]
                ]);

        $this->assertTrue($response->json('success'));
        $this->assertCount(2, $response->json('data'));
        
        // Verify records were created in database
        $this->assertDatabaseHas('attendance_record', [
            'user_eschool_role_id' => $userRole2->id,
            'date' => now()->format('Y-m-d'),
            'status' => 'present',
            'notes' => 'Present today'
        ]);
        
        $this->assertDatabaseHas('attendance_record', [
            'user_eschool_role_id' => $userRole3->id,
            'date' => now()->format('Y-m-d'),
            'status' => 'absent',
            'notes' => 'Sick leave'
        ]);
    }

    public function test_store_converts_boolean_to_status_enum()
    {
        $user2 = User::factory()->create();
        $userRole2 = UserEschoolRole::factory()->create([
            'user_id' => $user2->id,
            'eschool_id' => $this->eschool->id,
            'role' => 'member'
        ]);

        $requestData = [
            'date' => now()->format('Y-m-d'),
            'members' => [
                [
                    'member_id' => $user2->id,
                    'is_present' => true,
                    'notes' => null
                ]
            ]
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson("/api/eschool/{$this->eschool->id}/attendance/records", $requestData);

        $response->assertStatus(201);
        
        $responseData = $response->json('data')[0];
        $this->assertTrue($responseData['is_present']);
        $this->assertEquals('present', $responseData['status']);
        
        // Test false conversion
        $user3 = User::factory()->create();
        $userRole3 = UserEschoolRole::factory()->create([
            'user_id' => $user3->id,
            'eschool_id' => $this->eschool->id,
            'role' => 'member'
        ]);

        $requestData2 = [
            'date' => now()->subDay()->format('Y-m-d'),
            'members' => [
                [
                    'member_id' => $user3->id,
                    'is_present' => false,
                    'notes' => 'Absent'
                ]
            ]
        ];

        $response2 = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson("/api/eschool/{$this->eschool->id}/attendance/records", $requestData2);

        $response2->assertStatus(201);
        
        $responseData2 = $response2->json('data')[0];
        $this->assertFalse($responseData2['is_present']);
        $this->assertEquals('absent', $responseData2['status']);
    }

    public function test_store_prevents_duplicate_attendance()
    {
        $user2 = User::factory()->create();
        $userRole2 = UserEschoolRole::factory()->create([
            'user_id' => $user2->id,
            'eschool_id' => $this->eschool->id,
            'role' => 'member'
        ]);

        $date = now()->format('Y-m-d');

        // Create first attendance record
        AttendanceRecord::factory()->create([
            'user_eschool_role_id' => $userRole2->id,
            'date' => $date,
            'status' => 'present'
        ]);

        // Try to create duplicate
        $requestData = [
            'date' => $date,
            'members' => [
                [
                    'member_id' => $user2->id,
                    'is_present' => true,
                    'notes' => 'Duplicate attempt'
                ]
            ]
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson("/api/eschool/{$this->eschool->id}/attendance/records", $requestData);

        $response->assertStatus(500)
                ->assertJson([
                    'success' => false
                ]);

        $this->assertStringContainsString('already exists', $response->json('error'));
    }

    public function test_store_validates_member_belongs_to_eschool()
    {
        // Create user not associated with this eschool
        $outsideUser = User::factory()->create();

        $requestData = [
            'date' => now()->format('Y-m-d'),
            'members' => [
                [
                    'member_id' => $outsideUser->id,
                    'is_present' => true,
                    'notes' => 'Should fail'
                ]
            ]
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson("/api/eschool/{$this->eschool->id}/attendance/records", $requestData);

        $response->assertStatus(500)
                ->assertJson([
                    'success' => false
                ]);

        $this->assertStringContainsString('not associated with this eschool', $response->json('error'));
    }

    public function test_store_validates_required_fields()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson("/api/eschool/{$this->eschool->id}/attendance/records", []);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['date', 'members']);
    }

    public function test_store_validates_future_date()
    {
        $user2 = User::factory()->create();
        UserEschoolRole::factory()->create([
            'user_id' => $user2->id,
            'eschool_id' => $this->eschool->id,
            'role' => 'member'
        ]);

        $requestData = [
            'date' => now()->addDay()->format('Y-m-d'), // Future date
            'members' => [
                [
                    'member_id' => $user2->id,
                    'is_present' => true,
                    'notes' => 'Future date test'
                ]
            ]
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson("/api/eschool/{$this->eschool->id}/attendance/records", $requestData);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['date']);
    }

    public function test_store_transaction_rollback_on_failure()
    {
        $user2 = User::factory()->create();
        $user3 = User::factory()->create();
        
        $userRole2 = UserEschoolRole::factory()->create([
            'user_id' => $user2->id,
            'eschool_id' => $this->eschool->id,
            'role' => 'member'
        ]);

        $date = now()->format('Y-m-d');

        // Create existing record for user2
        AttendanceRecord::factory()->create([
            'user_eschool_role_id' => $userRole2->id,
            'date' => $date,
            'status' => 'present'
        ]);

        // Try to create batch with one duplicate (should rollback all)
        $requestData = [
            'date' => $date,
            'members' => [
                [
                    'member_id' => $user2->id, // This will fail due to duplicate
                    'is_present' => true,
                    'notes' => 'Duplicate'
                ],
                [
                    'member_id' => $user3->id, // This should not be created due to rollback
                    'is_present' => false,
                    'notes' => 'Should not exist'
                ]
            ]
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson("/api/eschool/{$this->eschool->id}/attendance/records", $requestData);

        $response->assertStatus(500);

        // Verify user3 record was not created (transaction rolled back)
        $this->assertDatabaseMissing('attendance_record', [
            'date' => $date,
            'notes' => 'Should not exist'
        ]);
    }

    public function test_store_with_proof_document_upload()
    {
        $user2 = User::factory()->create();
        $userRole2 = UserEschoolRole::factory()->create([
            'user_id' => $user2->id,
            'eschool_id' => $this->eschool->id,
            'role' => 'member'
        ]);

        // Create a fake file
        $file = UploadedFile::fake()->image('proof.jpg', 100, 100)->size(1024); // 1MB

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->post("/api/eschool/{$this->eschool->id}/attendance/records", [
            'date' => now()->format('Y-m-d'),
            'members' => [
                [
                    'member_id' => $user2->id,
                    'is_present' => false,
                    'notes' => 'Sick with proof',
                    'proof_document' => $file
                ]
            ]
        ]);

        $response->assertStatus(201);
        $this->assertTrue($response->json('success'));
        
        $responseData = $response->json('data')[0];
        $this->assertNotNull($responseData['proof_document']);
        $this->assertStringContainsString('storage/attendance/proofs/', $responseData['proof_document']);

        // Verify file was stored
        $attendanceRecord = AttendanceRecord::where('user_eschool_role_id', $userRole2->id)->first();
        $this->assertNotNull($attendanceRecord->proof_document);
        Storage::disk('public')->assertExists($attendanceRecord->proof_document);
    }

    public function test_store_validates_proof_document_file_type()
    {
        $user2 = User::factory()->create();
        UserEschoolRole::factory()->create([
            'user_id' => $user2->id,
            'eschool_id' => $this->eschool->id,
            'role' => 'member'
        ]);

        // Create invalid file type
        $file = UploadedFile::fake()->create('document.txt', 100);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->post("/api/eschool/{$this->eschool->id}/attendance/records", [
            'date' => now()->format('Y-m-d'),
            'members' => [
                [
                    'member_id' => $user2->id,
                    'is_present' => false,
                    'notes' => 'Invalid file type',
                    'proof_document' => $file
                ]
            ]
        ]);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['members.0.proof_document']);
    }

    public function test_store_validates_proof_document_file_size()
    {
        $user2 = User::factory()->create();
        UserEschoolRole::factory()->create([
            'user_id' => $user2->id,
            'eschool_id' => $this->eschool->id,
            'role' => 'member'
        ]);

        // Create file that's too large (6MB)
        $file = UploadedFile::fake()->image('large.jpg')->size(6144); // 6MB

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->post("/api/eschool/{$this->eschool->id}/attendance/records", [
            'date' => now()->format('Y-m-d'),
            'members' => [
                [
                    'member_id' => $user2->id,
                    'is_present' => false,
                    'notes' => 'File too large',
                    'proof_document' => $file
                ]
            ]
        ]);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['members.0.proof_document']);
    }

    public function test_update_with_proof_document_upload()
    {
        $attendanceRecord = AttendanceRecord::factory()->create([
            'user_eschool_role_id' => $this->userEschoolRole->id,
            'date' => now()->subDays(1),
            'status' => 'absent',
            'proof_document' => null
        ]);

        // Create a fake file
        $file = UploadedFile::fake()->image('new_proof.jpg', 100, 100)->size(1024);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->putJson("/api/eschool/{$this->eschool->id}/attendance/records/{$attendanceRecord->id}", [
            'status' => 'absent',
            'notes' => 'Updated with proof',
            'proof_document' => $file
        ]);

        $response->assertStatus(200);
        $this->assertTrue($response->json('success'));
        
        $responseData = $response->json('data');
        $this->assertNotNull($responseData['proof_document']);
        $this->assertStringContainsString('storage/attendance/proofs/', $responseData['proof_document']);

        // Verify file was stored
        $attendanceRecord->refresh();
        $this->assertNotNull($attendanceRecord->proof_document);
        Storage::disk('public')->assertExists($attendanceRecord->proof_document);
    }

    public function test_update_removes_proof_document_when_status_changes_to_present()
    {
        // Create attendance record with proof document
        $file = UploadedFile::fake()->image('proof.jpg');
        $filePath = 'attendance/proofs/test_proof.jpg';
        Storage::disk('public')->put($filePath, $file->getContent());

        $attendanceRecord = AttendanceRecord::factory()->create([
            'user_eschool_role_id' => $this->userEschoolRole->id,
            'date' => now()->subDays(1),
            'status' => 'absent',
            'proof_document' => $filePath
        ]);

        // Update status to present
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->putJson("/api/eschool/{$this->eschool->id}/attendance/records/{$attendanceRecord->id}", [
            'status' => 'present',
            'notes' => 'Now present'
        ]);

        $response->assertStatus(200);
        $this->assertTrue($response->json('success'));
        
        $responseData = $response->json('data');
        $this->assertNull($responseData['proof_document']);

        // Verify file was deleted and database updated
        $attendanceRecord->refresh();
        $this->assertNull($attendanceRecord->proof_document);
        Storage::disk('public')->assertMissing($filePath);
    }

    public function test_destroy_deletes_proof_document_file()
    {
        // Create attendance record with proof document
        $file = UploadedFile::fake()->image('proof.jpg');
        $filePath = 'attendance/proofs/test_proof.jpg';
        Storage::disk('public')->put($filePath, $file->getContent());

        $attendanceRecord = AttendanceRecord::factory()->create([
            'user_eschool_role_id' => $this->userEschoolRole->id,
            'date' => now()->subDays(1),
            'status' => 'absent',
            'proof_document' => $filePath
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->deleteJson("/api/eschool/{$this->eschool->id}/attendance/records/{$attendanceRecord->id}");

        $response->assertStatus(200);
        $this->assertTrue($response->json('success'));

        // Verify record was deleted from database
        $this->assertDatabaseMissing('attendance_record', [
            'id' => $attendanceRecord->id
        ]);

        // Verify file was deleted from storage
        Storage::disk('public')->assertMissing($filePath);
    }

    public function test_proof_document_url_generation()
    {
        $attendanceRecord = AttendanceRecord::factory()->create([
            'user_eschool_role_id' => $this->userEschoolRole->id,
            'date' => now()->subDays(1),
            'status' => 'absent',
            'proof_document' => 'attendance/proofs/test_proof.jpg'
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson("/api/eschool/{$this->eschool->id}/attendance/records/{$attendanceRecord->id}");

        $response->assertStatus(200);
        
        $responseData = $response->json('data');
        $this->assertNotNull($responseData['proof_document']);
        $this->assertStringContainsString('storage/attendance/proofs/test_proof.jpg', $responseData['proof_document']);
        $this->assertStringStartsWith('http', $responseData['proof_document']);
    }

    public function test_statistics_returns_dashboard_data()
    {
        // Create test attendance records with different statuses and dates
        AttendanceRecord::create([
            'user_eschool_role_id' => $this->userEschoolRole->id,
            'date' => now()->subDays(1),
            'status' => 'present',
            'notes' => 'Present today'
        ]);
        
        AttendanceRecord::create([
            'user_eschool_role_id' => $this->userEschoolRole->id,
            'date' => now()->subDays(2),
            'status' => 'absent',
            'notes' => 'Sick leave'
        ]);
        
        AttendanceRecord::create([
            'user_eschool_role_id' => $this->userEschoolRole->id,
            'date' => now()->subDays(3),
            'status' => 'late',
            'notes' => 'Traffic jam'
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson("/api/eschool/{$this->eschool->id}/attendance/statistics");

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'message',
                    'data' => [
                        'total_records',
                        'total_present',
                        'total_absent',
                        'total_late',
                        'attendance_rate',
                        'this_week' => [
                            'total',
                            'present',
                            'absent',
                            'rate'
                        ],
                        'this_month' => [
                            'total',
                            'present',
                            'absent',
                            'rate'
                        ]
                    ]
                ]);

        $this->assertTrue($response->json('success'));
        $data = $response->json('data');
        
        $this->assertEquals(3, $data['total_records']);
        $this->assertEquals(1, $data['total_present']);
        $this->assertEquals(1, $data['total_absent']);
        $this->assertEquals(1, $data['total_late']);
        $this->assertEquals(33.3, $data['attendance_rate']);
    }

    public function test_statistics_unauthorized_access_returns_403()
    {
        // Create another eschool that user doesn't have access to
        $otherEschool = Eschool::factory()->create();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson("/api/eschool/{$otherEschool->id}/attendance/statistics");

        $response->assertStatus(403)
                ->assertJson([
                    'success' => false,
                    'message' => 'You do not have permission to view statistics for this eschool.'
                ]);
    }

    public function test_analytics_returns_period_data()
    {
        // Create test attendance records for analytics
        AttendanceRecord::factory()->create([
            'user_eschool_role_id' => $this->userEschoolRole->id,
            'date' => now()->startOfWeek(),
            'status' => 'present'
        ]);
        
        AttendanceRecord::factory()->create([
            'user_eschool_role_id' => $this->userEschoolRole->id,
            'date' => now()->startOfWeek()->addDay(),
            'status' => 'absent'
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson("/api/eschool/{$this->eschool->id}/attendance/analytics?period=week");



        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'message',
                    'data' => [
                        'period',
                        'date_range' => [
                            'start',
                            'end'
                        ],
                        'chart_data' => [
                            '*' => [
                                'date',
                                'total',
                                'present',
                                'absent',
                                'late',
                                'rate'
                            ]
                        ],
                        'trends' => [
                            'attendance_trend',
                            'average_rate',
                            'best_day',
                            'worst_day',
                            'daily_breakdown',
                            'period_comparison'
                        ]
                    ]
                ]);

        $this->assertTrue($response->json('success'));
        $data = $response->json('data');
        $this->assertEquals('week', $data['period']);
        $this->assertIsArray($data['chart_data']);
        $this->assertIsArray($data['trends']);
    }

    public function test_analytics_validates_period_parameter()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson("/api/eschool/{$this->eschool->id}/attendance/analytics?period=invalid");

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['period']);
    }

    public function test_analytics_requires_period_parameter()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson("/api/eschool/{$this->eschool->id}/attendance/analytics");

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['period']);
    }

    public function test_analytics_with_custom_date_range()
    {
        $startDate = now()->subDays(7)->format('Y-m-d');
        $endDate = now()->format('Y-m-d');

        AttendanceRecord::factory()->create([
            'user_eschool_role_id' => $this->userEschoolRole->id,
            'date' => now()->subDays(3),
            'status' => 'present'
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson("/api/eschool/{$this->eschool->id}/attendance/analytics?period=week&start_date={$startDate}&end_date={$endDate}");

        $response->assertStatus(200);
        
        $data = $response->json('data');
        $this->assertEquals($startDate, $data['date_range']['start']);
        $this->assertEquals($endDate, $data['date_range']['end']);
    }

    public function test_analytics_validates_date_range()
    {
        $startDate = now()->format('Y-m-d');
        $endDate = now()->subDays(1)->format('Y-m-d'); // End date before start date

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson("/api/eschool/{$this->eschool->id}/attendance/analytics?period=week&start_date={$startDate}&end_date={$endDate}");

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['end_date']);
    }

    public function test_analytics_unauthorized_access_returns_403()
    {
        // Create another eschool that user doesn't have access to
        $otherEschool = Eschool::factory()->create();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson("/api/eschool/{$otherEschool->id}/attendance/analytics?period=week");

        $response->assertStatus(403)
                ->assertJson([
                    'success' => false,
                    'message' => 'You do not have permission to view analytics for this eschool.'
                ]);
    }

    public function test_export_csv_returns_csv_file()
    {
        // Just test that the endpoint exists and returns the right headers
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->get("/api/eschool/{$this->eschool->id}/attendance/export/csv");

        // Should return 200 and proper headers for CSV
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
        $response->assertHeader('Content-Disposition');
    }

    public function test_export_csv_with_date_range_filter()
    {
        // Create attendance records with different dates
        AttendanceRecord::factory()->create([
            'user_eschool_role_id' => $this->userEschoolRole->id,
            'date' => now()->subDays(1),
            'status' => 'present',
            'notes' => 'Should be included'
        ]);

        AttendanceRecord::factory()->create([
            'user_eschool_role_id' => $this->userEschoolRole->id,
            'date' => now()->subDays(10),
            'status' => 'absent',
            'notes' => 'Should be excluded'
        ]);

        $dateFrom = now()->subDays(5)->format('Y-m-d');
        $dateTo = now()->format('Y-m-d');

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->get("/api/eschool/{$this->eschool->id}/attendance/export/csv?date_from={$dateFrom}&date_to={$dateTo}");

        $response->assertStatus(200);
        
        $content = $response->getContent();
        $this->assertStringContainsString('Should be included', $content);
        $this->assertStringNotContainsString('Should be excluded', $content);
    }

    public function test_export_csv_with_status_filter()
    {
        AttendanceRecord::factory()->create([
            'user_eschool_role_id' => $this->userEschoolRole->id,
            'date' => now()->subDays(1),
            'status' => 'present',
            'notes' => 'Present record'
        ]);

        AttendanceRecord::factory()->create([
            'user_eschool_role_id' => $this->userEschoolRole->id,
            'date' => now()->subDays(2),
            'status' => 'absent',
            'notes' => 'Absent record'
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->get("/api/eschool/{$this->eschool->id}/attendance/export/csv?status=present");

        $response->assertStatus(200);
        
        $content = $response->getContent();
        $this->assertStringContainsString('Present record', $content);
        $this->assertStringNotContainsString('Absent record', $content);
    }

    public function test_export_csv_with_search_filter()
    {
        // This test assumes user profiles are set up correctly
        // For now, we'll just test that the endpoint accepts the search parameter
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->get("/api/eschool/{$this->eschool->id}/attendance/export/csv?search=test");

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
    }

    public function test_export_csv_includes_proof_document_urls()
    {
        // Create attendance record with proof document
        $file = UploadedFile::fake()->image('proof.jpg');
        $filePath = 'attendance/proofs/test_proof.jpg';
        Storage::disk('public')->put($filePath, $file->getContent());

        AttendanceRecord::factory()->create([
            'user_eschool_role_id' => $this->userEschoolRole->id,
            'date' => now()->subDays(1),
            'status' => 'absent',
            'notes' => 'With proof document',
            'proof_document' => $filePath
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->get("/api/eschool/{$this->eschool->id}/attendance/export/csv");

        $response->assertStatus(200);
        
        $content = $response->getContent();
        $this->assertStringContainsString('storage/attendance/proofs/test_proof.jpg', $content);
    }

    public function test_export_csv_validates_date_range()
    {
        $startDate = now()->format('Y-m-d');
        $endDate = now()->subDays(1)->format('Y-m-d'); // End date before start date

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->get("/api/eschool/{$this->eschool->id}/attendance/export/csv?date_from={$startDate}&date_to={$endDate}");

        $response->assertStatus(422)
                ->assertJson([
                    'success' => false,
                    'message' => 'Validation failed.'
                ]);
    }

    public function test_export_csv_validates_status_filter()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->get("/api/eschool/{$this->eschool->id}/attendance/export/csv?status=invalid_status");

        $response->assertStatus(422)
                ->assertJson([
                    'success' => false,
                    'message' => 'Validation failed.'
                ]);
    }

    public function test_export_csv_unauthorized_access_returns_403()
    {
        // Create another eschool that user doesn't have access to
        $otherEschool = Eschool::factory()->create();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->get("/api/eschool/{$otherEschool->id}/attendance/export/csv");

        $response->assertStatus(403)
                ->assertJson([
                    'success' => false,
                    'message' => 'You do not have permission to export attendance records for this eschool.'
                ]);
    }

    public function test_export_csv_filename_includes_filters()
    {
        $dateFrom = now()->subDays(5)->format('Y-m-d');
        $dateTo = now()->format('Y-m-d');

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->get("/api/eschool/{$this->eschool->id}/attendance/export/csv?date_from={$dateFrom}&date_to={$dateTo}");

        $response->assertStatus(200);
        
        $contentDisposition = $response->headers->get('Content-Disposition');
        $this->assertStringContainsString($dateFrom, $contentDisposition);
        $this->assertStringContainsString($dateTo, $contentDisposition);
        $this->assertStringContainsString('.csv', $contentDisposition);
    }
}