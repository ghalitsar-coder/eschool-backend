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
        
        // Middleware untuk memastikan hanya koordinator yang bisa akses
        $this->middleware('role:koordinator');
    }

    /**
     * Display a listing of attendance records.
     */
    public function index(Request $request): JsonResponse
{
    try {
        $eschoolId = $request->input('eschool_id');
        $date = $request->input('date', now()->toDateString());
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        if (!$eschoolId) {
            return response()->json([
                'success' => false,
                'message' => 'Eschool ID is required'
            ], 400);
        }

        if ($startDate && $endDate) {
            // Get attendance records for date range
            $records = AttendanceRecord::with(['member.user', 'recorder'])
                ->byEschool($eschoolId)
                ->byDateRange($startDate, $endDate)
                ->orderBy('date', 'desc')
                ->orderBy('created_at', 'desc')
                ->paginate(15);
        } else {
            // Get attendance records for specific date
            $records = AttendanceRecord::with(['member.user', 'recorder'])
                ->byEschool($eschoolId)
                ->whereDate('date', $date)
                ->orderBy('created_at', 'desc')
                ->paginate(15);
        }

        return response()->json([
    'success' => true,
    'data' => AttendanceRecordResource::collection($records),
    'meta' => [
        'total' => $records->count(),
        'per_page' => $records->count(),
        'current_page' => 1,
        'last_page' => 1
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

            $attendance->update($validatedData);
            $attendance->load(['member', 'recorder']);

            return response()->json([
                'success' => true,
                'data' => $attendance,
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
    public function destroy(AttendanceRecord $attendance): JsonResponse
    {
        try {
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
     * Get attendance AttendanceS
     */
    public function statistics(Request $request): JsonResponse
    {
        try {
            $eschoolId = $request->input('eschool_id');
            $startDate = $request->input('start_date', now()->subMonth()->toDateString());
            $endDate = $request->input('end_date', now()->toDateString());

            if (!$eschoolId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Eschool ID is required'
                ], 400);
            }

            $statistics = $this->attendanceService->getAttendanceStatistics($eschoolId, $startDate, $endDate);

            return response()->json([
                'success' => true,
                'data' => $statistics,
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

            // Get all members with their attendance status for the specified date
            // Using many-to-many relationship
            $eschool = Eschool::findOrFail($eschoolId);
            $members = $eschool->members()
                           ->with(['user'])
                           ->get()
                           ->map(function ($member) use ($date) {
                               $attendance = AttendanceRecord::where('member_id', $member->id)
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
 * Export attendance records as CSV
 */
public function exportCsv(Request $request): StreamedResponse
{
    $eschoolId = $request->input('eschool_id');
    $startDate = $request->input('start_date');
    $endDate = $request->input('end_date');
    
    if (!$eschoolId) {
        abort(400, 'Eschool ID is required');
    }
    
    // Build query
    $query = AttendanceRecord::with(['member.user', 'recorder', 'eschool'])
        ->byEschool($eschoolId)
        ->orderBy('date', 'desc')
        ->orderBy('created_at', 'desc');
        
    if ($startDate && $endDate) {
        $query->byDateRange($startDate, $endDate);
    }
    
    $records = $query->get();
    
    $fileName = 'attendance_records_' . now()->format('Y-m-d_H-i-s') . '.csv';
    
    return response()->streamDownload(function () use ($records) {
        $file = fopen('php://output', 'w');
        
        // Add CSV headers
        fputcsv($file, [
            'ID',
            'Tanggal',
            'Nama Member',
            'ID Student',
            'Email Member',
            'Status Kehadiran',
            'Catatan',
            'Dicatat Oleh',
            'Email Pencatat',
            'Nama Eschool',
            'Tanggal Dibuat',
            'Tanggal Diupdate'
        ]);
        
        // Add data rows
        foreach ($records as $record) {
            fputcsv($file, [
                $record->id,
                $record->date,
                $record->member->user->name ?? $record->member->name ?? 'N/A',
                $record->member->student_id ?? '',
                $record->member->user->email ?? '',
                $record->is_present ? 'Hadir' : 'Tidak Hadir',
                $record->notes ?? '',
                $record->recorder->name ?? '',
                $record->recorder->email ?? '',
                $record->eschool->name ?? '',
                $record->created_at->format('Y-m-d H:i:s'),
                $record->updated_at->format('Y-m-d H:i:s')
            ]);
        }
        
        fclose($file);
    }, $fileName, [
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
}