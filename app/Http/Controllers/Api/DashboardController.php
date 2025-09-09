<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\UserEschoolRole;
use App\Models\AttendanceRecord;
use App\Models\KasRecord;
use App\Models\KasPayment;
use App\Models\Eschool;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Get multi-role profile data for dashboard
     */
    public function getMultiRoleProfile(Request $request)
    {
        try {
            $user = Auth::user()->load(['profile', 'userEschoolRoles.eschool.school']);
            
            if (!$user) {
                return response()->json([
                    'message' => 'Unauthenticated',
                    'error' => 'No authenticated user found'
                ], 401);
            }

            // Get user's profile data
            $profile = $user->profile;
            $userEschoolRoles = $user->userEschoolRoles;

            // Prepare eschool roles data
            $eschoolRoles = [];
            $totalEschools = 0;
            $totalKasManaged = 0;
            $totalPersonalKas = 0;
            $totalAttendanceRate = 0;
            $roleCount = [
                'koordinator' => 0,
                'bendahara' => 0,
                'member' => 0
            ];

            foreach ($userEschoolRoles as $userEschoolRole) {
                $eschool = $userEschoolRole->eschool;
                $school = $eschool->school;

                // Count roles
                switch ($userEschoolRole->role) {
                    case 'coordinator':
                        $roleCount['koordinator']++;
                        break;
                    case 'treasurer':
                        $roleCount['bendahara']++;
                        break;
                    case 'member':
                        $roleCount['member']++;
                        break;
                }

                // Get attendance summary for this role
                $attendanceRecords = $userEschoolRole->attendanceRecords;
                $totalMeetings = $attendanceRecords->count();
                $attendedMeetings = $attendanceRecords->where('status', 'present')->count();
                $attendanceRate = $totalMeetings > 0 ? round(($attendedMeetings / $totalMeetings) * 100, 2) : 0;
                $totalAttendanceRate += $attendanceRate;

                // Get kas summary for this role
                $kasSummary = $this->getKasSummaryForRole($userEschoolRole);
                
                if ($userEschoolRole->role === 'treasurer') {
                    $totalKasManaged += $kasSummary['total_balance'] ?? 0;
                }
                $totalPersonalKas += $kasSummary['personal_balance'] ?? 0;

                // Get permissions based on role
                $permissions = $this->getPermissionsForRole($userEschoolRole->role);

                $eschoolRoles[] = [
                    'eschool_id' => $eschool->id,
                    'eschool_name' => $eschool->name,
                    'school_id' => $school->id,
                    'role_in_eschool' => $this->mapRoleToIndonesian($userEschoolRole->role),
                    'permissions' => $permissions,
                    'assigned_at' => $userEschoolRole->created_at->toISOString(),
                    'status' => 'active', // Assuming all are active
                    'kas_summary' => $kasSummary,
                    'attendance_summary' => [
                        'total_meetings' => $totalMeetings,
                        'attended' => $attendedMeetings,
                        'attendance_rate' => $attendanceRate
                    ]
                ];

                $totalEschools++;
            }

            // Calculate overall metrics
            $avgAttendanceRate = $totalEschools > 0 ? round($totalAttendanceRate / $totalEschools, 2) : 0;
            $overallActivityScore = $this->calculateActivityScore($avgAttendanceRate, $totalPersonalKas, $totalEschools);

            // Get recent activities
            $recentActivities = $this->getRecentActivities($user->id);

            // Prepare response data
            $responseData = [
                'user' => [
                    'id' => $user->id,
                    'name' => $profile->name,
                    'email' => $user->email,
                    'base_role' => $this->getUserPrimaryRole($userEschoolRoles),
                    'is_system_admin' => false // Assuming no system admin for now
                ],
                'eschool_roles' => $eschoolRoles,
                'overall_summary' => [
                    'total_eschools' => $totalEschools,
                    'roles' => $roleCount,
                    'performance' => [
                        'avg_attendance_rate' => $avgAttendanceRate,
                        'total_kas_managed' => $totalKasManaged,
                        'total_personal_kas' => $totalPersonalKas,
                        'overall_activity_score' => $overallActivityScore
                    ]
                ],
                'recent_activities' => $recentActivities
            ];

            return response()->json([
                'success' => true,
                'data' => $responseData
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error retrieving multi-role profile data',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get attendance statistics for coordinator dashboard
     */
    public function getAttendanceStatistics(Request $request)
    {
        try {
            $user = Auth::user();
            $coordinatorRole = $user->userEschoolRoles()->where('role', 'coordinator')->first();
            
            if (!$coordinatorRole) {
                return response()->json([
                    'message' => 'User is not a coordinator',
                    'error' => 'Access denied'
                ], 403);
            }

            $eschoolId = $coordinatorRole->eschool_id;
            $today = Carbon::today();
            $weekStart = Carbon::now()->startOfWeek();
            $monthStart = Carbon::now()->startOfMonth();

            // Get all members of this eschool
            $memberRoles = UserEschoolRole::where('eschool_id', $eschoolId)
                ->whereIn('role', ['member', 'treasurer'])
                ->get();

            $totalMembers = $memberRoles->count();

            // Today's statistics
            $todayAttendance = AttendanceRecord::whereIn('user_eschool_role_id', $memberRoles->pluck('id'))
                ->whereDate('created_at', $today)
                ->get();
            
            $todayPresent = $todayAttendance->where('status', 'present')->count();
            $todayTotal = $todayAttendance->count();
            $todayPercentage = $todayTotal > 0 ? round(($todayPresent / $todayTotal) * 100, 2) : 0;

            // This week's statistics
            $weekAttendance = AttendanceRecord::whereIn('user_eschool_role_id', $memberRoles->pluck('id'))
                ->where('created_at', '>=', $weekStart)
                ->get();
            
            $weekPresent = $weekAttendance->where('status', 'present')->count();
            $weekTotal = $weekAttendance->count();
            $weekPercentage = $weekTotal > 0 ? round(($weekPresent / $weekTotal) * 100, 2) : 0;

            // This month's statistics
            $monthAttendance = AttendanceRecord::whereIn('user_eschool_role_id', $memberRoles->pluck('id'))
                ->where('created_at', '>=', $monthStart)
                ->get();
            
            $monthPresent = $monthAttendance->where('status', 'present')->count();
            $monthTotal = $monthAttendance->count();
            $monthPercentage = $monthTotal > 0 ? round(($monthPresent / $monthTotal) * 100, 2) : 0;

            $statistics = [
                'today' => [
                    'present' => $todayPresent,
                    'total' => $todayTotal,
                    'percentage' => $todayPercentage
                ],
                'week' => [
                    'present' => $weekPresent,
                    'total' => $weekTotal,
                    'percentage' => $weekPercentage
                ],
                'month' => [
                    'present' => $monthPresent,
                    'total' => $monthTotal,
                    'percentage' => $monthPercentage
                ],
                'total_members' => $totalMembers
            ];

            return response()->json([
                'success' => true,
                'data' => $statistics
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error retrieving attendance statistics',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get attendance analytics for coordinator dashboard
     */
    public function getAttendanceAnalytics(Request $request)
    {
        try {
            $user = Auth::user();
            $coordinatorRole = $user->userEschoolRoles()->where('role', 'coordinator')->first();
            
            if (!$coordinatorRole) {
                return response()->json([
                    'message' => 'User is not a coordinator',
                    'error' => 'Access denied'
                ], 403);
            }

            $eschoolId = $coordinatorRole->eschool_id;
            $period = $request->get('period', 'week');

            // Get date range based on period
            switch ($period) {
                case 'month':
                    $startDate = Carbon::now()->startOfMonth();
                    break;
                case 'year':
                    $startDate = Carbon::now()->startOfYear();
                    break;
                default:
                    $startDate = Carbon::now()->startOfWeek();
            }

            // Get all members of this eschool
            $memberRoles = UserEschoolRole::where('eschool_id', $eschoolId)
                ->whereIn('role', ['member', 'treasurer'])
                ->with('user.profile')
                ->get();

            // Overall statistics
            $allAttendance = AttendanceRecord::whereIn('user_eschool_role_id', $memberRoles->pluck('id'))
                ->where('created_at', '>=', $startDate)
                ->get();

            $totalPresent = $allAttendance->where('status', 'present')->count();
            $totalPossible = $allAttendance->count();
            $attendanceRate = $totalPossible > 0 ? round(($totalPresent / $totalPossible) * 100, 2) : 0;

            // Daily summary
            $dailySummary = AttendanceRecord::whereIn('user_eschool_role_id', $memberRoles->pluck('id'))
                ->where('date', '>=', $startDate)
                ->select(
                    DB::raw('DATE(date) as date'),
                    DB::raw('SUM(CASE WHEN is_present = 1 THEN 1 ELSE 0 END) as present'),
                    DB::raw('SUM(CASE WHEN is_present = 0 THEN 1 ELSE 0 END) as absent'),
                    DB::raw('COUNT(*) as total')
                )
                ->groupBy('date')
                ->orderBy('date')
                ->get()
                ->map(function ($item) {
                    return [
                        'date' => $item->date,
                        'formatted_date' => Carbon::parse($item->date)->format('M d'),
                        'present' => $item->present,
                        'absent' => $item->absent,
                        'total' => $item->total
                    ];
                });

            // Member attendance rates
            $memberAttendance = $memberRoles->map(function ($memberRole) use ($startDate) {
                $attendance = AttendanceRecord::where('user_eschool_role_id', $memberRole->id)
                    ->where('date', '>=', $startDate)
                    ->get();

                $present = $attendance->where('is_present', true)->count();
                $total = $attendance->count();
                $rate = $total > 0 ? round(($present / $total) * 100, 2) : 0;

                return [
                    'name' => $memberRole->user->profile->name,
                    'attendance_rate' => $rate,
                    'present' => $present,
                    'total' => $total
                ];
            })->sortByDesc('attendance_rate')->values();

            // Weekday analysis
            $weekdayAnalysis = AttendanceRecord::whereIn('user_eschool_role_id', $memberRoles->pluck('id'))
                ->where('date', '>=', $startDate)
                ->select(
                    DB::raw('DAYOFWEEK(date) as day_of_week'),
                    DB::raw('AVG(CASE WHEN is_present = 1 THEN 100 ELSE 0 END) as average_attendance_rate')
                )
                ->groupBy('day_of_week')
                ->orderBy('day_of_week')
                ->get()
                ->map(function ($item) {
                    $days = ['', 'Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
                    $shortDays = ['', 'Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
                    
                    return [
                        'day_of_week' => $days[$item->day_of_week],
                        'short_day' => $shortDays[$item->day_of_week],
                        'average_attendance_rate' => round($item->average_attendance_rate, 2)
                    ];
                });

            $analytics = [
                'overall' => [
                    'total_members' => $memberRoles->count(),
                    'total_present' => $totalPresent,
                    'total_possible' => $totalPossible,
                    'attendance_rate' => $attendanceRate
                ],
                'daily_summary' => $dailySummary,
                'member_attendance' => $memberAttendance->take(10), // Top 10 members
                'weekday_analysis' => $weekdayAnalysis
            ];

            return response()->json([
                'success' => true,
                'data' => $analytics
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error retrieving attendance analytics',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get kas summary for bendahara dashboard
     */
    public function getKasSummary(Request $request)
    {
        try {
            $user = Auth::user();
            $treasurerRole = $user->userEschoolRoles()->where('role', 'treasurer')->first();
            
            if (!$treasurerRole) {
                return response()->json([
                    'message' => 'User is not a treasurer',
                    'error' => 'Access denied'
                ], 403);
            }

            $eschoolId = $treasurerRole->eschool_id;
            $eschool = Eschool::find($eschoolId);

            // Get kas records for this eschool
            $kasRecords = KasRecord::where('eschool_id', $eschoolId)->get();
            
            $totalIncome = $kasRecords->where('category', 'income')->sum('amount');
            $totalExpense = $kasRecords->where('category', 'expense')->sum('amount');
            $balance = $totalIncome - $totalExpense;

            // Get member count
            $totalMembers = UserEschoolRole::where('eschool_id', $eschoolId)
                ->whereIn('role', ['member', 'treasurer'])
                ->count();

            // Current month payment statistics
            $currentMonth = Carbon::now()->month;
            $currentYear = Carbon::now()->year;

            $monthlyPayments = KasPayment::whereHas('kasRecord', function ($query) use ($eschoolId) {
                $query->where('eschool_id', $eschoolId);
            })
            ->where('month', $currentMonth)
            ->where('year', $currentYear)
            ->get();

            $paidCount = $monthlyPayments->where('is_paid', true)->count();
            $unpaidCount = $totalMembers - $paidCount;
            $paymentPercentage = $totalMembers > 0 ? round(($paidCount / $totalMembers) * 100, 2) : 0;

            $summary = [
                'eschool' => [
                    'name' => $eschool->name,
                    'monthly_kas_amount' => $eschool->monthly_fee_amount
                ],
                'summary' => [
                    'total_income' => $totalIncome,
                    'total_expense' => $totalExpense,
                    'balance' => $balance,
                    'total_members' => $totalMembers
                ],
                'current_month' => [
                    'month' => $currentMonth,
                    'year' => $currentYear,
                    'paid_count' => $paidCount,
                    'unpaid_count' => $unpaidCount,
                    'payment_percentage' => $paymentPercentage
                ]
            ];

            return response()->json([
                'success' => true,
                'data' => $summary
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error retrieving kas summary',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get staff overview for staff dashboard
     */
    public function getStaffOverview(Request $request)
    {
        try {
            $user = Auth::user();
            $staffRole = $user->userEschoolRoles()->where('role', 'supervisor')->first();
            
            if (!$staffRole) {
                return response()->json([
                    'message' => 'User is not a staff member',
                    'error' => 'Access denied'
                ], 403);
            }

            // Get user's school
            $schoolId = $staffRole->eschool ? $staffRole->eschool->school_id : null;
            
            if (!$schoolId) {
                return response()->json([
                    'message' => 'School not found for staff member',
                    'error' => 'Invalid data'
                ], 400);
            }

            // Get all eschools in this school
            $eschools = Eschool::where('school_id', $schoolId)->with(['userEschoolRoles.user.profile'])->get();

            $totalEschools = $eschools->count();
            $totalMembers = 0;
            $totalAttendanceRate = 0;
            $totalCollectionRate = 0;
            $eschoolBreakdown = [];

            foreach ($eschools as $eschool) {
                // Get members for this eschool
                $members = $eschool->userEschoolRoles()->whereIn('role', ['member', 'treasurer'])->get();
                $memberCount = $members->count();
                $totalMembers += $memberCount;

                // Get coordinator and treasurer
                $coordinator = $eschool->userEschoolRoles()->where('role', 'coordinator')->with('user.profile')->first();
                $treasurer = $eschool->userEschoolRoles()->where('role', 'treasurer')->with('user.profile')->first();

                // Calculate attendance rate
                $attendanceRecords = AttendanceRecord::whereIn('user_eschool_role_id', $members->pluck('id'))->get();
                $totalAttendance = $attendanceRecords->count();
                $presentAttendance = $attendanceRecords->where('status', 'present')->count();
                $attendanceRate = $totalAttendance > 0 ? round(($presentAttendance / $totalAttendance) * 100, 2) : 0;
                $totalAttendanceRate += $attendanceRate;

                // Calculate kas collection rate
                $kasRecords = KasRecord::where('eschool_id', $eschool->id)->where('category', 'income')->get();
                $totalKasCollected = $kasRecords->sum('amount');
                $expectedKas = $memberCount * $eschool->monthly_fee_amount * 12; // Assuming yearly calculation
                $kasCollectionRate = $expectedKas > 0 ? round(($totalKasCollected / $expectedKas) * 100, 2) : 0;
                $totalCollectionRate += $kasCollectionRate;

                $eschoolBreakdown[] = [
                    'eschool_name' => $eschool->name,
                    'coordinator_name' => $coordinator ? $coordinator->user->profile->name : 'Not assigned',
                    'treasurer_name' => $treasurer ? $treasurer->user->profile->name : 'Not assigned',
                    'total_members' => $memberCount,
                    'attendance_rate' => $attendanceRate,
                    'kas_collection_rate' => $kasCollectionRate,
                    'total_kas_collected' => $totalKasCollected
                ];
            }

            $averageAttendanceRate = $totalEschools > 0 ? round($totalAttendanceRate / $totalEschools, 2) : 0;
            $overallCollectionRate = $totalEschools > 0 ? round($totalCollectionRate / $totalEschools, 2) : 0;

            $overview = [
                'overview_statistics' => [
                    'total_eschools' => $totalEschools,
                    'total_members' => $totalMembers,
                    'average_attendance_rate' => $averageAttendanceRate,
                    'overall_collection_rate' => $overallCollectionRate
                ],
                'eschool_breakdown' => $eschoolBreakdown
            ];

            return response()->json([
                'success' => true,
                'data' => $overview
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error retrieving staff overview',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Helper methods

    private function getKasSummaryForRole($userEschoolRole)
    {
        $eschoolId = $userEschoolRole->eschool_id;
        
        if ($userEschoolRole->role === 'treasurer') {
            // For treasurer, get overall kas data
            $kasRecords = KasRecord::where('eschool_id', $eschoolId)->get();
            $totalIncome = $kasRecords->where('category', 'income')->sum('amount');
            $totalExpense = $kasRecords->where('category', 'expense')->sum('amount');
            $balance = $totalIncome - $totalExpense;

            $memberCount = UserEschoolRole::where('eschool_id', $eschoolId)
                ->whereIn('role', ['member', 'treasurer'])
                ->count();

            $monthlyTarget = $memberCount * ($userEschoolRole->eschool->monthly_fee_amount ?? 0);
            $collectionRate = $monthlyTarget > 0 ? round(($totalIncome / $monthlyTarget) * 100, 2) : 0;

            return [
                'total_balance' => $balance,
                'monthly_target' => $monthlyTarget,
                'collection_rate' => $collectionRate,
                'pending_approvals' => 0, // Placeholder
                'payment_status' => 'up_to_date' // Placeholder
            ];
        } else {
            // For member, get personal payment data
            $personalPayments = KasPayment::where('member_id', $userEschoolRole->id)->get();
            $personalBalance = $personalPayments->where('is_paid', true)->sum('amount');
            $monthlyTarget = $userEschoolRole->eschool->monthly_fee_amount ?? 0;

            return [
                'personal_balance' => $personalBalance,
                'monthly_target' => $monthlyTarget,
                'collection_rate' => $monthlyTarget > 0 ? round(($personalBalance / $monthlyTarget) * 100, 2) : 0,
                'payment_status' => $personalBalance >= $monthlyTarget ? 'up_to_date' : 'overdue'
            ];
        }
    }

    private function getPermissionsForRole($role)
    {
        switch ($role) {
            case 'supervisor':
                return ['view_all_eschools', 'manage_members', 'view_reports', 'export_data'];
            case 'coordinator':
                return ['manage_attendance', 'view_members', 'export_attendance'];
            case 'treasurer':
                return ['manage_kas', 'view_payments', 'export_kas', 'approve_expenses'];
            case 'member':
                return ['view_own_data', 'make_payments'];
            default:
                return [];
        }
    }

    private function mapRoleToIndonesian($role)
    {
        $mapping = [
            'supervisor' => 'Staff',
            'coordinator' => 'Koordinator',
            'treasurer' => 'Bendahara',
            'member' => 'Member'
        ];

        return $mapping[$role] ?? $role;
    }

    private function getUserPrimaryRole($userEschoolRoles)
    {
        // Priority order: supervisor > coordinator > treasurer > member
        if ($userEschoolRoles->contains('role', 'supervisor')) {
            return 'staff';
        }
        
        if ($userEschoolRoles->contains('role', 'coordinator')) {
            return 'koordinator';
        }
        
        if ($userEschoolRoles->contains('role', 'treasurer')) {
            return 'bendahara';
        }
        
        return 'siswa';
    }

    private function calculateActivityScore($attendanceRate, $kasContribution, $totalEschools)
    {
        // Simple activity score calculation
        $attendanceScore = $attendanceRate * 0.4; // 40% weight
        $kasScore = min(($kasContribution / 100000) * 30, 30); // 30% weight, max 30 points
        $participationScore = min($totalEschools * 10, 30); // 30% weight, max 30 points

        return round($attendanceScore + $kasScore + $participationScore, 2);
    }

    private function getRecentActivities($userId)
    {
        $activities = [];

        // Get recent attendance records
        $recentAttendance = AttendanceRecord::whereHas('userEschoolRole', function ($query) use ($userId) {
            $query->where('user_id', $userId);
        })
        ->with(['userEschoolRole.eschool', 'userEschoolRole'])
        ->orderBy('created_at', 'desc')
        ->take(5)
        ->get();

        foreach ($recentAttendance as $attendance) {
            $activities[] = [
                'type' => 'attendance',
                'eschool_name' => $attendance->userEschoolRole->eschool->name,
                'description' => 'Attendance recorded: ' . ucfirst($attendance->status),
                'date' => $attendance->created_at->toISOString(),
                'role_context' => $this->mapRoleToIndonesian($attendance->userEschoolRole->role)
            ];
        }

        // Get recent kas payments
        $recentKasPayments = KasPayment::whereHas('member', function ($query) use ($userId) {
            $query->where('user_id', $userId);
        })
        ->with(['member.eschool', 'member'])
        ->orderBy('created_at', 'desc')
        ->take(5)
        ->get();

        foreach ($recentKasPayments as $payment) {
            $activities[] = [
                'type' => 'kas_transaction',
                'eschool_name' => $payment->member->eschool->name,
                'description' => 'Kas payment: ' . ($payment->is_paid ? 'Paid' : 'Pending'),
                'amount' => $payment->amount,
                'date' => $payment->created_at->toISOString(),
                'role_context' => $this->mapRoleToIndonesian($payment->member->role)
            ];
        }

        // Sort by date and return latest 10
        usort($activities, function ($a, $b) {
            return strtotime($b['date']) - strtotime($a['date']);
        });

        return array_slice($activities, 0, 10);
    }
}