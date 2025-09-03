<?php

namespace App\Http\Controllers;

use App\Models\AttendanceRecord;
use App\Models\Member;
use App\Models\Eschool;
use App\Services\AttendanceService;
use App\Http\Requests\StoreAttendanceRequest;
use App\Http\Requests\UpdateAttendanceRequest;
use App\Http\Resources\AttendanceRecordResource;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;



class AttendanceController extends Controller
{
    protected AttendanceService $attendanceService;

    public function __construct(AttendanceService $attendanceService)
    {
        $this->attendanceService = $attendanceService;
        
        // Remove default middleware as we'll use specific middleware in routes
        // $this->middleware('role:koordinator');
    }

    /**
     * Display a listing of attendance records.
     */
    public function index(Request $request): JsonResponse
{
    try {
        $eschoolId = $request->input('eschool_id');
        $date = $request->input('date');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $search = $request->input('search');
        $memberId = $request->input('member_id');
        $isPresent = $request->input('is_present');

        if (!$eschoolId) {
            return response()->json([
                'success' => false,
                'message' => 'Eschool ID is required'
            ], 400);
        }

        // Build query
        $query = AttendanceRecord::with(['member.user', 'recorder'])
            ->byEschool($eschoolId);

        // Apply date filters
        if ($startDate && $endDate) {
            $query->byDateRange($startDate, $endDate);
        } elseif ($date) {
            $query->whereDate('date', $date);
        } else {
            // Default to today's records if no date filter specified
            $query->whereDate('date', now()->toDateString());
        }

        // Apply search filter
        if ($search) {
            $query->whereHas('member', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('student_id', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($q2) use ($search) {
                      $q2->where('email', 'like', "%{$search}%");
                  });
            })->orWhere('notes', 'like', "%{$search}%");
        }

        // Apply member filter
        if ($memberId) {
            $query->where('member_id', $memberId);
        }

        // Apply is_present filter
        if ($isPresent !== null) {
            $query->where('is_present', filter_var($isPresent, FILTER_VALIDATE_BOOLEAN));
        }

        // Get records with pagination
        $perPage = $request->input('per_page', 10); // Default 10 per page
        $records = $query->orderBy('date', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        return response()->json([
    'success' => true,
    'data' => AttendanceRecordResource::collection($records),
    'meta' => [
        'total' => $records->total(),
        'per_page' => $records->perPage(),
        'current_page' => $records->currentPage(),
        'last_page' => $records->lastPage(),
        'from' => $records->firstItem(),
        'to' => $records->lastItem(),
        'has_next_page' => $records->hasMorePages(),
        'has_prev_page' => $records->currentPage() > 1
    ],
    'message' => 'Attendance records retrieved successfully'
]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Failed to retrieve attendance records: ' . $e->getMessage()
        ], 500);
    }
}


    /**
     * Store batch attendance records.
     */
 public function store(StoreAttendanceRequest $request): JsonResponse
{
    try {
        $validatedData = $request->validated();
        $recorderId = auth()->id();

        // Log the incoming data for debugging
        \Log::info('Attendance recording request', [
            'eschool_id' => $validatedData['eschool_id'],
            'date' => $validatedData['date'] ?? null,
            'members_count' => count($validatedData['members'] ?? []),
            'members' => $validatedData['members'] ?? []
        ]);

        $records = $this->attendanceService->recordBatchAttendance(
            $validatedData['eschool_id'],
            $validatedData,
            $recorderId
        );

        return response()->json([
            'success' => true,
            'data' => $records->load(['member', 'recorder']),
            'message' => 'Attendance recorded successfully'
        ], 201);

    } catch (\Exception $e) {
        \Log::error('Attendance recording error', [
            'message' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        
        $decoded = json_decode($e->getMessage(), true);

        return response()->json([
            'success' => false,
            'messages' => is_array($decoded) ? $decoded : [$e->getMessage()]
        ], 400);
    }
}


 

    /**
     * Display the specified attendance record.
     */
    public function show(AttendanceRecord $attendance): JsonResponse
    {
        try {
            $attendance->load(['member', 'recorder', 'eschool']);

            return response()->json([
                'success' => true,
                'data' => $attendance,
                'message' => 'Attendance record retrieved successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve attendance record: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified attendance record.
     */
    public function update(UpdateAttendanceRequest $request, AttendanceRecord $attendance): JsonResponse
    {
        try {
            $validatedData = $request->validated();
            $validatedData['recorder_id'] = auth()->id();

            // Update attendance record using service
            $updatedRecord = $this->attendanceService->updateAttendance($attendance, $validatedData);
            $updatedRecord->load(['member', 'recorder']);

            return response()->json([
                'success' => true,
                'data' => $updatedRecord,
                'message' => 'Attendance record updated successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update attendance record: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified attendance record.
     */
    public function destroy($eschoolId, $attendance): JsonResponse
    {
        try {
            // Handle both explicit ID and model binding
            if (!$attendance instanceof AttendanceRecord) {
                $attendance = AttendanceRecord::findOrFail($attendance);
            }
            
            // Verify the attendance record belongs to the specified eschool
            if ($attendance->eschool_id != $eschoolId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Attendance record not found in specified eschool'
                ], 404);
            }
            
            // Delete associated proof document if exists
            if ($attendance->proof_document_path) {
                \Storage::disk('public')->delete($attendance->proof_document_path);
            }
            
            $attendance->delete();

            return response()->json([
                'success' => true,
                'message' => 'Attendance record deleted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete attendance record: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Remove the specified attendance record by ID.
     */
    public function destroyRecord($eschoolId, $recordId): JsonResponse
    {
        try {
            // Find the attendance record by ID
            $attendance = AttendanceRecord::findOrFail($recordId);
            
            // Verify the attendance record belongs to the specified eschool
            if ($attendance->eschool_id != $eschoolId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Attendance record not found in specified eschool'
                ], 404);
            }
            
            // Delete associated proof document if exists
            if ($attendance->proof_document_path) {
                \Storage::disk('public')->delete($attendance->proof_document_path);
            }
            
            $attendance->delete();

            return response()->json([
                'success' => true,
                'message' => 'Attendance record deleted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete attendance record: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get comprehensive attendance analytics.
     */
    public function analytics(Request $request): JsonResponse
    {
        try {
            $eschoolId = $request->input('eschool_id');
            $period = $request->input('period', 'week'); // week, month, semester
            
            if (!$eschoolId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Eschool ID is required'
                ], 400);
            }
            
            // Get eschool
            $eschool = Eschool::findOrFail($eschoolId);
            
            // Set date range based on period
            $endDate = now();
            switch ($period) {
                case 'month':
                    $startDate = now()->subMonth();
                    break;
                case 'semester':
                    $startDate = now()->subMonths(6);
                    break;
                case 'week':
                default:
                    $startDate = now()->subWeek();
                    break;
            }
            
            // Get total members in eschool
            $totalMembers = $eschool->members()->count();
            
            // Get daily attendance summary
            $dailySummary = $this->attendanceService->getDailyAttendanceSummary(
                $eschoolId, 
                $startDate->toDateString(), 
                $endDate->toDateString()
            );
            
            // Create complete date range
            $dateRange = [];
            $currentDate = clone $startDate;
            while ($currentDate->lte($endDate)) {
                $dateRange[] = $currentDate->copy();
                $currentDate->addDay();
            }
            
            // Enhance daily summary with missing dates
            $enhancedDailySummary = [];
            foreach ($dateRange as $date) {
                $dateString = $date->toDateString();
                $summary = $dailySummary->firstWhere('attendance_date', $dateString);
                
                $presentCount = $summary ? (int)$summary->present_count : 0;
                $totalCount = $summary ? (int)$summary->total_records : 0;
                $absentCount = $totalCount - $presentCount;
                
                // If no records exist for this date, assume all members are absent
                if ($totalCount == 0 && $totalMembers > 0) {
                    $totalCount = $totalMembers;
                    $absentCount = $totalMembers;
                }
                
                $enhancedDailySummary[] = [
                    'date' => $dateString,
                    'formatted_date' => $date->format('M d'),
                    'day_name' => $date->format('l'),
                    'present' => $presentCount,
                    'absent' => $absentCount,
                    'total' => $totalCount,
                    'attendance_rate' => $totalCount > 0 ? round(($presentCount / $totalCount) * 100, 2) : 0
                ];
            }
            
            // Get member attendance rates
            $memberAttendance = [];
            if ($totalMembers > 0) {
                $members = $eschool->members()->with('user')->get();
                foreach ($members as $member) {
                    $attendanceRecords = AttendanceRecord::where('member_id', $member->id)
                        ->where('eschool_id', $eschoolId)
                        ->whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()])
                        ->get();
                    
                    $totalAttendanceDays = $attendanceRecords->count();
                    $presentDays = $attendanceRecords->where('is_present', true)->count();
                    
                    $memberAttendance[] = [
                        'id' => $member->id,
                        'name' => $member->user->name ?? $member->name,
                        'student_id' => $member->student_id,
                        'present_days' => $presentDays,
                        'total_days' => $totalAttendanceDays,
                        'attendance_rate' => $totalAttendanceDays > 0 ? round(($presentDays / $totalAttendanceDays) * 100, 2) : 0,
                        'status' => $member->is_active ? 'active' : 'inactive'
                    ];
                }
                
                // Sort by attendance rate (descending)
                usort($memberAttendance, function($a, $b) {
                    return $b['attendance_rate'] <=> $a['attendance_rate'];
                });
            }
            
            // Get weekday analysis
            $weekdayAnalysis = [];
            $weekdays = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
            foreach ($weekdays as $weekday) {
                $weekdayData = array_filter($enhancedDailySummary, function($item) use ($weekday) {
                    return date('l', strtotime($item['date'])) === $weekday;
                });
                
                if (count($weekdayData) > 0) {
                    $totalPresent = array_sum(array_column($weekdayData, 'present'));
                    $totalRecords = array_sum(array_column($weekdayData, 'total'));
                    $averageRate = $totalRecords > 0 ? round(($totalPresent / $totalRecords) * 100, 2) : 0;
                    
                    $weekdayAnalysis[] = [
                        'day' => $weekday,
                        'short_day' => substr($weekday, 0, 3),
                        'average_attendance_rate' => $averageRate,
                        'total_sessions' => count($weekdayData),
                        'total_present' => $totalPresent,
                        'total_possible' => $totalRecords
                    ];
                }
            }
            
            // Overall statistics
            $overallPresent = array_sum(array_column($enhancedDailySummary, 'present'));
            $overallTotal = array_sum(array_column($enhancedDailySummary, 'total'));
            $overallAttendanceRate = $overallTotal > 0 ? round(($overallPresent / $overallTotal) * 100, 2) : 0;
            
            return response()->json([
                'success' => true,
                'data' => [
                    'period' => $period,
                    'date_range' => [
                        'start' => $startDate->toDateString(),
                        'end' => $endDate->toDateString()
                    ],
                    'overall' => [
                        'total_members' => $totalMembers,
                        'total_present' => $overallPresent,
                        'total_possible' => $overallTotal,
                        'attendance_rate' => $overallAttendanceRate
                    ],
                    'daily_summary' => $enhancedDailySummary,
                    'member_attendance' => array_slice($memberAttendance, 0, 10), // Top 10 members
                    'weekday_analysis' => $weekdayAnalysis
                ],
                'message' => 'Attendance analytics retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve attendance analytics: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get daily attendance summary.
     */
    public function dailySummary(Request $request): JsonResponse
    {
        try {
            $eschoolId = $request->input('eschool_id');
            $startDate = $request->input('start_date', now()->subWeek()->toDateString());
            $endDate = $request->input('end_date', now()->toDateString());

            if (!$eschoolId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Eschool ID is required'
                ], 400);
            }

            $summary = $this->attendanceService->getDailyAttendanceSummary($eschoolId, $startDate, $endDate);

            return response()->json([
                'success' => true,
                'data' => $summary,
                'message' => 'Daily attendance summary retrieved successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve daily attendance summary: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get members for attendance taking.
     */
    public function getMembers(Request $request): JsonResponse
    {
        try {
            $eschoolId = $request->input('eschool_id');
            $date = $request->input('date', now()->toDateString());

            if (!$eschoolId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Eschool ID is required'
                ], 400);
            }

            // Get the eschool
            $eschool = Eschool::findOrFail($eschoolId);
            
            // Validate that the logged-in user has access to this eschool
            $user = Auth::user();
            if ($user->role === 'koordinator') {
                // Koordinator hanya bisa mengakses eschool yang mereka koordinatori
                if ($eschool->coordinator_id !== $user->id) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Unauthorized. You can only access your own eschool.'
                    ], 403);
                }
            } elseif ($user->role === 'bendahara') {
                // Bendahara hanya bisa mengakses eschool yang mereka bendaharai
                if ($eschool->treasurer_id !== $user->id) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Unauthorized. You can only access your own eschool.'
                    ], 403);
                }
            } elseif ($user->role !== 'staff') {
                // Hanya staff, koordinator, dan bendahara yang bisa mengakses dengan eschool_id
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized. Only staff, coordinator, or treasurer can access members by eschool.'
                ], 403);
            }

            // Get all members with their attendance status for the specified date
            // Using many-to-many relationship and ensuring they're from the same school
            $members = $eschool->members()
                           ->with(['user'])
                           ->where('is_active', true)
                           ->where('school_id', $eschool->school_id) // Filter by school_id to ensure consistency
                           ->get()
                           ->map(function ($member) use ($date, $eschoolId) {
                               $attendance = AttendanceRecord::where('member_id', $member->id)
                                                           ->where('eschool_id', $eschoolId)
                                                           ->whereDate('date', $date)
                                                           ->first();
                               
                               return [
                                   'id' => $member->id,
                                   'user' => $member->user,
                                   'member_data' => $member,
                                   'attendance' => $attendance ? [
                                       'id' => $attendance->id,
                                       'is_present' => $attendance->is_present,
                                       'notes' => $attendance->notes,
                                       'recorded_at' => $attendance->created_at
                                   ] : null
                               ];
                           });

            return response()->json([
                'success' => true,
                'data' => $members,
                'message' => 'Members retrieved successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve members: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get member attendance history.
     */
    public function memberHistory(Request $request, string $memberId): JsonResponse
    {
        try {
            $startDate = $request->input('start_date');
            $endDate = $request->input('end_date');

            $history = $this->attendanceService->getMemberAttendanceHistory($memberId, $startDate, $endDate);

            return response()->json([
                'success' => true,
                'data' => $history,
                'message' => 'Member attendance history retrieved successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve member attendance history: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete attendance for a specific date.
     */
    public function deleteByDate(Request $request): JsonResponse
    {
        try {
            $eschoolId = $request->input('eschool_id');
            $date = $request->input('date');

            if (!$eschoolId || !$date) {
                return response()->json([
                    'success' => false,
                    'message' => 'Eschool ID and date are required'
                ], 400);
            }

            $deletedCount = $this->attendanceService->deleteAttendanceByDate($eschoolId, $date);

            return response()->json([
                'success' => true,
                'data' => ['deleted_count' => $deletedCount],
                'message' => "Successfully deleted {$deletedCount} attendance records"
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete attendance records: ' . $e->getMessage()
            ], 500);
        }
    }

   public function AttendanceStatistics(Request $request)
{
    $request->validate([
        'eschool_id' => 'required|exists:eschools,id',
    ]);

    $eschool = Eschool::where('id', $request->eschool_id)->firstOrFail();
    if (!in_array(Auth::user()->role, ['koordinator', 'staff']) || $eschool->coordinator_id !== Auth::id()) {
        return response()->json(['message' => 'Unauthorized'], 403);
    }

    // Keep as Carbon instances
    $today = now();
    $weekStart = now()->startOfWeek(); // Monday of the current week
    $monthStart = now()->startOfMonth();

    $totalMembers = $eschool->members()->count();

    $todayStats = AttendanceRecord::where('eschool_id', $request->eschool_id)
        ->whereDate('date', $today->toDateString())
        ->selectRaw('COUNT(*) as total, SUM(is_present) as present')
        ->first();

    $weekStats = AttendanceRecord::where('eschool_id', $request->eschool_id)
        ->whereBetween('date', [$weekStart->toDateString(), $today->toDateString()])
        ->selectRaw('COUNT(*) as total, SUM(is_present) as present')
        ->first();

    $monthStats = AttendanceRecord::where('eschool_id', $request->eschool_id)
        ->whereBetween('date', [$monthStart->toDateString(), $today->toDateString()])
        ->selectRaw('COUNT(*) as total, SUM(is_present) as present')
        ->first();

    // Calculate days using Carbon's diffInDays
    $daysInWeek = $weekStart->diffInDays($today) + 1; // Include today
    $daysInMonth = $monthStart->diffInDays($today) + 1; // Include today

    return response()->json([
        'today' => [
            'present' => (int) ($todayStats->present ?? 0),
            'total' => (int) $totalMembers,
            'percentage' => $totalMembers ? round(($todayStats->present ?? 0) / $totalMembers * 100) : 0,
        ],
        'week' => [
            'present' => (int) ($weekStats->present ?? 0),
            'total' => (int) ($totalMembers * $daysInWeek),
            'percentage' => $totalMembers ? round(($weekStats->present ?? 0) / ($totalMembers * $daysInWeek) * 100) : 0,
        ],
        'month' => [
            'present' => (int) ($monthStats->present ?? 0),
            'total' => (int) ($totalMembers * $daysInMonth),
            'percentage' => $totalMembers ? round(($monthStats->present ?? 0) / ($totalMembers * $daysInMonth) * 100) : 0,
        ],
        'total_members' => (int) $totalMembers,
    ]);
}

    public function available(Request $request)
{
    $request->validate([
        'eschool_id' => 'required|exists:eschools,id',
        'date' => 'required|date_format:Y-m-d',
    ]);

    $eschool = Eschool::findOrFail($request->eschool_id);
    \Log::info('INI ESCHOOL eschool: '.$eschool);
    if (!in_array(Auth::user()->role, ['koordinator', 'staff']) || $eschool->coordinator_id !== Auth::id()) {
        return response()->json(['message' => 'Unauthorized'], 403);
    }

    $members = $eschool->members()
        ->with(['user'])
        ->select('id',  'student_id', 'user_id')
        ->get()
        ->map(function ($member) use ($request) {
            $isAttended = AttendanceRecord::where('eschool_id', $request->eschool_id)
                ->where('member_id', $member->id)
                ->whereDate('date', $request->date)
                ->exists();

            return [
                'id' => $member->id,
                'name' => $member->user?->name  ,
                'student_id' => $member->student_id,
                'is_attended' => $isAttended,
            ];
        });

    return response()->json(['members' => $members]);
}

/**
 * Export attendance records as CSV with multi-role context
 */
public function exportCsv(Request $request, $eschoolId = null): StreamedResponse
{
    // Support both route parameter and query parameter for eschool_id
    if (!$eschoolId) {
        $eschoolId = $request->input('eschool_id');
    }
    
    $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
    $endDate = $request->input('end_date', Carbon::now()->format('Y-m-d'));
    
    if (!$eschoolId) {
        abort(400, 'Eschool ID is required');
    }
    
    $fileName = "attendance_eschool_{$eschoolId}_{$startDate}_to_{$endDate}.csv";

    return response()->stream(function () use ($eschoolId, $startDate, $endDate) {
        $file = fopen('php://output', 'w');

        // Add BOM for UTF-8
        fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

        // Get records with multi-role context
        $records = AttendanceRecord::where('eschool_id', $eschoolId)
            ->when($startDate && $endDate, function ($query) use ($startDate, $endDate) {
                return $query->whereBetween('date', [$startDate, $endDate]);
            })
            ->with([
                'member.user.eschoolRoles' => function ($query) use ($eschoolId) {
                    $query->where('eschool_id', $eschoolId);
                },
                'recorder.eschoolRoles' => function ($query) use ($eschoolId) {
                    $query->where('eschool_id', $eschoolId);
                },
                'eschool'
            ])
            ->orderBy('date', 'desc')
            ->get();

        // Add header
        fputcsv($file, [
            'ID',
            'Tanggal Kehadiran',
            'User ID',
            'Nama Member',
            'Role di Eschool',
            'ID Student',
            'Email Member',
            'Status Kehadiran',
            'Catatan',
            'Dicatat Oleh',
            'Role Pencatat',
            'Email Pencatat',
            'Nama Eschool',
            'Tanggal Dibuat',
            'Tanggal Diupdate'
        ]);

        // Add data rows
        foreach ($records as $record) {
            $userRole = optional($record->member->user->eschoolRoles)->first();
            $recorderRole = optional($record->recorder->eschoolRoles)->first();

            fputcsv($file, [
                $record->id,
                $record->date,
                $record->member->user->id ?? 'N/A',
                $record->member->user->name ?? $record->member->name ?? 'N/A',
                $userRole->role ?? 'unknown',
                $record->member->student_id ?? '',
                $record->member->user->email ?? '',
                $record->is_present ? 'Hadir' : 'Tidak Hadir',
                $record->notes ?? '',
                $record->recorder->name ?? '',
                $recorderRole->role ?? 'unknown',
                $record->recorder->email ?? '',
                $record->eschool->name ?? '',
                $record->created_at->format('Y-m-d H:i:s'),
                $record->updated_at->format('Y-m-d H:i:s')
            ]);
        }

        fclose($file);
    }, 200, [
        'Content-Type' => 'text/csv',
        'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
    ]);
}

/**
 * Export attendance records as PDF
 */
public function exportPdf(Request $request)
{
    return response()->json([
        'success' => false,
        'message' => 'PDF export is not yet implemented. Please use CSV export for now.'
    ], 400);
}

    /**
     * MULTI-ROLE ATTENDANCE MANAGEMENT METHODS
     * Enhanced methods for the new multi-role schema
     */

    /**
     * Get members list for attendance with multi-role context
     * Excludes koordinator (teachers/staff) from the list as they don't need to be tracked for attendance
     */
    public function getMembersList(Request $request, $eschoolId): JsonResponse
    {
        try {
            // Get all users who have roles in this eschool, but exclude koordinator for attendance purposes
            $members = \App\Models\User::whereHas('eschoolRoles', function ($query) use ($eschoolId) {
                $query->where('eschool_id', $eschoolId)
                      ->where('status', 'active')
                      ->whereIn('role', ['member', 'bendahara']); // Only include members and bendahara, exclude koordinator
            })->with([
                'eschoolRoles' => function ($query) use ($eschoolId) {
                    $query->where('eschool_id', $eschoolId);
                },
                'member' // Use singular 'member' relationship
            ])->get();

            $membersList = $members->map(function ($user) use ($eschoolId) {
                $roleInEschool = $user->eschoolRoles->first();
                $memberData = $user->member; // Use singular member
                
                // Get attendance summary for this user in this eschool
                $attendanceRecords = AttendanceRecord::where('member_id', $memberData->id ?? 0)
                    ->where('eschool_id', $eschoolId)
                    ->get();
                
                $totalSessions = $attendanceRecords->count();
                $attendedSessions = $attendanceRecords->where('is_present', true)->count();
                $attendanceRate = $totalSessions > 0 ? ($attendedSessions / $totalSessions) * 100 : 0;

                // Get other roles this user has in other eschools
                $otherRoles = $user->eschoolRoles->where('eschool_id', '!=', $eschoolId)->map(function ($role) {
                    return [
                        'eschool_id' => $role->eschool_id,
                        'eschool_name' => $role->eschool->name ?? 'Unknown',
                        'role' => $role->role
                    ];
                });

                return [
                    'user_id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'student_id' => $memberData->student_id ?? 'N/A',
                    'phone' => $memberData->phone ?? $user->phone,
                    'role_in_eschool' => $roleInEschool->role ?? 'unknown',
                    'permissions' => $roleInEschool ? $roleInEschool->getPermissions() : [],
                    'status' => $roleInEschool->status ?? 'inactive',
                    'assigned_at' => $roleInEschool->created_at ?? null,
                    'member_details' => [
                        'gender' => $memberData->gender ?? null,
                        'address' => $memberData->address ?? null,
                        'date_of_birth' => $memberData->date_of_birth ?? null
                    ],
                    'other_roles' => $otherRoles->toArray(),
                    'attendance_summary' => [
                        'total_sessions' => $totalSessions,
                        'attended' => $attendedSessions,
                        'attendance_rate' => round($attendanceRate, 2)
                    ]
                ];
            });

            // Role summary
            $roleSummary = $membersList->groupBy('role_in_eschool')->map(function ($group) {
                return $group->count();
            });

            return response()->json([
                'success' => true,
                'data' => $membersList->values(),
                'role_summary' => $roleSummary,
                'pagination' => [
                    'current_page' => 1,
                    'last_page' => 1,
                    'per_page' => $membersList->count(),
                    'total' => $membersList->count()
                ],
                'message' => 'Members list retrieved successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve members list: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get enhanced analytics with multi-role context
     */
    public function getAnalytics(Request $request, $eschoolId): JsonResponse
    {
        try {
            $period = $request->input('period', 'week');
            
            // Get date range based on period
            $dateRange = $this->getDateRange($period);
            
            // Get all attendance records for this eschool in the period
            $attendanceRecords = AttendanceRecord::where('eschool_id', $eschoolId)
                ->whereBetween('date', [$dateRange['start'], $dateRange['end']])
                ->with(['member.user', 'member.user.eschoolRoles' => function ($query) use ($eschoolId) {
                    $query->where('eschool_id', $eschoolId);
                }])
                ->get();

            // Get all active members with their roles
            $allMembers = \App\Models\User::whereHas('eschoolRoles', function ($query) use ($eschoolId) {
                $query->where('eschool_id', $eschoolId)->where('status', 'active');
            })->with([
                'eschoolRoles' => function ($query) use ($eschoolId) {
                    $query->where('eschool_id', $eschoolId);
                },
                'member' // Use singular 'member' relationship
            ])->get();

            $totalMembers = $allMembers->count();
            $activeMembers = $allMembers->where('eschoolRoles.0.status', 'active')->count();
            
            // Calculate overall statistics
            $totalPresent = $attendanceRecords->where('is_present', true)->count();
            $totalPossible = $this->calculateTotalPossibleAttendance($allMembers, $dateRange);
            $attendanceRate = $totalPossible > 0 ? ($totalPresent / $totalPossible) * 100 : 0;

            // Role breakdown analysis
            $roleBreakdown = [];
            $roleGroups = $allMembers->groupBy('eschoolRoles.0.role');
            
            foreach ($roleGroups as $role => $users) {
                $roleAttendance = $attendanceRecords->whereIn('member.user.id', $users->pluck('id'));
                $rolePresent = $roleAttendance->where('is_present', true)->count();
                $roleTotal = $users->count() * $this->getWorkingDaysInRange($dateRange);
                
                $roleBreakdown[$role] = [
                    'total' => $users->count(),
                    'present' => $rolePresent,
                    'rate' => $roleTotal > 0 ? round(($rolePresent / $roleTotal) * 100, 2) : 0
                ];
            }

            // Daily summary with role context
            $dailySummary = $this->generateDailySummaryWithRoles($attendanceRecords, $dateRange, $allMembers);

            // Member attendance with multi-role context
            $memberAttendance = $allMembers->map(function ($user) use ($attendanceRecords, $eschoolId) {
                $userAttendance = $attendanceRecords->where('member.user.id', $user->id);
                $presentDays = $userAttendance->where('is_present', true)->count();
                $totalDays = $userAttendance->count();
                $attendanceRate = $totalDays > 0 ? ($presentDays / $totalDays) * 100 : 0;
                
                $roleInEschool = $user->eschoolRoles->first();
                $memberData = $user->member; // Use singular member relationship
                
                // Get other participations
                $otherParticipations = $user->eschoolRoles->where('eschool_id', '!=', $eschoolId)->map(function ($role) {
                    // Get attendance rate for other eschool
                    $otherAttendance = AttendanceRecord::whereHas('member', function ($query) use ($role) {
                        $query->where('user_id', $role->user_id);
                    })->where('eschool_id', $role->eschool_id)->get();
                    
                    $otherPresent = $otherAttendance->where('is_present', true)->count();
                    $otherTotal = $otherAttendance->count();
                    $otherRate = $otherTotal > 0 ? ($otherPresent / $otherTotal) * 100 : 0;
                    
                    return [
                        'eschool_name' => $role->eschool->name ?? 'Unknown',
                        'role' => $role->role,
                        'attendance_rate' => round($otherRate, 2)
                    ];
                });

                return [
                    'user_id' => $user->id,
                    'name' => $user->name,
                    'role_in_eschool' => $roleInEschool->role ?? 'unknown',
                    'student_id' => $memberData->student_id ?? 'N/A',
                    'present_days' => $presentDays,
                    'total_days' => $totalDays,
                    'attendance_rate' => round($attendanceRate, 2),
                    'status' => $roleInEschool->status ?? 'inactive',
                    'other_participations' => $otherParticipations->toArray()
                ];
            })->sortByDesc('attendance_rate');

            return response()->json([
                'success' => true,
                'data' => [
                    'period' => $period,
                    'date_range' => [
                        'start' => $dateRange['start'],
                        'end' => $dateRange['end']
                    ],
                    'overall' => [
                        'total_members' => $totalMembers,
                        'active_members' => $activeMembers,
                        'total_present' => $totalPresent,
                        'total_possible' => $totalPossible,
                        'attendance_rate' => round($attendanceRate, 2)
                    ],
                    'role_breakdown' => $roleBreakdown,
                    'daily_summary' => $dailySummary,
                    'member_attendance' => $memberAttendance->values(),
                    'weekday_analysis' => $this->generateWeekdayAnalysis($attendanceRecords, $dateRange)
                ],
                'message' => 'Attendance analytics retrieved successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve attendance analytics: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get enhanced statistics with role context
     */
    public function getStatistics(Request $request, $eschoolId): JsonResponse
    {
        try {
            $today = Carbon::today();
            $weekStart = $today->copy()->startOfWeek();
            $monthStart = $today->copy()->startOfMonth();

            // Get active members with roles
            $activeMembers = \App\Models\User::whereHas('eschoolRoles', function ($query) use ($eschoolId) {
                $query->where('eschool_id', $eschoolId)->where('status', 'active');
            })->with(['eschoolRoles' => function ($query) use ($eschoolId) {
                $query->where('eschool_id', $eschoolId);
            }])->count();

            // Today's statistics
            $todayRecords = AttendanceRecord::where('eschool_id', $eschoolId)
                ->whereDate('date', $today)
                ->get();
            $todayPresent = $todayRecords->where('is_present', true)->count();
            $todayPercentage = $activeMembers > 0 ? ($todayPresent / $activeMembers) * 100 : 0;

            // Week's statistics
            $weekRecords = AttendanceRecord::where('eschool_id', $eschoolId)
                ->whereBetween('date', [$weekStart, $today])
                ->get();
            $weekPresent = $weekRecords->where('is_present', true)->count();
            $weekTotal = $weekRecords->count();
            $weekPercentage = $weekTotal > 0 ? ($weekPresent / $weekTotal) * 100 : 0;

            // Month's statistics
            $monthRecords = AttendanceRecord::where('eschool_id', $eschoolId)
                ->whereBetween('date', [$monthStart, $today])
                ->get();
            $monthPresent = $monthRecords->where('is_present', true)->count();
            $monthTotal = $monthRecords->count();
            $monthPercentage = $monthTotal > 0 ? ($monthPresent / $monthTotal) * 100 : 0;

            // Role-based statistics
            $roleStats = \App\Models\User::whereHas('eschoolRoles', function ($query) use ($eschoolId) {
                $query->where('eschool_id', $eschoolId)->where('status', 'active');
            })->with(['eschoolRoles' => function ($query) use ($eschoolId) {
                $query->where('eschool_id', $eschoolId);
            }])->get()->groupBy('eschoolRoles.0.role')->map(function ($users, $role) use ($eschoolId, $today) {
                $userIds = $users->pluck('id');
                $todayPresent = AttendanceRecord::whereHas('member', function ($query) use ($userIds) {
                    $query->whereIn('user_id', $userIds);
                })->where('eschool_id', $eschoolId)
                  ->whereDate('date', $today)
                  ->where('is_present', true)
                  ->count();

                return [
                    'total' => $users->count(),
                    'present_today' => $todayPresent,
                    'rate_today' => $users->count() > 0 ? round(($todayPresent / $users->count()) * 100, 2) : 0
                ];
            });

            return response()->json([
                'success' => true,
                'data' => [
                    'today' => [
                        'present' => $todayPresent,
                        'total' => $activeMembers,
                        'percentage' => round($todayPercentage, 2)
                    ],
                    'week' => [
                        'present' => $weekPresent,
                        'total' => $weekTotal,
                        'percentage' => round($weekPercentage, 2)
                    ],
                    'month' => [
                        'present' => $monthPresent,
                        'total' => $monthTotal,
                        'percentage' => round($monthPercentage, 2)
                    ],
                    'total_members' => $activeMembers,
                    'role_statistics' => $roleStats
                ],
                'message' => 'Attendance statistics retrieved successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve attendance statistics: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get enhanced attendance records with multi-role context
     */
    public function getRecords(Request $request, $eschoolId): JsonResponse
    {
        try {
            $page = $request->input('page', 1);
            $perPage = $request->input('per_page', 10);
            $search = $request->input('search');
            $roleFilter = $request->input('role_filter');
            $dateFilter = $request->input('date_filter');
            $statusFilter = $request->input('status_filter');

            // Build query with multi-role context
            $query = AttendanceRecord::where('eschool_id', $eschoolId)
                ->with([
                    'member.user.eschoolRoles' => function ($q) use ($eschoolId) {
                        $q->where('eschool_id', $eschoolId);
                    },
                    'recorder.eschoolRoles' => function ($q) use ($eschoolId) {
                        $q->where('eschool_id', $eschoolId);
                    }
                ]);

            // Apply filters
            if ($search) {
                $query->whereHas('member.user', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
                })->orWhereHas('member', function ($q) use ($search) {
                    $q->where('student_id', 'like', "%{$search}%");
                });
            }

            if ($roleFilter && $roleFilter !== 'all') {
                $query->whereHas('member.user.eschoolRoles', function ($q) use ($roleFilter, $eschoolId) {
                    $q->where('eschool_id', $eschoolId)->where('role', $roleFilter);
                });
            }

            if ($dateFilter) {
                $query->whereDate('date', $dateFilter);
            }

            if ($statusFilter !== null) {
                $query->where('is_present', $statusFilter === 'present');
            }

            // Get paginated results
            $records = $query->orderBy('date', 'desc')
                ->orderBy('created_at', 'desc')
                ->paginate($perPage);

            // Transform records with multi-role context
            $transformedRecords = $records->getCollection()->map(function ($record) {
                $userRole = $record->member->user->eschoolRoles->first();
                $recorderRole = $record->recorder->eschoolRoles->first();

                return [
                    'id' => $record->id,
                    'date' => $record->date,
                    'is_present' => $record->is_present,
                    'notes' => $record->notes,
                    'proof_document_path' => $record->proof_document_path,
                    'member' => [
                        'user_id' => $record->member->user->id,
                        'name' => $record->member->user->name,
                        'email' => $record->member->user->email,
                        'student_id' => $record->member->student_id,
                        'phone' => $record->member->phone,
                        'role_in_eschool' => $userRole->role ?? 'unknown'
                    ],
                    'recorder' => [
                        'id' => $record->recorder->id,
                        'name' => $record->recorder->name,
                        'email' => $record->recorder->email,
                        'role_in_eschool' => $recorderRole->role ?? 'unknown'
                    ],
                    'created_at' => $record->created_at,
                    'updated_at' => $record->updated_at
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $transformedRecords,
                'meta' => [
                    'total' => $records->total(),
                    'per_page' => $records->perPage(),
                    'current_page' => $records->currentPage(),
                    'last_page' => $records->lastPage(),
                    'from' => $records->firstItem(),
                    'to' => $records->lastItem(),
                    'has_next_page' => $records->hasMorePages(),
                    'has_prev_page' => $records->currentPage() > 1
                ],
                'message' => 'Attendance records retrieved successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve attendance records: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create attendance record with multi-role context
     */
    public function createRecord(Request $request, $eschoolId): JsonResponse
    {
        try {
            // Log the incoming data for debugging
            \Log::info('Attendance createRecord request', [
                'eschool_id' => $eschoolId,
                'request_data' => $request->all(),
                'members_count' => count($request->input('members', [])),
            ]);

            $request->validate([
                'date' => 'required|date',
                'members' => 'required|array',
                'members.*.member_id' => 'required|exists:users,id', // Changed from user_id to member_id for frontend compatibility
                'members.*.is_present' => 'required|boolean',
                'members.*.notes' => 'nullable|string|max:500',
                'members.*.proof_document' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048'
            ]);

            $recorderId = auth()->id();
            $date = $request->input('date');
            $membersData = $request->input('members');
            $createdRecords = [];
            $errors = [];

            foreach ($membersData as $index => $memberData) {
                // Convert member_id to user_id for internal processing
                $userId = $memberData['member_id'];
                
                // Verify user has role in this eschool
                $userRole = \App\Models\UserEschoolRole::where('user_id', $userId)
                    ->where('eschool_id', $eschoolId)
                    ->where('status', 'active')
                    ->first();

                if (!$userRole) {
                    continue; // Skip users who don't have role in this eschool
                }

                // Get member record
                $member = \App\Models\Member::where('user_id', $userId)
                    ->whereHas('eschools', function ($query) use ($eschoolId) {
                        $query->where('eschool_id', $eschoolId);
                    })->first();

                if (!$member) {
                    continue; // Skip if no member record found
                }

                // Check for duplicate attendance
                $existingRecord = AttendanceRecord::where('eschool_id', $eschoolId)
                    ->where('member_id', $member->id)
                    ->whereDate('date', $date)
                    ->first();

                if ($existingRecord) {
                    $errors[] = "Member {$member->user->name} sudah absen pada tanggal {$date}";
                    continue;
                }

                // Handle proof document upload
                $proofDocumentPath = null;
                $proofDocumentName = null;
                $proofDocumentType = null;
                $proofDocumentSize = null;

                if ($request->hasFile("members.{$index}.proof_document")) {
                    $file = $request->file("members.{$index}.proof_document");
                    $proofDocumentPath = $file->store('attendance/proof_documents', 'public');
                    $proofDocumentName = $file->getClientOriginalName();
                    $proofDocumentType = $file->getClientMimeType();
                    $proofDocumentSize = $file->getSize();
                }

                // Create or update attendance record
                $attendanceRecord = AttendanceRecord::create([
                    'eschool_id' => $eschoolId,
                    'member_id' => $member->id,
                    'date' => $date,
                    'recorder_id' => $recorderId,
                    'is_present' => $memberData['is_present'],
                    'notes' => $memberData['notes'] ?? null,
                    'proof_document_path' => $proofDocumentPath,
                    'proof_document_name' => $proofDocumentName,
                    'proof_document_type' => $proofDocumentType,
                    'proof_document_size' => $proofDocumentSize
                ]);

                $createdRecords[] = $attendanceRecord->load(['member.user', 'recorder']);
            }

            // If there are duplicate errors, return them
            if (!empty($errors)) {
                return response()->json([
                    'success' => false,
                    'messages' => $errors
                ], 422);
            }

            return response()->json([
                'success' => true,
                'data' => $createdRecords,
                'message' => 'Attendance records created successfully',
                'total_created' => count($createdRecords)
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create attendance records: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Helper methods for analytics
     */
    private function getDateRange($period): array
    {
        $end = Carbon::now();
        
        switch ($period) {
            case 'week':
                $start = $end->copy()->startOfWeek();
                break;
            case 'month':
                $start = $end->copy()->startOfMonth();
                break;
            case 'year':
                $start = $end->copy()->startOfYear();
                break;
            default:
                $start = $end->copy()->startOfWeek();
        }

        return [
            'start' => $start->format('Y-m-d'),
            'end' => $end->format('Y-m-d')
        ];
    }

    private function calculateTotalPossibleAttendance($members, $dateRange): int
    {
        $workingDays = $this->getWorkingDaysInRange($dateRange);
        return $members->count() * $workingDays;
    }

    private function getWorkingDaysInRange($dateRange): int
    {
        $start = Carbon::parse($dateRange['start']);
        $end = Carbon::parse($dateRange['end']);
        
        $workingDays = 0;
        while ($start->lte($end)) {
            if (!$start->isWeekend()) {
                $workingDays++;
            }
            $start->addDay();
        }
        
        return $workingDays;
    }

    private function generateDailySummaryWithRoles($attendanceRecords, $dateRange, $allMembers): array
    {
        $start = Carbon::parse($dateRange['start']);
        $end = Carbon::parse($dateRange['end']);
        $summary = [];

        while ($start->lte($end)) {
            $dateString = $start->format('Y-m-d');
            $dayRecords = $attendanceRecords->where('date', $dateString);
            
            $roleAttendance = [];
            $membersByRole = $allMembers->groupBy('eschoolRoles.0.role');
            
            foreach ($membersByRole as $role => $users) {
                $userIds = $users->pluck('id');
                $rolePresent = $dayRecords->whereIn('member.user.id', $userIds)->where('is_present', true)->count();
                $roleTotal = $users->count();
                
                $roleAttendance[$role] = [
                    'present' => $rolePresent,
                    'total' => $roleTotal
                ];
            }

            $totalPresent = $dayRecords->where('is_present', true)->count();
            $totalPossible = $allMembers->count();
            
            $summary[] = [
                'date' => $dateString,
                'formatted_date' => $start->format('M d'),
                'day_name' => $start->format('l'),
                'role_attendance' => $roleAttendance,
                'overall_present' => $totalPresent,
                'overall_total' => $totalPossible,
                'overall_rate' => $totalPossible > 0 ? round(($totalPresent / $totalPossible) * 100, 2) : 0
            ];
            
            $start->addDay();
        }

        return $summary;
    }

    private function generateWeekdayAnalysis($attendanceRecords, $dateRange): array
    {
        $weekdays = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
        $analysis = [];

        foreach ($weekdays as $weekday) {
            $weekdayRecords = $attendanceRecords->filter(function ($record) use ($weekday) {
                return Carbon::parse($record->date)->format('l') === $weekday;
            });

            $totalSessions = $weekdayRecords->groupBy('date')->count();
            $totalPresent = $weekdayRecords->where('is_present', true)->count();
            $totalPossible = $weekdayRecords->count();

            $analysis[] = [
                'day' => $weekday,
                'short_day' => substr($weekday, 0, 3),
                'average_attendance_rate' => $totalPossible > 0 ? round(($totalPresent / $totalPossible) * 100, 2) : 0,
                'total_sessions' => $totalSessions,
                'total_present' => $totalPresent,
                'total_possible' => $totalPossible
            ];
        }

        return $analysis;
    }
}