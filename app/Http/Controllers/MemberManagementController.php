<?php

namespace App\Http\Controllers;

use App\Models\Member;
use App\Models\Eschool;
use App\Models\User;
use App\Models\School;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class MemberManagementController extends Controller
{
    /**
     * Display a listing of members with pagination and search
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            
            // Different access levels based on role
            $eschool = null;
            if ($user->role === 'koordinator') {
                // Koordinator can see members in their eschool
                $eschool = Eschool::where('coordinator_id', $user->id)->first();
            } elseif ($user->role === 'bendahara') {
                // Bendahara can see members in their eschool
                $eschool = Eschool::where('treasurer_id', $user->id)->first();
            } elseif ($user->role === 'staff') {
                // Staff can see all members in their school
                // We'll implement this based on school relationship
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access'
                ], 403);
            }
            
            $search = $request->input('search');
            $perPage = $request->input('per_page', 15);
            $sortBy = $request->input('sort_by', 'created_at');
            $sortDirection = $request->input('sort_direction', 'desc');
            $eschoolId = $request->input('eschool_id');
            
            $query = Member::with(['user', 'school', 'eschools']);
            
            // Apply filters based on user role and permissions
            if ($eschool) {
                // Filter by specific eschool if provided, otherwise use user's eschool
                $filterEschoolId = $eschoolId ?: $eschool->id;
                $query->whereHas('eschools', function ($q) use ($filterEschoolId) {
                    $q->where('eschools.id', $filterEschoolId);
                });
            } elseif ($user->role === 'staff') {
                // Staff can see members in their school
                // This would require a relationship between user and school
                // For now, we'll leave it unrestricted for staff but could add filtering
            }
            
            // Apply search
            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('student_id', 'like', "%{$search}%")
                      ->orWhereHas('user', function ($userQuery) use ($search) {
                          $userQuery->where('name', 'like', "%{$search}%")
                                   ->orWhere('email', 'like', "%{$search}%");
                      });
                });
            }
            
            $members = $query->orderBy($sortBy, $sortDirection)
                           ->paginate($perPage);
            
            return response()->json([
                'success' => true,
                'data' => $members->items(),
                'pagination' => [
                    'current_page' => $members->currentPage(),
                    'last_page' => $members->lastPage(),
                    'per_page' => $members->perPage(),
                    'total' => $members->total(),
                ],
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
     * Store a newly created member
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $currentUser = Auth::user();
            
            // Validate that user has permission to create members
            if (!in_array($currentUser->role, ['koordinator', 'staff'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Only koordinator and staff can create members'
                ], 403);
            }
            
            // For koordinator, get their eschool automatically
            $eschool = null;
            if ($currentUser->role === 'koordinator') {
                $eschool = Eschool::where('coordinator_id', $currentUser->id)->first();
                if (!$eschool) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Koordinator tidak memiliki eschool'
                    ], 400);
                }
            }
            
            // Validate input
            $rules = [
                'create_new_user' => 'required|boolean',
                'existing_user_id' => 'nullable|required_if:create_new_user,0|exists:users,id',
                'new_user_name' => 'nullable|required_if:create_new_user,1|string|max:255',
                'new_user_email' => 'nullable|required_if:create_new_user,1|email|max:255|unique:users,email',
                'new_user_password' => 'nullable|required_if:create_new_user,1|string|min:8',
                'nip' => 'nullable|string|max:255',
                'name' => 'required|string|max:255',
                'student_id' => 'nullable|string|max:255|unique:members,student_id',
                'date_of_birth' => 'nullable|date',
                'gender' => 'nullable|string|in:L,P',
                'address' => 'nullable|string',
                'phone' => 'nullable|string|max:20',
                'email' => 'nullable|email|max:255|unique:members,email',
                'position' => 'nullable|string|max:255',
                'status' => 'nullable|string|in:active,inactive',
            ];
            
            // For staff, we still need eschool_ids
            if ($currentUser->role === 'staff') {
                $rules['eschool_ids'] = 'required|array|min:1';
                $rules['eschool_ids.*'] = 'exists:eschools,id';
            }
            
            $validator = Validator::make($request->all(), $rules);
            
            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }
            
            // Handle user creation or selection
            $userId = null;
            if ($request->create_new_user) {
                // Create new user
                $newUser = User::create([
                    'name' => $request->new_user_name,
                    'email' => $request->new_user_email,
                    'password' => Hash::make($request->new_user_password),
                    'role' => 'siswa', // Default role for new members
                ]);
                $userId = $newUser->id;
            } else {
                // Use existing user
                $userId = $request->existing_user_id;
                
                // For koordinator, check if user already has a member record in their eschool
                if ($currentUser->role === 'koordinator') {
                    $existingMember = Member::where('user_id', $userId)
                                           ->whereHas('eschools', function ($query) use ($eschool) {
                                               $query->where('eschools.id', $eschool->id);
                                           })
                                           ->first();
                    
                    if ($existingMember) {
                        return response()->json([
                            'success' => false,
                            'message' => 'User already has a member record in this eschool'
                        ], 400);
                    }
                }
                // For staff, check if user already has a member record in any of the selected eschools
                else {
                    $existingMember = Member::where('user_id', $userId)
                                           ->whereHas('eschools', function ($query) use ($request) {
                                               $query->whereIn('eschools.id', $request->eschool_ids);
                                           })
                                           ->first();
                    
                    if ($existingMember) {
                        return response()->json([
                            'success' => false,
                            'message' => 'User already has a member record in one of the selected eschools'
                        ], 400);
                    }
                }
            }
            
            // Set eschools and school based on user role
            $eschoolIds = [];
            $schoolId = null;
            
            if ($currentUser->role === 'koordinator') {
                // For koordinator, use their eschool
                $eschoolIds = [$eschool->id];
                $schoolId = $eschool->school_id;
            } else {
                // For staff, check if all eschools belong to the same school
                $eschools = Eschool::whereIn('id', $request->eschool_ids)->get();
                $schoolIds = $eschools->pluck('school_id')->unique();
                
                if ($schoolIds->count() > 1) {
                    return response()->json([
                        'success' => false,
                        'message' => 'All selected eschools must belong to the same school.'
                    ], 400);
                }
                
                $eschoolIds = $request->eschool_ids;
                $schoolId = $schoolIds->first();
            }
            
            // Create member
            $memberData = $request->only([
                'nip', 'name', 'student_id', 'date_of_birth', 'gender',
                'address', 'phone', 'email', 'position', 'status'
            ]);
            
            $memberData['school_id'] = $schoolId;
            $memberData['user_id'] = $userId;
            $memberData['status'] = $memberData['status'] ?? 'active';
            $memberData['is_active'] = true;
            
            $member = Member::create($memberData);
            
            // Attach to eschools
            $member->eschools()->attach($eschoolIds);
            
            // Load relationships
            $member->load(['user', 'school', 'eschools']);
            
            return response()->json([
                'success' => true,
                'data' => $member,
                'message' => 'Member created successfully'
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create member: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified member
     */
    public function show(string $id): JsonResponse
    {
        try {
            $user = Auth::user();
            
            // Check permissions - similar logic to index
            $member = Member::with(['user', 'school', 'eschools'])
                          ->findOrFail($id);
            
            // Check if user has access to this member
            $hasAccess = false;
            
            if ($user->role === 'koordinator') {
                // Koordinator can access members in their eschools
                $eschool = Eschool::where('coordinator_id', $user->id)->first();
                if ($eschool && $member->eschools->contains($eschool->id)) {
                    $hasAccess = true;
                }
            } elseif ($user->role === 'bendahara') {
                // Bendahara can access members in their eschool
                $eschool = Eschool::where('treasurer_id', $user->id)->first();
                if ($eschool && $member->eschools->contains($eschool->id)) {
                    $hasAccess = true;
                }
            } elseif ($user->role === 'staff') {
                // Staff can access members in their school
                // Add school-based access control here
                $hasAccess = true; // Simplified for now
            }
            
            if (!$hasAccess) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access to this member'
                ], 403);
            }
            
            return response()->json([
                'success' => true,
                'data' => $member,
                'message' => 'Member retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve member: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified member
     */
    public function update(Request $request, string $id): JsonResponse
    {
        try {
            $user = Auth::user();
            
            // Check permissions
            if (!in_array($user->role, ['koordinator', 'staff'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Only koordinator and staff can update members'
                ], 403);
            }
            
            $member = Member::findOrFail($id);
            
            // Validate input
            $validator = Validator::make($request->all(), [
                'eschool_ids' => 'sometimes|required|array|min:1',
                'eschool_ids.*' => 'exists:eschools,id',
                'nip' => 'nullable|string|max:255',
                'name' => 'sometimes|required|string|max:255',
                'student_id' => 'nullable|string|max:255|unique:members,student_id,' . $id,
                'date_of_birth' => 'nullable|date',
                'gender' => 'nullable|string|in:L,P',
                'address' => 'nullable|string',
                'phone' => 'nullable|string|max:20',
                'email' => 'nullable|email|max:255|unique:members,email,' . $id,
                'position' => 'nullable|string|max:255',
                'status' => 'nullable|string|in:active,inactive',
            ]);
            
            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }
            
            // Check permissions for this specific member
            $hasAccess = false;
            
            if ($user->role === 'koordinator') {
                // Koordinator can update members in their eschools
                $eschool = Eschool::where('coordinator_id', $user->id)->first();
                if ($eschool && $member->eschools->contains($eschool->id)) {
                    $hasAccess = true;
                }
            } elseif ($user->role === 'staff') {
                // Staff can update members in their school
                // Add school-based access control here
                $hasAccess = true; // Simplified for now
            }
            
            if (!$hasAccess) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access to update this member'
                ], 403);
            }
            
            // Update member data
            $memberData = $request->only([
                'nip', 'name', 'student_id', 'date_of_birth', 'gender',
                'address', 'phone', 'email', 'position', 'status'
            ]);
            
            $member->update($memberData);
            
            // Update eschool associations if provided
            if ($request->has('eschool_ids')) {
                // Check that all eschools belong to the same school
                $eschools = Eschool::whereIn('id', $request->eschool_ids)->get();
                $schoolIds = $eschools->pluck('school_id')->unique();
                
                if ($schoolIds->count() > 1) {
                    return response()->json([
                        'success' => false,
                        'message' => 'All selected eschools must belong to the same school.'
                    ], 400);
                }
                
                // Update school_id if needed
                $member->school_id = $schoolIds->first();
                $member->save();
                
                // Sync eschool associations
                $member->eschools()->sync($request->eschool_ids);
            }
            
            // Load relationships
            $member->load(['user', 'school', 'eschools']);
            
            return response()->json([
                'success' => true,
                'data' => $member,
                'message' => 'Member updated successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update member: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified member
     */
    public function destroy(string $id): JsonResponse
    {
        try {
            $user = Auth::user();
            
            // Check permissions
            if (!in_array($user->role, ['koordinator', 'staff'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Only koordinator and staff can delete members'
                ], 403);
            }
            
            $member = Member::findOrFail($id);
            
            // Check permissions for this specific member
            $hasAccess = false;
            
            if ($user->role === 'koordinator') {
                // Koordinator can delete members in their eschools
                $eschool = Eschool::where('coordinator_id', $user->id)->first();
                if ($eschool && $member->eschools->contains($eschool->id)) {
                    $hasAccess = true;
                }
            } elseif ($user->role === 'staff') {
                // Staff can delete members in their school
                // Add school-based access control here
                $hasAccess = true; // Simplified for now
            }
            
            if (!$hasAccess) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access to delete this member'
                ], 403);
            }
            
            // Remove eschool associations
            $member->eschools()->detach();
            
            // Delete member
            $member->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Member deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete member: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get available users for member creation
     */
    public function getAvailableUsers(Request $request): JsonResponse
    {
        try {
            $currentUser = Auth::user();
            
            // Only koordinator and staff can manage members
            if (!in_array($currentUser->role, ['koordinator', 'staff'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access'
                ], 403);
            }
            
            // Tentukan school_id dari user yang sedang login
            $schoolId = null;
            if ($currentUser->role === 'koordinator') {
                $eschool = Eschool::where('coordinator_id', $currentUser->id)->first();
                if (!$eschool) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Koordinator tidak memiliki eschool'
                    ], 400);
                }
                $schoolId = $eschool->school_id;
            } elseif ($currentUser->role === 'staff') {
                // Staff harus memiliki school_id
                if (!$currentUser->school_id) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Staff tidak memiliki akses sekolah yang ditentukan'
                    ], 400);
                }
                $schoolId = $currentUser->school_id;
            }
            
            // Get users who can be members (role = siswa) AND belong to the same school
            $usersQuery = User::where('role', 'siswa')
                            ->where('school_id', $schoolId) // <-- Filter berdasarkan sekolah
                            ->select('id', 'name', 'email');
            
            // For koordinator, filter out users who are already members in their eschool
            if ($currentUser->role === 'koordinator') {
                $eschool = Eschool::where('coordinator_id', $currentUser->id)->first();
                if ($eschool) {
                    // Get user IDs that are already members in this eschool
                    $existingMemberUserIds = Member::whereHas('eschools', function ($query) use ($eschool) {
                        $query->where('eschools.id', $eschool->id);
                    })->pluck('user_id');
                    
                    // Exclude these users from the available list
                    $usersQuery->whereNotIn('id', $existingMemberUserIds);
                }
            }
            
            $users = $usersQuery->get();
            
            return response()->json([
                'success' => true,
                'data' => $users,
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
     * Get schools for member creation
     */
    public function getSchools(): JsonResponse
    {
        try {
            $user = Auth::user();
            
            // Only koordinator and staff can manage members
            if (!in_array($user->role, ['koordinator', 'staff'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access'
                ], 403);
            }
            
            $schools = School::select('id', 'name')->get();
            
            return response()->json([
                'success' => true,
                'data' => $schools,
                'message' => 'Schools retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve schools: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get eschools for member creation
     */
    public function getEschools(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            
            // Only koordinator and staff can manage members
            if (!in_array($user->role, ['koordinator', 'staff'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access'
                ], 403);
            }
            
            $schoolId = $request->input('school_id');
            
            $query = Eschool::with(['school', 'coordinator', 'treasurer']);
            
            if ($user->role === 'koordinator') {
                // Koordinator can only see their own eschool
                $query->where('coordinator_id', $user->id);
            } elseif ($user->role === 'staff') {
                // Staff can see eschools in their school
                // We would need to implement school-staff relationship
                // For now, filter by school_id if provided
                if ($schoolId) {
                    $query->where('school_id', $schoolId);
                }
            }
            
            $eschools = $query->select('id', 'name', 'school_id', 'coordinator_id', 'treasurer_id')
                             ->get();
            
            return response()->json([
                'success' => true,
                'data' => $eschools,
                'message' => 'Eschools retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve eschools: ' . $e->getMessage()
            ], 500);
        }
    }
}