<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AttendanceRecord;
use App\Models\UserEschoolRole;
use App\Models\Eschool;
use App\Services\FileUploadService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    protected $fileUploadService;

    /**
     * Create a new controller instance.
     */
    public function __construct(FileUploadService $fileUploadService)
    {
        $this->middleware('auth:api');
        $this->fileUploadService = $fileUploadService;
    }
    /**
     * Display a listing of attendance records for a specific eschool
     *
     * @param int $eschoolId
     * @param Request $request
     * @return JsonResponse
     */
    public function index($eschoolId, Request $request): JsonResponse
    {
        try {
            // Check if eschool exists
            $eschool = Eschool::find($eschoolId);
            if (!$eschool) {
                return response()->json([
                    'success' => false,
                    'message' => 'Eschool not found.'
                ], 404);
            }

            // Check if user has access to this eschool
            $user = Auth::user();
            $userRole = UserEschoolRole::where('user_id', $user->id)
                ->where('eschool_id', $eschoolId)
                ->first();

            // Check if user is staff (supervisor) for this school
            $isStaff = false;
            if (!$userRole) {
                $staffRole = UserEschoolRole::where('user_id', $user->id)
                    ->where('role', 'supervisor')
                    ->whereHas('eschool', function ($query) use ($eschoolId) {
                        $query->where('school_id', Eschool::find($eschoolId)->school_id);
                    })
                    ->first();
                
                $isStaff = !!$staffRole;
            }

            // If user has no role in this eschool and is not staff, deny access
            if (!$userRole && !$isStaff) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to view attendance records for this eschool.'
                ], 403);
            }

            // Get filter parameters
            $search = $request->get('search');
            $date = $request->get('date');
            $dateFrom = $request->get('date_from');
            $dateTo = $request->get('date_to');
            $status = $request->get('status');
            $page = $request->get('page', 1);
            $perPage = $request->get('per_page', 10);

            // Build query for attendance records
            $query = AttendanceRecord::whereHas('userEschoolRole', function ($q) use ($eschoolId) {
                $q->where('eschool_id', $eschoolId);
            })->with([
                'userEschoolRole.user.profile',
                'userEschoolRole.user.student',
                'recorder.profile'
            ]);

            // Apply search filter (member name or student ID)
            if ($search) {
                $query->whereHas('userEschoolRole.user', function ($q) use ($search) {
                    $q->whereHas('profile', function ($profileQuery) use ($search) {
                        $profileQuery->where('name', 'LIKE', "%{$search}%");
                    })->orWhereHas('student', function ($studentQuery) use ($search) {
                        $studentQuery->where('student_id', 'LIKE', "%{$search}%");
                    });
                });
            }

            // Apply date filter
            if ($date) {
                $query->whereDate('date', $date);
            }

            // Apply date range filter
            if ($dateFrom) {
                $query->where('date', '>=', $dateFrom);
            }

            if ($dateTo) {
                $query->where('date', '<=', $dateTo);
            }

            // Apply status filter (convert status to is_present boolean)
            if ($status) {
                if ($status === 'present') {
                    $query->where('is_present', true);
                } elseif ($status === 'absent') {
                    $query->where('is_present', false);
                }
                // Note: 'late' status is not supported with boolean field
            }

            // Get records with pagination
            $attendanceRecords = $query->orderBy('date', 'desc')
                ->orderBy('created_at', 'desc')
                ->paginate($perPage, ['*'], 'page', $page);

            // Transform the data to match frontend expectations
            $transformedData = $attendanceRecords->getCollection()->map(function ($record) {
                return $this->transformAttendanceRecord($record);
            });

            return response()->json([
                'success' => true,
                'message' => 'Attendance records retrieved successfully.',
                'data' => $transformedData,
                'meta' => [
                    'total' => $attendanceRecords->total(),
                    'per_page' => $attendanceRecords->perPage(),
                    'current_page' => $attendanceRecords->currentPage(),
                    'last_page' => $attendanceRecords->lastPage(),
                    'from' => $attendanceRecords->firstItem(),
                    'to' => $attendanceRecords->lastItem(),
                    'has_next_page' => $attendanceRecords->hasMorePages(),
                    'has_prev_page' => $attendanceRecords->currentPage() > 1
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve attendance records.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified attendance record
     *
     * @param int $eschoolId
     * @param int $id
     * @return JsonResponse
     */
    public function show($eschoolId, $id): JsonResponse
    {
        try {
            // Check if eschool exists
            $eschool = Eschool::find($eschoolId);
            if (!$eschool) {
                return response()->json([
                    'success' => false,
                    'message' => 'Eschool not found.'
                ], 404);
            }

            // Find the attendance record
            $attendanceRecord = AttendanceRecord::with([
                'userEschoolRole.user.profile',
                'userEschoolRole.user.student',
                'recorder.profile'
            ])->find($id);

            if (!$attendanceRecord) {
                return response()->json([
                    'success' => false,
                    'message' => 'Attendance record not found.'
                ], 404);
            }

            // Verify the record belongs to the specified eschool
            if ($attendanceRecord->userEschoolRole->eschool_id != $eschoolId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Attendance record does not belong to this eschool.'
                ], 403);
            }

            // Check if user has access to this eschool
            $user = Auth::user();
            $userRole = UserEschoolRole::where('user_id', $user->id)
                ->where('eschool_id', $eschoolId)
                ->first();

            // Check if user is staff (supervisor) for this school
            $isStaff = false;
            if (!$userRole) {
                $staffRole = UserEschoolRole::where('user_id', $user->id)
                    ->where('role', 'supervisor')
                    ->whereHas('eschool', function ($query) use ($eschoolId) {
                        $query->where('school_id', Eschool::find($eschoolId)->school_id);
                    })
                    ->first();
                
                $isStaff = !!$staffRole;
            }

            // If user has no role in this eschool and is not staff, deny access
            if (!$userRole && !$isStaff) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to view this attendance record.'
                ], 403);
            }

            // Transform the data to match frontend expectations
            $transformedData = $this->transformAttendanceRecord($attendanceRecord);

            return response()->json([
                'success' => true,
                'message' => 'Attendance record retrieved successfully.',
                'data' => $transformedData
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve attendance record.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Transform attendance record to match frontend expectations
     *
     * @param AttendanceRecord $record
     * @return array
     */
    private function transformAttendanceRecord(AttendanceRecord $record): array
    {
        $user = $record->userEschoolRole->user;
        $profile = $user->profile;
        $student = $user->student;

        // Get recorder information
        $recorder = $record->recorder;
        $recorderProfile = $recorder ? $recorder->profile : null;

        return [
            'id' => $record->id,
            'date' => $record->date->format('Y-m-d'),
            'member' => [
                'user_id' => $user->id,
                'name' => $profile ? $profile->name : 'Unknown',
                'student_id' => $student ? $student->student_id : 'N/A'
            ],
            'recorder' => [
                'user_id' => $recorder ? $recorder->id : null,
                'name' => $recorderProfile ? $recorderProfile->name : 'Unknown'
            ],
            'is_present' => $record->is_present,
            'status' => $record->is_present ? 'present' : 'absent',
            'notes' => $record->notes,
            'proof_document' => $record->proof_document_url,
            'created_at' => $record->created_at->toISOString(),
            'updated_at' => $record->updated_at->toISOString()
        ];
    }

    /**
     * Store multiple attendance records in a single request
     *
     * @param Request $request
     * @param int $eschoolId
     * @return JsonResponse
     */
    public function store(Request $request, $eschoolId): JsonResponse
    {
        try {
            // Validate eschool access
            if (!$this->validateEschoolAccess(Auth::id(), $eschoolId)) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to create attendance records for this eschool.'
                ], 403);
            }

            // Validate request structure for multi-member attendance
            $validator = Validator::make($request->all(), [
                'date' => 'required|date|before_or_equal:today',
                'members' => 'required|array|min:1',
                'members.*.member_id' => 'required|exists:users,id',
                'members.*.is_present' => 'required|in:true,false,1,0', // Handle FormData boolean conversion
                'members.*.notes' => 'nullable|string|max:500',
                'members.*.proof_document' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120' // 5MB max
            ]);

            // Custom validation for proof document requirement (optional for backward compatibility)
            $validator->after(function ($validator) use ($request) {
                $members = $request->input('members', []);
                foreach ($members as $index => $member) {
                    // Handle FormData boolean conversion
                    $isPresent = filter_var($member['is_present'] ?? true, FILTER_VALIDATE_BOOLEAN);
                    $hasProofDocument = $request->hasFile("members.{$index}.proof_document");
                    
                    // Only validate proof document if it's explicitly provided or if strict validation is enabled
                    // For now, we'll make it optional to maintain backward compatibility
                    // TODO: In future versions, make this required for absent members
                    if (!$isPresent && $hasProofDocument) {
                        // If proof document is provided, validate it
                        // Validation rules already handle file validation
                    }
                }
            });

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed.',
                    'errors' => $validator->errors()
                ], 422);
            }

            $date = $request->input('date');
            $members = $request->input('members');
            $createdRecords = [];
            $uploadedFiles = []; // Track uploaded files for cleanup on error

            // Validate for duplicate attendance records before processing
            $duplicateRecords = [];
            foreach ($members as $index => $memberData) {
                $memberId = $memberData['member_id'];

                // Map member_id (user_id) to user_eschool_role_id
                $userEschoolRole = UserEschoolRole::where('user_id', $memberId)
                    ->where('eschool_id', $eschoolId)
                    ->first();

                if (!$userEschoolRole) {
                    throw new \Exception("Member with ID {$memberId} is not associated with this eschool.");
                }

                // Check for duplicate attendance record
                $existingRecord = AttendanceRecord::where('user_eschool_role_id', $userEschoolRole->id)
                    ->where('date', $date)
                    ->first();

                if ($existingRecord) {
                    // Get member name for error message
                    $memberName = $userEschoolRole->user->profile->name ?? 'Unknown Member';
                    
                    $duplicateRecords[] = [
                        'member_id' => $memberId,
                        'member_name' => $memberName,
                        'date' => $date
                    ];
                }
            }

            // If there are duplicate records, return error
            if (!empty($duplicateRecords)) {
                $errorMessages = [];
                foreach ($duplicateRecords as $dup) {
                    $errorMessages[] = "Kehadiran untuk {$dup['member_name']} pada tanggal {$dup['date']} sudah tercatat";
                }

                return response()->json([
                    'success' => false,
                    'message' => implode('. ', $errorMessages),
                    'errors' => [
                        'duplicate_records' => $duplicateRecords
                    ]
                ], 422);
            }

            // Use database transaction for multi-record creation
            \DB::transaction(function () use ($request, $eschoolId, $date, $members, &$createdRecords, &$uploadedFiles) {
                foreach ($members as $index => $memberData) {
                    $memberId = $memberData['member_id'];
                    // Handle FormData boolean conversion (string "1"/"0" to boolean)
                    $isPresent = filter_var($memberData['is_present'], FILTER_VALIDATE_BOOLEAN);
                    $notes = $memberData['notes'] ?? null;

                    // Map member_id (user_id) to user_eschool_role_id
                    $userEschoolRole = UserEschoolRole::where('user_id', $memberId)
                        ->where('eschool_id', $eschoolId)
                        ->first();

                    // Use is_present boolean field directly

                    // Handle proof document upload if member is absent
                    $proofDocumentPath = null;
                    if (!$isPresent && $request->hasFile("members.{$index}.proof_document")) {
                        $file = $request->file("members.{$index}.proof_document");
                        try {
                            $proofDocumentPath = $this->fileUploadService->uploadProofDocument($file, $memberId, $date);
                            $uploadedFiles[] = $proofDocumentPath; // Track for cleanup
                        } catch (\Exception $e) {
                            throw new \Exception("Failed to upload proof document for member {$memberId}: " . $e->getMessage());
                        }
                    }

                    // Create attendance record
                    $attendanceRecord = AttendanceRecord::create([
                        'user_eschool_role_id' => $userEschoolRole->id,
                        'date' => $date,
                        'recorder_id' => Auth::id(),
                        'is_present' => $isPresent,
                        'notes' => $notes,
                        'proof_document' => $proofDocumentPath
                    ]);

                    // Load relationships for response
                    $attendanceRecord->load([
                        'userEschoolRole.user.profile',
                        'userEschoolRole.user.student'
                    ]);

                    $createdRecords[] = $attendanceRecord;
                }
            });

            // Transform all created records for response
            $transformedRecords = collect($createdRecords)->map(function ($record) {
                return $this->transformAttendanceRecord($record);
            });

            return response()->json([
                'success' => true,
                'message' => count($createdRecords) . ' attendance record(s) created successfully.',
                'data' => $transformedRecords
            ], 201);

        } catch (\Exception $e) {
            // Clean up uploaded files if transaction failed
            if (isset($uploadedFiles)) {
                foreach ($uploadedFiles as $filePath) {
                    $this->fileUploadService->deleteProofDocument($filePath);
                }
            }

            return response()->json([
                'success' => false,
                'message' => 'Failed to create attendance records.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified attendance record
     *
     * @param Request $request
     * @param int $eschoolId
     * @param int $id
     * @return JsonResponse
     */
    public function update(Request $request, $eschoolId, $id): JsonResponse
    {
        try {
            // Validate eschool access
            if (!$this->validateEschoolAccess(Auth::id(), $eschoolId)) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to update attendance records for this eschool.'
                ], 403);
            }

            // Find the attendance record
            $attendanceRecord = AttendanceRecord::with([
                'userEschoolRole.user.profile',
                'userEschoolRole.user.student'
            ])->find($id);

            if (!$attendanceRecord) {
                return response()->json([
                    'success' => false,
                    'message' => 'Attendance record not found.'
                ], 404);
            }

            // Verify the record belongs to the specified eschool
            if ($attendanceRecord->userEschoolRole->eschool_id != $eschoolId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Attendance record does not belong to this eschool.'
                ], 403);
            }

            // Validation with file upload support
            $validator = Validator::make($request->all(), [
                'date' => 'sometimes|date|before_or_equal:today',
                'is_present' => 'sometimes|boolean',
                'notes' => 'nullable|string|max:500',
                'proof_document' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120' // 5MB max
            ]);

            // Custom validation for proof document requirement
            $validator->after(function ($validator) use ($request, $attendanceRecord) {
                $newIsPresent = $request->input('is_present', $attendanceRecord->is_present);
                $hasProofDocument = $request->hasFile('proof_document');
                $existingProofDocument = $attendanceRecord->proof_document;
                
                // If is_present is changing to false (absent) and no proof document exists or is being uploaded
                if (!$newIsPresent && !$hasProofDocument && !$existingProofDocument) {
                    $validator->errors()->add(
                        'proof_document',
                        'Proof document is required when member is absent.'
                    );
                }
            });

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed.',
                    'errors' => $validator->errors()
                ], 422);
            }

            $oldProofDocument = $attendanceRecord->proof_document;
            $newProofDocumentPath = $oldProofDocument;

            // Handle proof document upload
            if ($request->hasFile('proof_document')) {
                try {
                    $file = $request->file('proof_document');
                    $userId = $attendanceRecord->userEschoolRole->user_id;
                    $date = $request->input('date', $attendanceRecord->date->format('Y-m-d'));
                    
                    // Upload new file
                    $newProofDocumentPath = $this->fileUploadService->uploadProofDocument($file, $userId, $date);
                    
                    // Delete old file if it exists
                    if ($oldProofDocument) {
                        $this->fileUploadService->deleteProofDocument($oldProofDocument);
                    }
                } catch (\Exception $e) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Failed to upload proof document: ' . $e->getMessage()
                    ], 500);
                }
            }

            // If is_present is changing from false to true (absent to present), remove proof document requirement
            $newIsPresent = $request->input('is_present');
            if ($newIsPresent === true && $oldProofDocument) {
                // Delete the proof document file
                $this->fileUploadService->deleteProofDocument($oldProofDocument);
                $newProofDocumentPath = null;
            }

            // Update attendance record
            $updateData = $request->only(['date', 'is_present', 'notes']);
            $updateData['proof_document'] = $newProofDocumentPath;

            $attendanceRecord->update($updateData);

            return response()->json([
                'success' => true,
                'message' => 'Attendance record updated successfully.',
                'data' => $this->transformAttendanceRecord($attendanceRecord)
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update attendance record.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified attendance record
     *
     * @param int $eschoolId
     * @param int $id
     * @return JsonResponse
     */
    public function destroy($eschoolId, $id): JsonResponse
    {
        try {
            // Validate eschool access
            if (!$this->validateEschoolAccess(Auth::id(), $eschoolId)) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to delete attendance records for this eschool.'
                ], 403);
            }

            // Find the attendance record
            $attendanceRecord = AttendanceRecord::find($id);

            if (!$attendanceRecord) {
                return response()->json([
                    'success' => false,
                    'message' => 'Attendance record not found.'
                ], 404);
            }

            // Verify the record belongs to the specified eschool
            if ($attendanceRecord->userEschoolRole->eschool_id != $eschoolId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Attendance record does not belong to this eschool.'
                ], 403);
            }

            // Delete associated proof document file if it exists
            if ($attendanceRecord->proof_document) {
                $this->fileUploadService->deleteProofDocument($attendanceRecord->proof_document);
            }

            // Delete the record
            $attendanceRecord->delete();

            return response()->json([
                'success' => true,
                'message' => 'Attendance record deleted successfully.'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete attendance record.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get attendance statistics for dashboard
     *
     * @param int $eschoolId
     * @return JsonResponse
     */
    public function statistics($eschoolId): JsonResponse
    {
        try {
            // Validate eschool access
            if (!$this->validateEschoolAccess(Auth::id(), $eschoolId)) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to view statistics for this eschool.'
                ], 403);
            }

            // Check if eschool exists
            $eschool = Eschool::find($eschoolId);
            if (!$eschool) {
                return response()->json([
                    'success' => false,
                    'message' => 'Eschool not found.'
                ], 404);
            }

            // Cache key for statistics
            $cacheKey = "attendance_statistics_{$eschoolId}";
            
            // Get cached statistics or calculate new ones
            $statistics = Cache::remember($cacheKey, 300, function () use ($eschoolId) { // Cache for 5 minutes
                return $this->calculateStatistics($eschoolId);
            });

            return response()->json([
                'success' => true,
                'message' => 'Statistics retrieved successfully.',
                'data' => $statistics
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve statistics.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get attendance analytics with period filtering
     *
     * @param Request $request
     * @param int $eschoolId
     * @return JsonResponse
     */
    public function analytics(Request $request, $eschoolId): JsonResponse
    {
        try {
            // Validate eschool access
            if (!$this->validateEschoolAccess(Auth::id(), $eschoolId)) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to view analytics for this eschool.'
                ], 403);
            }

            // Check if eschool exists
            $eschool = Eschool::find($eschoolId);
            if (!$eschool) {
                return response()->json([
                    'success' => false,
                    'message' => 'Eschool not found.'
                ], 404);
            }

            // Validate request parameters
            $validator = Validator::make($request->all(), [
                'period' => 'required|in:week,month,year',
                'start_date' => 'nullable|date',
                'end_date' => 'nullable|date|after_or_equal:start_date'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed.',
                    'errors' => $validator->errors()
                ], 422);
            }

            $period = $request->input('period');
            $startDate = $request->input('start_date');
            $endDate = $request->input('end_date');

            // Cache key for analytics
            $cacheKey = "attendance_analytics_{$eschoolId}_{$period}_" . md5($startDate . $endDate);
            
            // Get cached analytics or calculate new ones
            $analytics = Cache::remember($cacheKey, 600, function () use ($eschoolId, $period, $startDate, $endDate) { // Cache for 10 minutes
                return $this->calculateAnalytics($eschoolId, $period, $startDate, $endDate);
            });

            return response()->json([
                'success' => true,
                'message' => 'Analytics retrieved successfully.',
                'data' => $analytics
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve analytics.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Calculate attendance statistics for dashboard
     *
     * @param int $eschoolId
     * @return array
     */
    private function calculateStatistics($eschoolId): array
    {
        $baseQuery = AttendanceRecord::whereHas('userEschoolRole', function ($q) use ($eschoolId) {
            $q->where('eschool_id', $eschoolId);
        });

        // Overall statistics
        $totalRecords = (clone $baseQuery)->count();
        $totalPresent = (clone $baseQuery)->where('is_present', true)->count();
        $totalAbsent = (clone $baseQuery)->where('is_present', false)->count();
        $totalLate = 0; // Late status not supported with boolean field
        
        $attendanceRate = $totalRecords > 0 ? round(($totalPresent / $totalRecords) * 100, 1) : 0;

        // This week statistics
        $weekStart = Carbon::now()->startOfWeek();
        $weekEnd = Carbon::now()->endOfWeek();
        
        $weekQuery = (clone $baseQuery)->whereBetween('date', [$weekStart, $weekEnd]);
        $weekTotal = $weekQuery->count();
        $weekPresent = $weekQuery->where('is_present', true)->count();
        $weekAbsent = $weekQuery->where('is_present', false)->count();
        $weekRate = $weekTotal > 0 ? round(($weekPresent / $weekTotal) * 100, 1) : 0;

        // This month statistics
        $monthStart = Carbon::now()->startOfMonth();
        $monthEnd = Carbon::now()->endOfMonth();
        
        $monthQuery = (clone $baseQuery)->whereBetween('date', [$monthStart, $monthEnd]);
        $monthTotal = $monthQuery->count();
        $monthPresent = $monthQuery->where('is_present', true)->count();
        $monthAbsent = $monthQuery->where('is_present', false)->count();
        $monthRate = $monthTotal > 0 ? round(($monthPresent / $monthTotal) * 100, 1) : 0;

        return [
            'total_records' => $totalRecords,
            'total_present' => $totalPresent,
            'total_absent' => $totalAbsent,
            'total_late' => $totalLate,
            'attendance_rate' => $attendanceRate,
            'this_week' => [
                'total' => $weekTotal,
                'present' => $weekPresent,
                'absent' => $weekAbsent,
                'rate' => $weekRate
            ],
            'this_month' => [
                'total' => $monthTotal,
                'present' => $monthPresent,
                'absent' => $monthAbsent,
                'rate' => $monthRate
            ]
        ];
    }

    /**
     * Calculate attendance analytics with period filtering
     *
     * @param int $eschoolId
     * @param string $period
     * @param string|null $startDate
     * @param string|null $endDate
     * @return array
     */
    private function calculateAnalytics($eschoolId, $period, $startDate = null, $endDate = null): array
    {
        // Determine date range based on period
        $dateRange = $this->getDateRangeForPeriod($period, $startDate, $endDate);
        
        $baseQuery = AttendanceRecord::whereHas('userEschoolRole', function ($q) use ($eschoolId) {
            $q->where('eschool_id', $eschoolId);
        })->whereBetween('date', [$dateRange['start'], $dateRange['end']]);

        // Generate chart data based on period
        $chartData = $this->generateChartData($baseQuery, $period, $dateRange);
        
        // Calculate trends
        $trends = $this->calculateTrends($baseQuery, $period, $dateRange);

        return [
            'period' => $period,
            'date_range' => [
                'start' => $dateRange['start']->format('Y-m-d'),
                'end' => $dateRange['end']->format('Y-m-d')
            ],
            'chart_data' => $chartData,
            'trends' => $trends
        ];
    }

    /**
     * Get date range for the specified period
     *
     * @param string $period
     * @param string|null $startDate
     * @param string|null $endDate
     * @return array
     */
    private function getDateRangeForPeriod($period, $startDate = null, $endDate = null): array
    {
        if ($startDate && $endDate) {
            return [
                'start' => Carbon::parse($startDate),
                'end' => Carbon::parse($endDate)
            ];
        }

        $now = Carbon::now();
        
        switch ($period) {
            case 'week':
                return [
                    'start' => $now->copy()->startOfWeek(),
                    'end' => $now->copy()->endOfWeek()
                ];
            case 'month':
                return [
                    'start' => $now->copy()->startOfMonth(),
                    'end' => $now->copy()->endOfMonth()
                ];
            case 'year':
                return [
                    'start' => $now->copy()->startOfYear(),
                    'end' => $now->copy()->endOfYear()
                ];
            default:
                return [
                    'start' => $now->copy()->startOfMonth(),
                    'end' => $now->copy()->endOfMonth()
                ];
        }
    }

    /**
     * Generate chart data for analytics
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $period
     * @param array $dateRange
     * @return array
     */
    private function generateChartData($query, $period, $dateRange): array
    {
        $chartData = [];
        
        // Group data by appropriate time unit
        $groupBy = $this->getGroupByFormat($period);
        
        $data = $query->select(
            DB::raw("DATE({$groupBy}) as period_date"),
            DB::raw('COUNT(*) as total'),
            DB::raw("COUNT(CASE WHEN is_present = 1 THEN 1 END) as present"),
            DB::raw("COUNT(CASE WHEN is_present = 0 THEN 1 END) as absent"),
            DB::raw("0 as late") // Late status not supported with boolean field
        )
        ->groupBy(DB::raw("DATE({$groupBy})"))
        ->orderBy(DB::raw("DATE({$groupBy})"))
        ->get();

        foreach ($data as $item) {
            $total = $item->total;
            $present = $item->present;
            $absent = $item->absent;
            $late = $item->late;
            $rate = $total > 0 ? round(($present / $total) * 100, 1) : 0;

            $chartData[] = [
                'date' => $item->period_date,
                'total' => $total,
                'present' => $present,
                'absent' => $absent,
                'late' => $late,
                'rate' => $rate
            ];
        }

        return $chartData;
    }

    /**
     * Calculate trends for analytics
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $period
     * @param array $dateRange
     * @return array
     */
    private function calculateTrends($query, $period, $dateRange): array
    {
        $totalRecords = $query->count();
        $totalPresent = $query->where('is_present', true)->count();
        $averageRate = $totalRecords > 0 ? round(($totalPresent / $totalRecords) * 100, 1) : 0;

        // Calculate daily attendance rates to find best and worst days
        $dailyRates = $query->select(
            DB::raw('DAYNAME(date) as day_name'),
            DB::raw('COUNT(*) as total'),
            DB::raw("COUNT(CASE WHEN is_present = 1 THEN 1 END) as present")
        )
        ->groupBy(DB::raw('DAYNAME(date)'), DB::raw('DAYOFWEEK(date)'))
        ->orderBy(DB::raw('DAYOFWEEK(date)'))
        ->get()
        ->map(function ($item) {
            $rate = $item->total > 0 ? round(($item->present / $item->total) * 100, 1) : 0;
            return [
                'day' => $item->day_name,
                'rate' => $rate,
                'total' => $item->total,
                'present' => $item->present
            ];
        });

        $bestDay = $dailyRates->sortByDesc('rate')->first();
        $worstDay = $dailyRates->sortBy('rate')->first();

        // Calculate trend direction (comparing first half vs second half of period)
        $midPoint = $dateRange['start']->copy()->addDays(
            $dateRange['start']->diffInDays($dateRange['end']) / 2
        );

        $firstHalfRate = $this->calculatePeriodRate($query, $dateRange['start'], $midPoint);
        $secondHalfRate = $this->calculatePeriodRate($query, $midPoint, $dateRange['end']);

        $trendDirection = 'stable';
        if ($secondHalfRate > $firstHalfRate + 5) {
            $trendDirection = 'increasing';
        } elseif ($secondHalfRate < $firstHalfRate - 5) {
            $trendDirection = 'decreasing';
        }

        return [
            'attendance_trend' => $trendDirection,
            'average_rate' => $averageRate,
            'best_day' => $bestDay ? $bestDay['day'] : 'N/A',
            'worst_day' => $worstDay ? $worstDay['day'] : 'N/A',
            'daily_breakdown' => $dailyRates->toArray(),
            'period_comparison' => [
                'first_half_rate' => $firstHalfRate,
                'second_half_rate' => $secondHalfRate,
                'change' => round($secondHalfRate - $firstHalfRate, 1)
            ]
        ];
    }

    /**
     * Get the appropriate GROUP BY format for the period
     *
     * @param string $period
     * @return string
     */
    private function getGroupByFormat($period): string
    {
        switch ($period) {
            case 'week':
            case 'month':
                return 'date'; // Group by day
            case 'year':
                return 'DATE_FORMAT(date, "%Y-%m")'; // Group by month
            default:
                return 'date';
        }
    }

    /**
     * Calculate attendance rate for a specific period
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @return float
     */
    private function calculatePeriodRate($query, $startDate, $endDate): float
    {
        $periodQuery = $query->clone()->whereBetween('date', [$startDate, $endDate]);
        $total = $periodQuery->count();
        $present = $periodQuery->where('is_present', true)->count();
        
        return $total > 0 ? round(($present / $total) * 100, 1) : 0;
    }

    /**
     * Export attendance records to CSV
     *
     * @param Request $request
     * @param int $eschoolId
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function exportCsv(Request $request, $eschoolId)
    {
        try {
            // Validate eschool access
            if (!$this->validateEschoolAccess(Auth::id(), $eschoolId)) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to export attendance records for this eschool.'
                ], 403);
            }

            // Check if eschool exists
            $eschool = Eschool::find($eschoolId);
            if (!$eschool) {
                return response()->json([
                    'success' => false,
                    'message' => 'Eschool not found.'
                ], 404);
            }

            // Validate request parameters
            $validator = Validator::make($request->all(), [
                'date_from' => 'nullable|date',
                'date_to' => 'nullable|date|after_or_equal:date_from',
                'status' => 'nullable|in:present,absent',
                'search' => 'nullable|string|max:255'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed.',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Get filter parameters
            $dateFrom = $request->get('date_from');
            $dateTo = $request->get('date_to');
            $status = $request->get('status');
            $search = $request->get('search');

            // Generate filename with timestamp and filters
            $filename = 'attendance_records_' . $eschool->name . '_' . date('Y-m-d_H-i-s');
            if ($dateFrom && $dateTo) {
                $filename .= '_' . $dateFrom . '_to_' . $dateTo;
            } elseif ($dateFrom) {
                $filename .= '_from_' . $dateFrom;
            } elseif ($dateTo) {
                $filename .= '_until_' . $dateTo;
            }
            $filename .= '.csv';

            // Create streamed response for memory efficiency with large datasets
            return response()->stream(function () use ($eschoolId, $dateFrom, $dateTo, $status, $search) {
                // Open output stream
                $handle = fopen('php://output', 'w');

                // Add BOM for proper UTF-8 encoding in Excel
                fwrite($handle, "\xEF\xBB\xBF");

                // Write CSV headers
                fputcsv($handle, [
                    'Date',
                    'Member Name',
                    'Student ID',
                    'Status',
                    'Notes',
                    'Proof Document URL',
                    'Created At',
                    'Updated At'
                ]);

                // Build query for attendance records
                $query = AttendanceRecord::whereHas('userEschoolRole', function ($q) use ($eschoolId) {
                    $q->where('eschool_id', $eschoolId);
                })->with([
                    'userEschoolRole.user.profile',
                    'userEschoolRole.user.student'
                ]);

                // Apply date range filter
                if ($dateFrom) {
                    $query->where('date', '>=', $dateFrom);
                }

                if ($dateTo) {
                    $query->where('date', '<=', $dateTo);
                }

                // Apply status filter
                if ($status) {
                    if ($status === 'present') {
                        $query->where('is_present', true);
                    } elseif ($status === 'absent') {
                        $query->where('is_present', false);
                    }
                }

                // Apply search filter (member name or student ID)
                if ($search) {
                    $query->whereHas('userEschoolRole.user', function ($q) use ($search) {
                        $q->whereHas('profile', function ($profileQuery) use ($search) {
                            $profileQuery->where('name', 'LIKE', "%{$search}%");
                        })->orWhereHas('student', function ($studentQuery) use ($search) {
                            $studentQuery->where('student_id', 'LIKE', "%{$search}%");
                        });
                    });
                }

                // Order by date and created_at for consistent export
                $query->orderBy('date', 'desc')->orderBy('created_at', 'desc');

                // Process records in chunks for memory efficiency
                $query->chunk(1000, function ($records) use ($handle) {
                    foreach ($records as $record) {
                        $user = $record->userEschoolRole->user;
                        $profile = $user->profile;
                        $student = $user->student;

                        // Generate proof document URL if exists
                        $proofDocumentUrl = '';
                        if ($record->proof_document) {
                            $proofDocumentUrl = asset('storage/' . $record->proof_document);
                        }

                        // Write record to CSV
                        fputcsv($handle, [
                            $record->date->format('Y-m-d'),
                            $profile ? $profile->name : 'Unknown',
                            $student ? $student->student_id : 'N/A',
                            $record->is_present ? 'Present' : 'Absent',
                            $record->notes ?? '',
                            $proofDocumentUrl,
                            $record->created_at->format('Y-m-d H:i:s'),
                            $record->updated_at->format('Y-m-d H:i:s')
                        ]);
                    }
                });

                // Close the file handle
                fclose($handle);
            }, 200, [
                'Content-Type' => 'text/csv; charset=UTF-8',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
                'Cache-Control' => 'no-cache, no-store, must-revalidate',
                'Pragma' => 'no-cache',
                'Expires' => '0'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to export attendance records.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Validate user access to eschool
     *
     * @param int $userId
     * @param int $eschoolId
     * @return bool
     */
    private function validateEschoolAccess($userId, $eschoolId): bool
    {
        // Check if user has role in this eschool
        $userRole = UserEschoolRole::where('user_id', $userId)
            ->where('eschool_id', $eschoolId)
            ->first();

        if ($userRole) {
            return true;
        }

        // Check if user is staff (supervisor) for this school
        $staffRole = UserEschoolRole::where('user_id', $userId)
            ->where('role', 'supervisor')
            ->whereHas('eschool', function ($query) use ($eschoolId) {
                $query->where('school_id', Eschool::find($eschoolId)->school_id);
            })
            ->first();

        return !!$staffRole;
    }

    /**
     * Export attendance records as PDF
     *
     * @param Request $request
     * @param int $eschoolId
     * @return \Illuminate\Http\JsonResponse
     */
    public function exportPdf(Request $request, $eschoolId)
    {
        // For now, return an error as PDF export is not implemented
        // TODO: Implement PDF export using a library like DomPDF or TCPDF
        return response()->json([
            'success' => false,
            'message' => 'PDF export is not yet implemented. Please use CSV export instead.'
        ], 501);
    }
}