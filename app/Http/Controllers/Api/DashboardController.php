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
            $totalMembers = 0;
            $totalKasManaged = 0;
            $totalPersonalKas = 0;
            $totalAttendanceRate = 0;
            $roleCount = [
                'koordinator' => 0,
                'bendahara' => 0,
                'member' => 0
            ];

            foreach ($userEschoolRoles as $userEschoolRole) {
                // Handle supervisor role (no eschool_id)
                if ($userEschoolRole->role === 'supervisor') {
                    // For supervisor, get all eschools in their school
                    $teacher = $user->teacher;
                    if (!$teacher) continue;
                    
                    $schoolEschools = Eschool::where('school_id', $teacher->school_id)->get();
                    
                    foreach ($schoolEschools as $eschool) {
                        $school = $eschool->school;

                        // For supervisor, get overview data for each eschool
                        $members = $eschool->userEschoolRoles()->whereIn('role', ['member', 'treasurer'])->get();
                        $memberCount = $members->count();
                        $totalMembers += $memberCount;

                        // Calculate attendance rate for this eschool
                        $attendanceRecords = AttendanceRecord::whereIn('user_eschool_role_id', $members->pluck('id'))->get();
                        $totalMeetings = $attendanceRecords->count();
                        $attendedMeetings = $attendanceRecords->where('status', 'present')->count();
                        $attendanceRate = $totalMeetings > 0 ? round(($attendedMeetings / $totalMeetings) * 100, 2) : 0;
                        $totalAttendanceRate += $attendanceRate;

                        // Supervisor tidak boleh melihat data kas

                        // Get coordinator and treasurer info
                        $coordinator = $eschool->userEschoolRoles()->where('role', 'coordinator')->with('user.profile')->first();
                        $treasurer = $eschool->userEschoolRoles()->where('role', 'treasurer')->with('user.profile')->first();

                        $eschoolRoles[] = [
                            'eschool_id' => $eschool->id,
                            'eschool_name' => $eschool->name,
                            'school_id' => $school->id,
                            'role_in_eschool' => 'Staff',
                            'permissions' => $this->getPermissionsForRole('supervisor'),
                            'assigned_at' => $userEschoolRole->created_at->toISOString(),
                            'status' => 'active',
                            'coordinator_name' => $coordinator ? $coordinator->user->profile->name : 'Not assigned',
                            'treasurer_name' => $treasurer ? $treasurer->user->profile->name : 'Not assigned',
                            'total_members' => $memberCount,
                            'attendance_rate' => $attendanceRate,
                            // Supervisor tidak boleh melihat data kas
                            'attendance_summary' => [
                                'total_meetings' => $totalMeetings,
                                'attended' => $attendedMeetings,
                                'attendance_rate' => $attendanceRate
                            ]
                        ];

                        $totalEschools++;
                    }
                    
                    // Count supervisor role
                    $roleCount['koordinator']++; // Using koordinator as staff representation
                    continue;
                }
                
                // Handle other roles (coordinator, treasurer, member)
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
            
            // Check if user is supervisor
            $isSupervisor = $userEschoolRoles->contains('role', 'supervisor');
            
            // Calculate activity score (exclude kas data for supervisor)
            $overallActivityScore = $isSupervisor 
                ? $this->calculateActivityScore($avgAttendanceRate, 0, $totalEschools)
                : $this->calculateActivityScore($avgAttendanceRate, $totalPersonalKas, $totalEschools);

            // Get recent activities (exclude kas activities for supervisor)
            $recentActivities = $this->getRecentActivities($user->id, $isSupervisor);

            // Prepare performance data (exclude kas data for supervisor)
            $performanceData = [
                'avg_attendance_rate' => $avgAttendanceRate,
                'overall_activity_score' => $overallActivityScore
            ];
            
            if (!$isSupervisor) {
                $performanceData['total_kas_managed'] = $totalKasManaged;
                $performanceData['total_personal_kas'] = $totalPersonalKas;
            }

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
                    'total_members' => $totalMembers,
                    'roles' => $roleCount,
                    'performance' => $performanceData
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
                ->whereDate('date', $today)
                ->get();
            
            $todayPresent = $todayAttendance->where('is_present', true)->count();
            $todayTotal = $todayAttendance->count();
            $todayPercentage = $todayTotal > 0 ? round(($todayPresent / $todayTotal) * 100, 2) : 0;

            // This week's statistics
            $weekAttendance = AttendanceRecord::whereIn('user_eschool_role_id', $memberRoles->pluck('id'))
                ->where('date', '>=', $weekStart)
                ->get();
            
            $weekPresent = $weekAttendance->where('is_present', true)->count();
            $weekTotal = $weekAttendance->count();
            $weekPercentage = $weekTotal > 0 ? round(($weekPresent / $weekTotal) * 100, 2) : 0;

            // This month's statistics
            $monthAttendance = AttendanceRecord::whereIn('user_eschool_role_id', $memberRoles->pluck('id'))
                ->where('date', '>=', $monthStart)
                ->get();
            
            $monthPresent = $monthAttendance->where('is_present', true)->count();
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
            
            // Check if user is coordinator or supervisor
            $coordinatorRole = $user->userEschoolRoles()->where('role', 'coordinator')->first();
            $isSupervisor = $user->userEschoolRoles()->where('role', 'supervisor')->exists();
            
            // For supervisors, get all eschools in their school
            if ($isSupervisor) {
                $teacher = $user->teacher;
                if (!$teacher) {
                    return response()->json([
                        'message' => 'User is not a teacher',
                        'error' => 'Access denied'
                    ], 403);
                }
                
                $eschools = Eschool::where('school_id', $teacher->school_id)->get();
                if ($eschools->isEmpty()) {
                    return response()->json([
                        'message' => 'No eschools found for this supervisor',
                        'data' => [
                            'overall' => [
                                'total_members' => 0,
                                'total_present' => 0,
                                'total_possible' => 0,
                                'attendance_rate' => 0
                            ],
                            'daily_summary' => [],
                            'member_attendance' => [],
                            'weekday_analysis' => []
                        ]
                    ], 200);
                }
            }
            // For coordinators, get their specific eschool
            else if ($coordinatorRole) {
                $eschools = Eschool::where('id', $coordinatorRole->eschool_id)->get();
            }
            // Neither coordinator nor supervisor
            else {
                return response()->json([
                    'message' => 'User is not authorized to access attendance analytics',
                    'error' => 'Access denied'
                ], 403);
            }

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

            // For supervisors, we need to collect data from all eschools
            $allDailySummary = [];
            $allMemberAttendance = [];
            $allWeekdayAnalysis = [];
            $totalMembers = 0;
            $totalPresent = 0;
            $totalPossible = 0;

            foreach ($eschools as $eschool) {
                // Get all members of this eschool
                $memberRoles = UserEschoolRole::where('eschool_id', $eschool->id)
                    ->whereIn('role', ['member', 'treasurer'])
                    ->with('user.profile')
                    ->get();

                $totalMembers += $memberRoles->count();

                // Overall statistics for this eschool
                $allAttendance = AttendanceRecord::whereIn('user_eschool_role_id', $memberRoles->pluck('id'))
                    ->where('created_at', '>=', $startDate)
                    ->get();

                $eschoolPresent = $allAttendance->where('is_present', true)->count();
                $eschoolPossible = $allAttendance->count();
                
                $totalPresent += $eschoolPresent;
                $totalPossible += $eschoolPossible;

                // Daily summary for this eschool
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

                // Add to all daily summaries (we'll aggregate later)
                foreach ($dailySummary as $day) {
                    $dateKey = $day['date'] instanceof \DateTimeInterface ? $day['date']->format('Y-m-d') : (string) $day['date'];
                    if (!isset($allDailySummary[$dateKey])) {
                        $allDailySummary[$dateKey] = [
                            'date' => $day['date'],
                            'formatted_date' => $day['formatted_date'],
                            'present' => 0,
                            'absent' => 0,
                            'total' => 0
                        ];
                    }
                    $allDailySummary[$dateKey]['present'] += $day['present'];
                    $allDailySummary[$dateKey]['absent'] += $day['absent'];
                    $allDailySummary[$dateKey]['total'] += $day['total'];
                }

                // Member attendance rates for this eschool
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
                });

                // Add to all member attendance
                $allMemberAttendance = array_merge($allMemberAttendance, $memberAttendance->toArray());

                // Weekday analysis for this eschool
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
                        $days = [0 => '', 1 => 'Sunday', 2 => 'Monday', 3 => 'Tuesday', 4 => 'Wednesday', 5 => 'Thursday', 6 => 'Friday', 7 => 'Saturday'];
                        $shortDays = [0 => '', 1 => 'Sun', 2 => 'Mon', 3 => 'Tue', 4 => 'Wed', 5 => 'Thu', 6 => 'Fri', 7 => 'Sat'];
                        
                        return [
                            'day_of_week' => $days[$item->day_of_week] ?? '',
                            'short_day' => $shortDays[$item->day_of_week] ?? '',
                            'average_attendance_rate' => round($item->average_attendance_rate, 2)
                        ];
                    });

                // Add to all weekday analysis (we'll average later)
                foreach ($weekdayAnalysis as $dayAnalysis) {
                    $dayKey = (string) $dayAnalysis['day_of_week'];
                    if (!isset($allWeekdayAnalysis[$dayKey])) {
                        $allWeekdayAnalysis[$dayKey] = [
                            'day_of_week' => $dayAnalysis['day_of_week'],
                            'short_day' => $dayAnalysis['short_day'],
                            'average_attendance_rate' => 0,
                            'count' => 0
                        ];
                    }
                    $allWeekdayAnalysis[$dayKey]['average_attendance_rate'] += $dayAnalysis['average_attendance_rate'];
                    $allWeekdayAnalysis[$dayKey]['count']++;
                }
            }

            // Calculate overall attendance rate
            $attendanceRate = $totalPossible > 0 ? round(($totalPresent / $totalPossible) * 100, 2) : 0;

            // Convert daily summary to array and sort by date
            $dailySummaryArray = array_values($allDailySummary);
            usort($dailySummaryArray, function($a, $b) {
                return strtotime($a['date']) - strtotime($b['date']);
            });

            // Calculate average attendance rates for members
            // Group by member name and calculate average rates
            $groupedMemberAttendance = [];
            foreach ($allMemberAttendance as $member) {
                $name = $member['name'];
                if (!isset($groupedMemberAttendance[$name])) {
                    $groupedMemberAttendance[$name] = [
                        'name' => $name,
                        'total_present' => 0,
                        'total_possible' => 0,
                        'count' => 0
                    ];
                }
                $groupedMemberAttendance[$name]['total_present'] += $member['present'];
                $groupedMemberAttendance[$name]['total_possible'] += $member['total'];
                $groupedMemberAttendance[$name]['count']++;
            }

            $finalMemberAttendance = array_map(function($member) {
                $avgRate = $member['total_possible'] > 0 ? 
                    round(($member['total_present'] / $member['total_possible']) * 100, 2) : 0;
                return [
                    'name' => $member['name'],
                    'attendance_rate' => $avgRate,
                    'present' => $member['total_present'],
                    'total' => $member['total_possible']
                ];
            }, $groupedMemberAttendance);

            // Sort by attendance rate descending
            usort($finalMemberAttendance, function($a, $b) {
                return $b['attendance_rate'] <=> $a['attendance_rate'];
            });

            // Calculate average weekday analysis
            $finalWeekdayAnalysis = array_map(function($day) {
                return [
                    'day_of_week' => $day['day_of_week'],
                    'short_day' => $day['short_day'],
                    'average_attendance_rate' => $day['count'] > 0 ? 
                        round($day['average_attendance_rate'] / $day['count'], 2) : 0
                ];
            }, array_values($allWeekdayAnalysis));

            // Sort by day of week order
            $dayOrder = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
            usort($finalWeekdayAnalysis, function($a, $b) use ($dayOrder) {
                $indexA = array_search($a['day_of_week'], $dayOrder);
                $indexB = array_search($b['day_of_week'], $dayOrder);
                // Handle case where day might not be found in the array
                if ($indexA === false) $indexA = 999;
                if ($indexB === false) $indexB = 999;
                return $indexA <=> $indexB;
            });

            $analytics = [
                'overall' => [
                    'total_members' => $totalMembers,
                    'total_present' => $totalPresent,
                    'total_possible' => $totalPossible,
                    'attendance_rate' => $attendanceRate
                ],
                'daily_summary' => array_values($dailySummaryArray),
                'member_attendance' => array_slice(array_values($finalMemberAttendance), 0, 10), // Top 10 members
                'weekday_analysis' => array_values($finalWeekdayAnalysis)
            ];

            return response()->json([
                'success' => true,
                'data' => $analytics
            ], 200);

        } catch (\Exception $e) {
            \Log::error('Error in getAttendanceAnalytics: ' . $e->getMessage() . ' in ' . $e->getFile() . ' on line ' . $e->getLine());
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
                return ['view_all_eschools', 'view_attendance', 'view_reports', 'export_attendance_data'];
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

    private function getRecentActivities($userId, $isSupervisor = false)
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

        // Get recent kas payments (only if not supervisor)
        if (!$isSupervisor) {
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
        }

        // Sort by date and return latest 10
        usort($activities, function ($a, $b) {
            return strtotime($b['date']) - strtotime($a['date']);
        });

        return array_slice($activities, 0, 10);
    }

    /**
     * Get eschool analytics data
     */
    public function getEschoolAnalytics(Request $request)
    {
        try {
            $user = Auth::user();
            $userEschoolRoles = $user->userEschoolRoles;

            // Check if user is supervisor
            $isSupervisor = $userEschoolRoles->contains('role', 'supervisor');

            // Get eschools based on user role
            if ($isSupervisor) {
                // For supervisor, get all eschools in their school
                $teacher = $user->teacher;
                if (!$teacher) {
                    return response()->json([
                        'message' => 'User is not a teacher',
                        'error' => 'Access denied'
                    ], 403);
                }

                $eschools = Eschool::where('school_id', $teacher->school_id)->get();
            } else {
                // For other roles, get only eschools they're associated with
                $eschoolIds = $userEschoolRoles->pluck('eschool_id')->filter();
                $eschools = Eschool::whereIn('id', $eschoolIds)->get();
            }

            // Prepare analytics data
            $totalEschools = $eschools->count();
            $totalMembers = 0;
            $activeEschools = 0;

            foreach ($eschools as $eschool) {
                $memberCount = $eschool->userEschoolRoles()
                    ->whereIn('role', ['member', 'treasurer'])
                    ->count();
                
                $totalMembers += $memberCount;
                
                if ($eschool->is_active) {
                    $activeEschools++;
                }
            }

            $analytics = [
                'total_eschools' => $totalEschools,
                'total_members' => $totalMembers,
                'active_eschools' => $activeEschools,
                'inactive_eschools' => $totalEschools - $activeEschools,
                'avg_members_per_eschool' => $totalEschools > 0 ? round($totalMembers / $totalEschools, 2) : 0
            ];

            return response()->json($analytics, 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error retrieving eschool analytics',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get financial analytics data
     */
    public function getFinancialAnalytics(Request $request)
    {
        try {
            $user = Auth::user();
            \Log::info('Financial API accessed by user: ' . $user->id . ' - ' . $user->email);
            
            $userEschoolRoles = $user->userEschoolRoles;
            \Log::info('User eschool roles: ' . json_encode($userEschoolRoles->toArray()));

            // Check if user is supervisor
            $isSupervisor = $userEschoolRoles->contains('role', 'supervisor');
            \Log::info('Is supervisor: ' . ($isSupervisor ? 'true' : 'false'));

            // Get eschools based on user role
            if ($isSupervisor) {
                // For supervisor, get all eschools in their school
                $teacher = $user->teacher;
                if (!$teacher) {
                    \Log::info('User is not a teacher: ' . $user->id);
                    return response()->json([
                        'message' => 'User is not a teacher',
                        'error' => 'Access denied'
                    ], 403);
                }
                
                \Log::info('Teacher school ID: ' . $teacher->school_id);
                $eschools = Eschool::where('school_id', $teacher->school_id)->get();
                \Log::info('Number of eschools for supervisor: ' . $eschools->count());
            } else {
                // For other roles, get only eschools they're associated with (treasurer or member)
                $eschoolIds = $userEschoolRoles->pluck('eschool_id')->filter();
                \Log::info('Eschool IDs for user: ' . json_encode($eschoolIds->toArray()));
                $eschools = Eschool::whereIn('id', $eschoolIds)->get();
                \Log::info('Number of eschools found: ' . $eschools->count());
            }

            $totalIncome = 0;
            $totalExpense = 0;
            $netBalance = 0;
            $monthlyTrends = [];
            $expenseCategories = [];
            $memberContributions = [];

            // Get financial data for each eschool
            foreach ($eschools as $eschool) {
                \Log::info('Processing eschool: ' . $eschool->id . ' - ' . $eschool->name);
                $kasRecords = KasRecord::where('eschool_id', $eschool->id)->get();
                \Log::info('Kas records found for eschool ' . $eschool->id . ': ' . $kasRecords->count());
                
                $eschoolIncome = $kasRecords->where('category', 'income')->sum('amount');
                $eschoolExpense = $kasRecords->where('category', 'expense')->sum('amount');
                
                $totalIncome += $eschoolIncome;
                $totalExpense += $eschoolExpense;
                
                // Monthly trends (last 6 months)
                $currentMonth = Carbon::now();
                for ($i = 5; $i >= 0; $i--) {
                    $month = $currentMonth->copy()->subMonths($i);
                    $monthKey = $month->format('Y-m');
                    
                    if (!isset($monthlyTrends[$monthKey])) {
                        $monthlyTrends[$monthKey] = [
                            'month' => $month->format('M Y'),
                            'income' => 0,
                            'expense' => 0
                        ];
                    }
                    
                    $monthIncome = $kasRecords
                        ->where('category', 'income')
                        ->whereBetween('date', [
                            $month->startOfMonth()->format('Y-m-d'),
                            $month->endOfMonth()->format('Y-m-d')
                        ])
                        ->sum('amount');
                        
                    $monthExpense = $kasRecords
                        ->where('category', 'expense')
                        ->whereBetween('date', [
                            $month->startOfMonth()->format('Y-m-d'),
                            $month->endOfMonth()->format('Y-m-d')
                        ])
                        ->sum('amount');
                        
                    $monthlyTrends[$monthKey]['income'] += $monthIncome;
                    $monthlyTrends[$monthKey]['expense'] += $monthExpense;
                }
                
                // Expense categories
                $expenses = $kasRecords->where('category', 'expense');
                foreach ($expenses as $expense) {
                    $category = $expense->description;
                    if (!isset($expenseCategories[$category])) {
                        $expenseCategories[$category] = 0;
                    }
                    $expenseCategories[$category] += $expense->amount;
                }
            }
            
            $netBalance = $totalIncome - $totalExpense;
            
            // Format monthly trends for response
            $formattedMonthlyTrends = array_values($monthlyTrends);
            
            // Format expense categories for response (top 5)
            arsort($expenseCategories);
            $topExpenseCategories = array_slice($expenseCategories, 0, 5, true);
            $formattedExpenseCategories = [];
            foreach ($topExpenseCategories as $category => $amount) {
                $formattedExpenseCategories[] = [
                    'category' => $category,
                    'amount' => $amount
                ];
            }
            
            // Member contributions (simplified)
            $memberContributions = [
                'total_contributions' => $totalIncome,
                'avg_contribution_per_member' => $totalIncome > 0 ? round($totalIncome / count($eschools), 2) : 0
            ];

            $analytics = [
                'totalIncome' => $totalIncome,
                'totalExpense' => $totalExpense,
                'netBalance' => $netBalance,
                'monthlyTrends' => $formattedMonthlyTrends,
                'expenseCategories' => $formattedExpenseCategories,
                'memberContributions' => $memberContributions
            ];

            \Log::info('Financial analytics response: ' . json_encode($analytics));
            return response()->json($analytics, 200);

        } catch (\Exception $e) {
            \Log::error('Error in getFinancialAnalytics: ' . $e->getMessage() . ' in ' . $e->getFile() . ' on line ' . $e->getLine());
            return response()->json([
                'message' => 'Error retrieving financial analytics',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get attendance analytics data
     */
    public function getAttendanceAnalyticsData(Request $request)
    {
        try {
            $user = Auth::user();
            $userEschoolRoles = $user->userEschoolRoles;

            // Check if user is supervisor
            $isSupervisor = $userEschoolRoles->contains('role', 'supervisor');

            // Get eschools based on user role
            if ($isSupervisor) {
                // For supervisor, get all eschools in their school
                $teacher = $user->teacher;
                if (!$teacher) {
                    return response()->json([
                        'message' => 'User is not a teacher',
                        'error' => 'Access denied'
                    ], 403);
                }

                $eschools = Eschool::where('school_id', $teacher->school_id)->get();
            } else {
                // For other roles, get only eschools they're associated with
                $eschoolIds = $userEschoolRoles->pluck('eschool_id')->filter();
                $eschools = Eschool::whereIn('id', $eschoolIds)->get();
            }

            $totalAttendanceRecords = 0;
            $totalPresent = 0;
            $totalAbsent = 0;
            $totalLate = 0;
            $averageAttendanceRate = 0;

            // Get attendance data for each eschool
            foreach ($eschools as $eschool) {
                $memberRoles = $eschool->userEschoolRoles()
                    ->whereIn('role', ['member', 'treasurer'])
                    ->pluck('id');
                    
                $attendanceRecords = AttendanceRecord::whereIn('user_eschool_role_id', $memberRoles)->get();
                
                $totalAttendanceRecords += $attendanceRecords->count();
                $totalPresent += $attendanceRecords->where('is_present', true)->count();
                $totalAbsent += $attendanceRecords->where('is_present', false)->count();
                
                // Count late records by checking notes for "terlambat" or "late"
                foreach ($attendanceRecords as $record) {
                    if (!$record->is_present && $record->notes && (stripos($record->notes, 'terlambat') !== false || stripos($record->notes, 'late') !== false)) {
                        $totalLate++;
                    }
                }
            }
            
            if ($totalAttendanceRecords > 0) {
                $averageAttendanceRate = round(($totalPresent / $totalAttendanceRecords) * 100, 2);
            }

            $analytics = [
                'totalRecords' => $totalAttendanceRecords,
                'totalPresent' => $totalPresent,
                'totalAbsent' => $totalAbsent,
                'totalLate' => $totalLate,
                'averageAttendanceRate' => $averageAttendanceRate
            ];

            return response()->json($analytics, 200);

        } catch (\Exception $e) {
            \Log::error('Error retrieving attendance analytics: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error retrieving attendance analytics',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}