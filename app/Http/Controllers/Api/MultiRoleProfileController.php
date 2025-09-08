<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\UserEschoolRole;
use App\Models\Eschool;
use App\Models\AttendanceRecord;
use App\Models\KasRecord;
use App\Models\KasPayment;

class MultiRoleProfileController extends Controller
{
    /**
     * Get multi-role profile data for the authenticated user
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getMultiRoleProfile(Request $request)
    {
        try {
            // Get the authenticated user
            $user = Auth::user();
            
            if (!$user) {
                return response()->json([
                    'message' => 'Unauthenticated',
                    'error' => 'No authenticated user found'
                ], 401);
            }
            
            // Load user with profile
            $user->load('profile');
            
            // Get user's eschool roles with related data
            $userEschoolRoles = UserEschoolRole::where('user_id', $user->id)
                ->with(['eschool', 'eschool.school'])
                ->get();
            
            // Prepare eschool roles data
            $eschoolRoles = [];
            $roleCounts = [
                'koordinator' => 0,
                'bendahara' => 0,
                'member' => 0
            ];
            
            foreach ($userEschoolRoles as $userEschoolRole) {
                // Increment role count
                if (isset($roleCounts[$userEschoolRole->role])) {
                    $roleCounts[$userEschoolRole->role]++;
                }
                
                // Get kas summary for this role
                $kasSummary = $this->getKasSummary($userEschoolRole);
                
                // Get attendance summary for this role
                $attendanceSummary = $this->getAttendanceSummary($userEschoolRole);
                
                // Prepare role data
                $eschoolRoles[] = [
                    'eschool_id' => $userEschoolRole->eschool->id,
                    'eschool_name' => $userEschoolRole->eschool->name,
                    'school_id' => $userEschoolRole->eschool->school->id,
                    'school_name' => $userEschoolRole->eschool->school->name,
                    'role_in_eschool' => $userEschoolRole->role,
                    'permissions' => $this->getRolePermissions($userEschoolRole->role),
                    'assigned_at' => $userEschoolRole->created_at,
                    'status' => $userEschoolRole->eschool->is_active ? 'active' : 'inactive',
                    'kas_summary' => $kasSummary,
                    'attendance_summary' => $attendanceSummary
                ];
            }
            
            // Get recent activities
            $recentActivities = $this->getRecentActivities($user);
            
            // Calculate performance metrics
            $performance = $this->calculatePerformanceMetrics($userEschoolRoles);
            
            // Prepare response data
            $responseData = [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->profile->name ?? $user->name,
                    'email' => $user->email,
                    'base_role' => $this->getUserBaseRole($user),
                    'is_system_admin' => false // For now, we'll set this to false
                ],
                'eschool_roles' => $eschoolRoles,
                'overall_summary' => [
                    'total_eschools' => count($eschoolRoles),
                    'roles' => $roleCounts,
                    'performance' => $performance
                ],
                'recent_activities' => $recentActivities
            ];
            
            return response()->json($responseData, 200);
            
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error retrieving multi-role profile data',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Get permissions based on role
     *
     * @param string $role
     * @return array
     */
    private function getRolePermissions($role)
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
                return ['view_own_data'];
        }
    }
    
    /**
     * Get user's base role
     *
     * @param User $user
     * @return string
     */
    private function getUserBaseRole($user)
    {
        // For now, we'll return a default role
        // In a real implementation, this would be based on the user's profile
        return 'siswa';
    }
    
    /**
     * Get kas summary for a user eschool role
     *
     * @param UserEschoolRole $userEschoolRole
     * @return array
     */
    private function getKasSummary($userEschoolRole)
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
                'pending_approvals' => 0,
                'payment_status' => 'up_to_date'
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
    
    /**
     * Get attendance summary for a user eschool role
     *
     * @param UserEschoolRole $userEschoolRole
     * @return array
     */
    private function getAttendanceSummary($userEschoolRole)
    {
        $attendanceRecords = AttendanceRecord::where('user_eschool_role_id', $userEschoolRole->id)->get();
        
        $totalMeetings = $attendanceRecords->count();
        $attended = $attendanceRecords->where('status', 'present')->count();
        $absent = $attendanceRecords->where('status', 'absent')->count();
        $late = $attendanceRecords->where('status', 'late')->count();
        
        $attendanceRate = $totalMeetings > 0 ? round(($attended / $totalMeetings) * 100, 2) : 0;
        
        return [
            'total_meetings' => $totalMeetings,
            'attended' => $attended,
            'absent' => $absent,
            'late' => $late,
            'attendance_rate' => $attendanceRate
        ];
    }
    
    /**
     * Get recent activities for a user
     *
     * @param User $user
     * @return array
     */
    private function getRecentActivities($user)
    {
        $activities = [];

        // Get recent attendance records
        $recentAttendance = AttendanceRecord::whereHas('userEschoolRole', function ($query) use ($user) {
            $query->where('user_id', $user->id);
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
                'role_context' => $attendance->userEschoolRole->role
            ];
        }

        // Get recent kas payments
        $recentKasPayments = KasPayment::whereHas('member', function ($query) use ($user) {
            $query->where('user_id', $user->id);
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
                'role_context' => $payment->member->role
            ];
        }

        // Sort by date and return latest 10
        usort($activities, function ($a, $b) {
            return strtotime($b['date']) - strtotime($a['date']);
        });

        return array_slice($activities, 0, 10);
    }
    
    /**
     * Calculate performance metrics
     *
     * @param \Illuminate\Database\Eloquent\Collection $userEschoolRoles
     * @return array
     */
    private function calculatePerformanceMetrics($userEschoolRoles)
    {
        $totalAttendanceRate = 0;
        $totalKasManaged = 0;
        $totalPersonalKas = 0;
        $eschoolCount = $userEschoolRoles->count();

        foreach ($userEschoolRoles as $role) {
            // Calculate attendance rate
            $attendanceRecords = AttendanceRecord::where('user_eschool_role_id', $role->id)->get();
            $totalMeetings = $attendanceRecords->count();
            $attended = $attendanceRecords->where('status', 'present')->count();
            $attendanceRate = $totalMeetings > 0 ? ($attended / $totalMeetings) * 100 : 0;
            $totalAttendanceRate += $attendanceRate;

            // Calculate kas data
            if ($role->role === 'treasurer') {
                $kasRecords = KasRecord::where('eschool_id', $role->eschool_id)->get();
                $totalIncome = $kasRecords->where('category', 'income')->sum('amount');
                $totalExpense = $kasRecords->where('category', 'expense')->sum('amount');
                $totalKasManaged += ($totalIncome - $totalExpense);
            }

            // Calculate personal kas
            $personalPayments = KasPayment::where('member_id', $role->id)->where('is_paid', true)->sum('amount');
            $totalPersonalKas += $personalPayments;
        }

        $avgAttendanceRate = $eschoolCount > 0 ? round($totalAttendanceRate / $eschoolCount, 2) : 0;
        
        // Simple activity score calculation
        $activityScore = min(($avgAttendanceRate * 0.4) + (min($totalPersonalKas / 100000, 30)) + (min($eschoolCount * 10, 30)), 100);

        return [
            'avg_attendance_rate' => $avgAttendanceRate,
            'total_kas_managed' => $totalKasManaged,
            'total_personal_kas' => $totalPersonalKas,
            'overall_activity_score' => round($activityScore, 2)
        ];
    }
}