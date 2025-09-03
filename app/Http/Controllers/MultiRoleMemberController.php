<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Eschool;
use App\Models\UserEschoolRole;
use App\Models\Member;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class MultiRoleMemberController extends Controller
{
    /**
     * Display a listing of members with pagination and search for management
     * GET /api/eschool/{eschool_id}/members/manage?page=1&per_page=15&search=&role_filter=all
     */
    public function index(Request $request, $eschoolId): JsonResponse
    {
        try {
            // Validate that eschool exists
            $eschool = Eschool::findOrFail($eschoolId);
            
            // Get query parameters
            $page = $request->input('page', 1);
            $perPage = $request->input('per_page', 15);
            $search = $request->input('search');
            $roleFilter = $request->input('role_filter', 'all');
            
            // Build query for users with roles in this eschool
            $query = User::whereHas('eschoolRoles', function ($q) use ($eschoolId, $roleFilter) {
                $q->where('eschool_id', $eschoolId)
                  ->where('status', 'active');
                  
                // Apply role filter if specified
                if ($roleFilter !== 'all') {
                    $q->where('role', $roleFilter);
                }
            })->with([
                'eschoolRoles' => function ($q) use ($eschoolId) {
                    $q->where('eschool_id', $eschoolId);
                },
                'member' // Get member details if user has member record
            ]);
            
            // Apply search filter
            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%")
                      ->orWhereHas('member', function ($memberQuery) use ($search) {
                          $memberQuery->where('student_id', 'like', "%{$search}%");
                      });
                });
            }
            
            // Paginate results
            $users = $query->paginate($perPage);
            
            // Transform data to match required response format
            $membersList = $users->getCollection()->map(function ($user) use ($eschoolId) {
                $roleInEschool = $user->eschoolRoles->first();
                $memberData = $user->member;
                
                // Get attendance summary for this user in this eschool
                $attendanceRecords = \App\Models\AttendanceRecord::whereHas('member', function ($q) use ($user) {
                    $q->where('user_id', $user->id);
                })->where('eschool_id', $eschoolId)->get();
                
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
                    'current_page' => $users->currentPage(),
                    'last_page' => $users->lastPage(),
                    'per_page' => $users->perPage(),
                    'total' => $users->total()
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
     * Get available users for assignment to this eschool
     * GET /api/eschool/{eschool_id}/users/available-for-eschool
     */
    public function getAvailableUsers(Request $request, $eschoolId): JsonResponse
    {
        try {
            // Validate that eschool exists
            $eschool = Eschool::findOrFail($eschoolId);
            
            // Get all users who can be assigned to an eschool (siswa base role)
            // Filter to only include users from the same school
            $usersQuery = User::where('base_role', 'siswa')
                ->where('school_id', $eschool->school_id);
                
            // Filter out users who already have a role in this eschool
            $existingUserIds = UserEschoolRole::where('eschool_id', $eschoolId)
                ->pluck('user_id');
                
            // Filter out users who are already bendahara in any other active eschool
            $bendaharaUserIds = UserEschoolRole::where('role', 'bendahara')
                ->where('status', 'active')
                ->pluck('user_id');
                
            // Filter out users who are already koordinator in any other active eschool
            $koordinatorUserIds = UserEschoolRole::where('role', 'koordinator')
                ->where('status', 'active')
                ->pluck('user_id');
                
            // Combine all user IDs that should be excluded
            $excludedUserIds = collect([$existingUserIds, $bendaharaUserIds, $koordinatorUserIds])
                ->flatten()
                ->unique()
                ->toArray();
                
            $users = $usersQuery->whereNotIn('id', $excludedUserIds)
                ->get();
            
            // Enhance user data with their current roles
            $availableUsers = $users->map(function ($user) use ($eschoolId) {
                // Get user's current eschool roles
                $currentRoles = $user->eschoolRoles()->with('eschool:id,name')->get();
                
                // Determine available roles for this user
                $availableRoles = ['member']; // Default available role
                
                // Check if user can be bendahara (should always be true now since we filtered them out)
                $isBendaharaInAnyEschool = $user->eschoolRoles()
                    ->where('role', 'bendahara')
                    ->where('status', 'active')
                    ->exists();
                    
                if (!$isBendaharaInAnyEschool) {
                    $availableRoles[] = 'bendahara';
                }
                
                // Check if user can be koordinator (should always be true now since we filtered them out)
                $isKoordinatorInAnyEschool = $user->eschoolRoles()
                    ->where('role', 'koordinator')
                    ->where('status', 'active')
                    ->exists();
                    
                // Qwen compliance check (should always be true now)
                $qwenCompliant = !$isKoordinatorInAnyEschool && !$isBendaharaInAnyEschool;
                
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'base_role' => $user->base_role,
                    'current_eschool_roles' => $currentRoles->map(function ($role) {
                        return [
                            'eschool_id' => $role->eschool_id,
                            'eschool_name' => $role->eschool->name ?? 'Unknown',
                            'role' => $role->role
                        ];
                    }),
                    'available_roles' => $availableRoles,
                    'qwen_compliant' => $qwenCompliant,
                    'can_assign_koordinator' => !$isKoordinatorInAnyEschool
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $availableUsers,
                'message' => 'Available users retrieved successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve available users: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Assign role to user in this eschool
     * POST /api/eschool/{eschool_id}/members/assign-role
     */
    public function assignRole(Request $request, $eschoolId): JsonResponse
    {
        try {
            // Validate that eschool exists
            $eschool = Eschool::findOrFail($eschoolId);
            
            // Validate request
            $validator = Validator::make($request->all(), [
                'user_id' => 'required|exists:users,id',
                'role' => 'required|in:member,bendahara,koordinator',
                'member_details' => 'nullable|array',
                'member_details.student_id' => 'required_unless:role,koordinator|string|max:255',
                'member_details.date_of_birth' => 'nullable|date',
                'member_details.gender' => 'nullable|string|in:L,P',
                'member_details.address' => 'nullable|string',
                'member_details.phone' => 'nullable|string|max:20',
                'create_user_if_not_exists' => 'boolean'
            ]);
            
            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }
            
            $userId = $request->input('user_id');
            $role = $request->input('role');
            $memberDetails = $request->input('member_details', []);
            $createUserIfNotExists = $request->input('create_user_if_not_exists', false);
            
            // Check if user already has a role in this eschool
            $existingRole = UserEschoolRole::where('user_id', $userId)
                ->where('eschool_id', $eschoolId)
                ->first();
                
            if ($existingRole) {
                return response()->json([
                    'success' => false,
                    'message' => 'User already has a role in this eschool'
                ], 400);
            }
            
            // Check Qwen compliance for role assignment
            if ($role === 'bendahara') {
                // Check if user is already bendahara in another active eschool
                $isBendaharaInAnyEschool = UserEschoolRole::where('user_id', $userId)
                    ->where('role', 'bendahara')
                    ->where('status', 'active')
                    ->exists();
                    
                if ($isBendaharaInAnyEschool) {
                    return response()->json([
                        'success' => false,
                        'message' => 'User is already bendahara in another eschool'
                    ], 400);
                }
            } elseif ($role === 'koordinator') {
                // Check if user is already koordinator in another active eschool
                $isKoordinatorInAnyEschool = UserEschoolRole::where('user_id', $userId)
                    ->where('role', 'koordinator')
                    ->where('status', 'active')
                    ->exists();
                    
                if ($isKoordinatorInAnyEschool) {
                    return response()->json([
                        'success' => false,
                        'message' => 'User is already koordinator in another eschool'
                    ], 400);
                }
            }
            
            // Get user or create new one if needed
            if ($createUserIfNotExists) {
                // This would require additional validation and user creation logic
                // For now, we'll assume the user already exists
                $user = User::findOrFail($userId);
            } else {
                $user = User::findOrFail($userId);
            }
            
            // Create or update member record if needed
            if (!empty($memberDetails) && $role !== 'koordinator') {
                $memberData = [
                    'user_id' => $userId,
                    'school_id' => $eschool->school_id,
                    'student_id' => $memberDetails['student_id'] ?? null,
                    'date_of_birth' => $memberDetails['date_of_birth'] ?? null,
                    'gender' => $memberDetails['gender'] ?? null,
                    'address' => $memberDetails['address'] ?? null,
                    'phone' => $memberDetails['phone'] ?? null,
                    'status' => 'active',
                    'is_active' => true
                ];
                
                // Use existing member record or create new one
                $member = Member::updateOrCreate(
                    ['user_id' => $userId],
                    $memberData
                );
            }
            
            // Create user eschool role
            $userEschoolRole = UserEschoolRole::create([
                'user_id' => $userId,
                'eschool_id' => $eschoolId,
                'school_id' => $eschool->school_id,
                'role' => $role,
                'assigned_by' => Auth::id(),
                'status' => 'active'
            ]);
            
            // If role is member, also add to eschool_member table for backward compatibility
            if ($role === 'member') {
                // Get or create member record
                $member = Member::firstOrCreate(
                    ['user_id' => $userId],
                    [
                        'school_id' => $eschool->school_id,
                        'name' => $user->name,
                        'student_id' => $memberDetails['student_id'] ?? null,
                        'date_of_birth' => $memberDetails['date_of_birth'] ?? null,
                        'gender' => $memberDetails['gender'] ?? null,
                        'address' => $memberDetails['address'] ?? null,
                        'phone' => $memberDetails['phone'] ?? null,
                        'status' => 'active',
                        'is_active' => true
                    ]
                );
                
                // Attach member to eschool
                $eschool->members()->syncWithoutDetaching([$member->id]);
            }
            
            // Load relationships
            $userEschoolRole->load(['user', 'eschool']);
            
            return response()->json([
                'success' => true,
                'data' => $userEschoolRole,
                'message' => 'Role assigned successfully'
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to assign role: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update user role in this eschool
     * PUT /api/eschool/{eschool_id}/members/{user_id}/update-role
     */
    public function updateRole(Request $request, $eschoolId, $userId): JsonResponse
    {
        try {
            // Validate that eschool exists
            $eschool = Eschool::findOrFail($eschoolId);
            
            // Validate request
            $validator = Validator::make($request->all(), [
                'role' => 'required|in:member,bendahara,koordinator'
            ]);
            
            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }
            
            $newRole = $request->input('role');
            
            // Find existing role
            $userEschoolRole = UserEschoolRole::where('user_id', $userId)
                ->where('eschool_id', $eschoolId)
                ->firstOrFail();
            
            // Check Qwen compliance for role update
            if ($newRole === 'bendahara') {
                // Check if user is already bendahara in another active eschool
                $isBendaharaInAnyEschool = UserEschoolRole::where('user_id', $userId)
                    ->where('role', 'bendahara')
                    ->where('status', 'active')
                    ->where('id', '!=', $userEschoolRole->id)
                    ->exists();
                    
                if ($isBendaharaInAnyEschool) {
                    return response()->json([
                        'success' => false,
                        'message' => 'User is already bendahara in another eschool'
                    ], 400);
                }
            } elseif ($newRole === 'koordinator') {
                // Check if user is already koordinator in another active eschool
                $isKoordinatorInAnyEschool = UserEschoolRole::where('user_id', $userId)
                    ->where('role', 'koordinator')
                    ->where('status', 'active')
                    ->where('id', '!=', $userEschoolRole->id)
                    ->exists();
                    
                if ($isKoordinatorInAnyEschool) {
                    return response()->json([
                        'success' => false,
                        'message' => 'User is already koordinator in another eschool'
                    ], 400);
                }
            }
            
            // Update role
            $userEschoolRole->update([
                'role' => $newRole,
                'assigned_by' => Auth::id()
            ]);
            
            // Handle eschool_member table synchronization
            $user = User::findOrFail($userId);
            $member = Member::where('user_id', $userId)->first();
            
            if ($newRole === 'member') {
                // If updating to member role, ensure member exists in eschool_member table
                if (!$member) {
                    $member = Member::create([
                        'user_id' => $userId,
                        'school_id' => $eschool->school_id,
                        'name' => $user->name,
                        'status' => 'active',
                        'is_active' => true
                    ]);
                }
                
                // Attach member to eschool
                $eschool->members()->syncWithoutDetaching([$member->id]);
            } else {
                // If updating to non-member role (koordinator/bendahara), 
                // we might want to keep them in eschool_member for backward compatibility
                // or remove them based on business requirements
                // For now, we'll keep them for backward compatibility
                if ($member && !$eschool->members()->where('member_id', $member->id)->exists()) {
                    $eschool->members()->attach($member->id);
                }
            }
            
            // Load relationships
            $userEschoolRole->load(['user', 'eschool']);
            
            return response()->json([
                'success' => true,
                'data' => $userEschoolRole,
                'message' => 'Role updated successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update role: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove user role from this eschool
     * DELETE /api/eschool/{eschool_id}/members/{user_id}/remove-role
     */
    public function removeRole($eschoolId, $userId): JsonResponse
    {
        try {
            // Validate that eschool exists
            $eschool = Eschool::findOrFail($eschoolId);
            
            // Find existing role
            $userEschoolRole = UserEschoolRole::where('user_id', $userId)
                ->where('eschool_id', $eschoolId)
                ->firstOrFail();
            
            // Check if user has any other roles in this eschool
            $otherRolesCount = UserEschoolRole::where('user_id', $userId)
                ->where('eschool_id', $eschoolId)
                ->where('id', '!=', $userEschoolRole->id)
                ->count();
            
            // If user has no other roles in this eschool, also remove from eschool_member table
            if ($otherRolesCount === 0) {
                $member = Member::where('user_id', $userId)->first();
                if ($member) {
                    $eschool->members()->detach($member->id);
                }
            }
            
            // Delete role
            $userEschoolRole->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Role removed successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to remove role: ' . $e->getMessage()
            ], 500);
        }
    }
}