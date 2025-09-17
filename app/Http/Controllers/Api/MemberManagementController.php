<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\UserEschoolRole;
use App\Models\Eschool;

class MemberManagementController extends Controller
{
    /**
     * Get members for management (coordinator/treasurer access)
     */
    public function getMembers(Request $request)
    {
        try {
            $user = Auth::user();
            
            // Get the eschool ID from the request attributes (set by middleware)
            $eschoolId = $request->attributes->get('eschool_id');
            
            if (!$eschoolId) {
                return response()->json([
                    'message' => 'Unauthorized. Required roles for this eschool: coordinator, treasurer'
                ], 403);
            }
            
            // Get pagination parameters
            $page = $request->get('page', 1);
            $perPage = $request->get('per_page', 15);
            $search = $request->get('search', '');
            $roleFilter = $request->get('role_filter', 'all');
            
            // Build query for members
            $query = UserEschoolRole::where('eschool_id', $eschoolId)
                ->with(['user.profile', 'user.student']);
                
            // Apply role filter
            if ($roleFilter !== 'all') {
                $query->where('role', $roleFilter);
            }
            
            // Apply search filter
            if (!empty($search)) {
                $query->whereHas('user.profile', function ($q) use ($search) {
                    $q->where('name', 'like', '%' . $search . '%')
                      ->orWhere('email', 'like', '%' . $search . '%');
                });
            }
            
            // Get paginated results
            $members = $query->paginate($perPage, ['*'], 'page', $page);
            
            // Transform the data
            $transformedMembers = $members->map(function ($userEschoolRole) {
                return [
                    'user_id' => $userEschoolRole->user->id,
                    'name' => $userEschoolRole->user->profile->name,
                    'email' => $userEschoolRole->user->email,
                    'student_id' => $userEschoolRole->user->student->student_id ?? null,
                    'phone' => $userEschoolRole->user->profile->phone ?? null,
                    'role_in_eschool' => $userEschoolRole->role,
                    'permissions' => $this->getRolePermissions($userEschoolRole->role),
                    'status' => $userEschoolRole->user->profile->status,
                    'assigned_at' => $userEschoolRole->created_at->toISOString(),
                    'member_details' => [
                        'gender' => $userEschoolRole->user->profile->gender,
                        'address' => $userEschoolRole->user->profile->address,
                        'date_of_birth' => $userEschoolRole->user->profile->date_of_birth,
                    ],
                    'other_roles' => $userEschoolRole->user->userEschoolRoles()
                        ->where('eschool_id', '!=', $userEschoolRole->eschool_id)
                        ->get()
                        ->map(function ($role) {
                            return [
                                'eschool_id' => $role->eschool_id,
                                'eschool_name' => $role->eschool->name,
                                'role' => $role->role,
                            ];
                        }),
                    'attendance_summary' => $this->getAttendanceSummary($userEschoolRole),
                ];
            });
            
            // Get role summary
            $roleSummary = [
                'coordinator' => UserEschoolRole::where('eschool_id', $eschoolId)->where('role', 'coordinator')->count(),
                'treasurer' => UserEschoolRole::where('eschool_id', $eschoolId)->where('role', 'treasurer')->count(),
                'member' => UserEschoolRole::where('eschool_id', $eschoolId)->where('role', 'member')->count(),
            ];
            
            return response()->json([
                'success' => true,
                'data' => $transformedMembers,
                'role_summary' => $roleSummary,
                'pagination' => [
                    'current_page' => $members->currentPage(),
                    'last_page' => $members->lastPage(),
                    'per_page' => $members->perPage(),
                    'total' => $members->total(),
                ]
            ], 200);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching members',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Get available users for eschool assignment
     */
    public function getAvailableUsers(Request $request)
    {
        try {
            $user = Auth::user();
            
            // Get the eschool ID from the request attributes (set by middleware)
            $eschoolId = $request->attributes->get('eschool_id');
            
            if (!$eschoolId) {
                return response()->json([
                    'message' => 'Unauthorized. Required roles for this eschool: coordinator, treasurer'
                ], 403);
            }
            
            // Get users who are not already assigned to this eschool
            // and who don't have conflicting roles (coordinator/supervisor in other eschools)
            $existingMemberIds = UserEschoolRole::where('eschool_id', $eschoolId)
                ->pluck('user_id')
                ->toArray();
                
            $users = User::whereNotIn('id', $existingMemberIds)
                ->with(['profile', 'student', 'teacher'])
                ->get()
                ->filter(function ($user) {
                    // Filter out users who are already coordinators or supervisors in other eschools
                    $conflictingRoles = $user->userEschoolRoles()
                        ->whereIn('role', ['coordinator', 'supervisor'])
                        ->exists();
                    
                    return !$conflictingRoles;
                })
                ->map(function ($user) {
                    return [
                        'id' => $user->id,
                        'name' => $user->profile->name,
                        'email' => $user->email,
                        'base_role' => $this->getUserBaseRole($user),
                        'current_eschool_roles' => $user->userEschoolRoles->map(function ($role) {
                            return [
                                'eschool_id' => $role->eschool_id,
                                'eschool_name' => $role->eschool->name,
                                'role' => $role->role,
                            ];
                        }),
                        'available_roles' => $this->getAvailableRoles($user),
                        'qwen_compliant' => $this->isQwenCompliant($user),
                        'can_assign_koordinator' => $this->canAssignKoordinator($user),
                    ];
                })
                ->values(); // Convert to array with numeric indices
                
            return response()->json([
                'success' => true,
                'data' => $users
            ], 200);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching available users',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Assign role to user in eschool
     */
    public function assignRole(Request $request)
    {
        try {
            $user = Auth::user();
            
            // Get the eschool ID from the request attributes (set by middleware)
            $eschoolId = $request->attributes->get('eschool_id');
            
            if (!$eschoolId) {
                return response()->json([
                    'message' => 'Unauthorized. Required roles for this eschool: coordinator, treasurer'
                ], 403);
            }
            
            // Validate request
            $request->validate([
                'user_id' => 'required|exists:users,id',
                'role' => 'required|in:member,treasurer',
            ]);
            
            // Check if user is already assigned to this eschool
            $existingRole = UserEschoolRole::where('user_id', $request->user_id)
                ->where('eschool_id', $eschoolId)
                ->first();
                
            if ($existingRole) {
                return response()->json([
                    'success' => false,
                    'message' => 'User is already a member of this eschool'
                ], 400);
            }
            
            // For treasurer role, check if there's already a treasurer
            if ($request->role === 'treasurer') {
                $existingTreasurer = UserEschoolRole::where('eschool_id', $eschoolId)
                    ->where('role', 'treasurer')
                    ->exists();
                
                if ($existingTreasurer) {
                    return response()->json([
                        'success' => false,
                        'message' => 'A treasurer is already assigned to this eschool'
                    ], 400);
                }
            }
            
            // Create the role assignment
            $newRole = UserEschoolRole::create([
                'user_id' => $request->user_id,
                'eschool_id' => $eschoolId,
                'role' => $request->role,
            ]);
            
            // Clear attendance statistics cache for this eschool
            \Illuminate\Support\Facades\Cache::forget("attendance_statistics_{$eschoolId}");
            
            // Load related data
            $newRole->load(['user.profile', 'user.student', 'eschool']);
            
            return response()->json([
                'success' => true,
                'message' => 'Member recruited successfully',
                'data' => [
                    'user_id' => $newRole->user->id,
                    'name' => $newRole->user->profile->name,
                    'email' => $newRole->user->email,
                    'student_id' => $newRole->user->student->student_id ?? null,
                    'role_in_eschool' => $newRole->role,
                    'permissions' => $this->getRolePermissions($newRole->role),
                    'status' => $newRole->user->profile->status,
                    'assigned_at' => $newRole->created_at->toISOString(),
                ]
            ], 201);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error recruiting member',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Update role for user in eschool
     */
    public function updateRole($userId, Request $request)
    {
        try {
            $user = Auth::user();
            
            // Get the eschool ID from the request attributes (set by middleware)
            $eschoolId = $request->attributes->get('eschool_id');
            
            if (!$eschoolId) {
                return response()->json([
                    'message' => 'Unauthorized. Required roles for this eschool: coordinator, treasurer'
                ], 403);
            }
            
            // Validate request
            $request->validate([
                'role' => 'required|in:member,treasurer',
            ]);
            
            // Find the user role in this eschool
            $existingRole = UserEschoolRole::where('user_id', $userId)
                ->where('eschool_id', $eschoolId)
                ->first();
                
            if (!$existingRole) {
                return response()->json([
                    'success' => false,
                    'message' => 'User is not assigned to this eschool'
                ], 404);
            }
            
            // For treasurer role, check if there's already a treasurer (other than this user)
            if ($request->role === 'treasurer') {
                $existingTreasurer = UserEschoolRole::where('eschool_id', $eschoolId)
                    ->where('role', 'treasurer')
                    ->where('user_id', '!=', $userId)
                    ->exists();
                
                if ($existingTreasurer) {
                    return response()->json([
                        'success' => false,
                        'message' => 'A treasurer is already assigned to this eschool'
                    ], 400);
                }
            }
            
            // Update the role
            $existingRole->update([
                'role' => $request->role,
            ]);
            
            // Clear attendance statistics cache for this eschool
            \Illuminate\Support\Facades\Cache::forget("attendance_statistics_{$eschoolId}");
            
            // Load related data
            $existingRole->load(['user.profile', 'user.student', 'eschool']);
            
            return response()->json([
                'success' => true,
                'message' => 'Role updated successfully',
                'data' => [
                    'user_id' => $existingRole->user->id,
                    'name' => $existingRole->user->profile->name,
                    'email' => $existingRole->user->email,
                    'student_id' => $existingRole->user->student->student_id ?? null,
                    'role_in_eschool' => $existingRole->role,
                    'permissions' => $this->getRolePermissions($existingRole->role),
                    'status' => $existingRole->user->profile->status,
                    'assigned_at' => $existingRole->created_at->toISOString(),
                ]
            ], 200);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating role',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Remove role from user in eschool
     */
    public function removeRole($userId)
    {
        try {
            $user = Auth::user();
            
            // Get the eschool ID from the request attributes (set by middleware)
            $eschoolId = request()->attributes->get('eschool_id');
            
            if (!$eschoolId) {
                return response()->json([
                    'message' => 'Unauthorized. Required roles for this eschool: coordinator, treasurer'
                ], 403);
            }
            
            // Find the user role in this eschool
            $existingRole = UserEschoolRole::where('user_id', $userId)
                ->where('eschool_id', $eschoolId)
                ->first();
                
            if (!$existingRole) {
                return response()->json([
                    'success' => false,
                    'message' => 'User is not assigned to this eschool'
                ], 404);
            }
            
            // Prevent removing the last member if there are no other roles
            if ($existingRole->role === 'member') {
                $memberCount = UserEschoolRole::where('eschool_id', $eschoolId)
                    ->whereIn('role', ['member', 'treasurer'])
                    ->count();
                
                if ($memberCount <= 1) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Cannot remove the last member from this eschool'
                    ], 400);
                }
            }
            
            // Delete the role
            $existingRole->delete();
            
            // Clear attendance statistics cache for this eschool
            \Illuminate\Support\Facades\Cache::forget("attendance_statistics_{$eschoolId}");
            
            return response()->json([
                'success' => true,
                'message' => 'Role removed successfully'
            ], 200);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error removing role',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Get role permissions
     */
    private function getRolePermissions($role)
    {
        switch ($role) {
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
     */
    private function getUserBaseRole($user)
    {
        if ($user->teacher) {
            return 'guru';
        } elseif ($user->student) {
            return 'siswa';
        } else {
            return 'staff';
        }
    }
    
    /**
     * Get available roles for a user
     */
    private function getAvailableRoles($user)
    {
        $baseRole = $this->getUserBaseRole($user);
        
        if ($baseRole === 'guru') {
            return ['coordinator'];
        } elseif ($baseRole === 'siswa') {
            return ['member', 'treasurer'];
        } else {
            return ['member'];
        }
    }
    
    /**
     * Check if user is QWEN compliant
     */
    private function isQwenCompliant($user)
    {
        // Check if user has any conflicting roles
        $conflictingRoles = $user->userEschoolRoles()
            ->whereIn('role', ['coordinator', 'supervisor'])
            ->exists();
            
        return !$conflictingRoles;
    }
    
    /**
     * Check if user can be assigned as coordinator
     */
    private function canAssignKoordinator($user)
    {
        $baseRole = $this->getUserBaseRole($user);
        
        // Only teachers can be coordinators
        if ($baseRole !== 'guru') {
            return false;
        }
        
        // Check if user is already a coordinator or supervisor
        $conflictingRoles = $user->userEschoolRoles()
            ->whereIn('role', ['coordinator', 'supervisor'])
            ->exists();
            
        return !$conflictingRoles;
    }
    
    /**
     * Get attendance summary for a user eschool role
     */
    private function getAttendanceSummary($userEschoolRole)
    {
        $attendanceRecords = \App\Models\AttendanceRecord::where('user_eschool_role_id', $userEschoolRole->id)->get();
        
        $totalSessions = $attendanceRecords->count();
        $attended = $attendanceRecords->where('status', 'present')->count();
        $attendanceRate = $totalSessions > 0 ? round(($attended / $totalSessions) * 100, 2) : 0;
        
        return [
            'total_sessions' => $totalSessions,
            'attended' => $attended,
            'attendance_rate' => $attendanceRate,
        ];
    }
}