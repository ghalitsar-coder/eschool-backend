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
        $permissions = [
            'member' => [
                'view_attendance' => true,
                'view_kas' => true,
                'pay_kas' => true,
                'view_activities' => true
            ],
            'bendahara' => [
                'view_attendance' => true,
                'manage_kas' => true,
                'view_kas' => true,
                'record_payments' => true,
                'view_activities' => true
            ],
            'koordinator' => [
                'manage_attendance' => true,
                'view_kas' => true,
                'manage_eschool' => true,
                'view_activities' => true
            ]
        ];
        
        return $permissions[$role] ?? $permissions['member'];
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
        // For now, we'll return placeholder data
        // In a real implementation, this would query the database for actual kas data
        return [
            'total_managed' => 0,
            'total_paid' => 0,
            'outstanding_balance' => 0
        ];
    }
    
    /**
     * Get attendance summary for a user eschool role
     *
     * @param UserEschoolRole $userEschoolRole
     * @return array
     */
    private function getAttendanceSummary($userEschoolRole)
    {
        // For now, we'll return placeholder data
        // In a real implementation, this would query the database for actual attendance data
        return [
            'total_sessions' => 0,
            'present' => 0,
            'absent' => 0,
            'late' => 0,
            'attendance_rate' => 0
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
        // For now, we'll return placeholder data
        // In a real implementation, this would query the database for actual activity data
        return [
            [
                'type' => 'attendance',
                'eschool_name' => 'Basketball',
                'description' => 'Attendance recorded',
                'date' => now()->toDateString(),
                'role_context' => 'member'
            ]
        ];
    }
    
    /**
     * Calculate performance metrics
     *
     * @param \Illuminate\Database\Eloquent\Collection $userEschoolRoles
     * @return array
     */
    private function calculatePerformanceMetrics($userEschoolRoles)
    {
        // For now, we'll return placeholder data
        // In a real implementation, this would calculate actual performance metrics
        return [
            'avg_attendance_rate' => 0,
            'total_kas_managed' => 0,
            'total_personal_kas' => 0,
            'overall_activity_score' => 0
        ];
    }
}