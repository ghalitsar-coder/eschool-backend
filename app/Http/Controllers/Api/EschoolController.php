<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Models\Eschool;
use App\Models\UserEschoolRole;
use App\Models\Teacher;

class EschoolController extends Controller
{
    /**
     * Display a listing of all eschools.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        try {
            // Get the authenticated user
            $user = Auth::user();
            
            // Get user's profile
            $profile = $user->profile;
            
            // Check if user is a teacher (staff/coordinator)
            $teacher = Teacher::where('profile_id', $profile->id)->first();
            
            // Get user's eschool roles
            $userEschoolRoles = $user->userEschoolRoles;
            
            // For supervisors (staff), get all eschools from their school
            if ($userEschoolRoles->contains('role', 'supervisor') && $teacher) {
                $eschools = Eschool::where('school_id', $teacher->school_id)
                    ->with(['school', 'userEschoolRoles.user.profile'])
                    ->get();
            } else {
                // For other roles, get only eschools they're associated with
                $eschoolIds = $userEschoolRoles->pluck('eschool_id')->filter();
                $eschools = Eschool::whereIn('id', $eschoolIds)
                    ->with(['school', 'userEschoolRoles.user.profile'])
                    ->get();
            }
            
            // Enhance eschools with additional data
            $enhancedEschools = $eschools->map(function ($eschool) {
                // Get coordinator and treasurer
                $coordinatorRole = $eschool->userEschoolRoles->firstWhere('role', 'coordinator');
                $treasurerRole = $eschool->userEschoolRoles->firstWhere('role', 'treasurer');
                
                // Count members
                $membersCount = $eschool->userEschoolRoles->where('role', 'member')->count();
                
                return [
                    'id' => $eschool->id,
                    'name' => $eschool->name,
                    'description' => $eschool->description,
                    'school_id' => $eschool->school_id,
                    'coordinator_id' => $coordinatorRole ? $coordinatorRole->user_id : null,
                    'treasurer_id' => $treasurerRole ? $treasurerRole->user_id : null,
                    'monthly_kas_amount' => $eschool->monthly_fee_amount,
                    'schedule_days' => $eschool->schedule_days ? json_decode($eschool->schedule_days) : [],
                    'total_schedule_days' => $eschool->schedule_days ? count(json_decode($eschool->schedule_days) ?: []) : 0,
                    'is_active' => $eschool->is_active,
                    'members_count' => $membersCount,
                    'coordinator' => $coordinatorRole ? [
                        'id' => $coordinatorRole->user->id,
                        'name' => $coordinatorRole->user->name,
                        'email' => $coordinatorRole->user->email,
                    ] : null,
                    'treasurer' => $treasurerRole ? [
                        'id' => $treasurerRole->user->id,
                        'name' => $treasurerRole->user->name,
                        'email' => $treasurerRole->user->email,
                    ] : null,
                    'created_at' => $eschool->created_at,
                    'updated_at' => $eschool->updated_at,
                ];
            });
            
            return response()->json($enhancedEschools);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error retrieving eschools',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified eschool.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        try {
            $eschool = Eschool::with(['school', 'userEschoolRoles.user.profile'])->findOrFail($id);
            
            // Get coordinator and treasurer
            $coordinatorRole = $eschool->userEschoolRoles->firstWhere('role', 'coordinator');
            $treasurerRole = $eschool->userEschoolRoles->firstWhere('role', 'treasurer');
            
            // Count members
            $membersCount = $eschool->userEschoolRoles->where('role', 'member')->count();
            
            $enhancedEschool = [
                'id' => $eschool->id,
                'name' => $eschool->name,
                'description' => $eschool->description,
                'school_id' => $eschool->school_id,
                'coordinator_id' => $coordinatorRole ? $coordinatorRole->user_id : null,
                'treasurer_id' => $treasurerRole ? $treasurerRole->user_id : null,
                'monthly_kas_amount' => $eschool->monthly_fee_amount,
                'schedule_days' => $eschool->schedule_days ? json_decode($eschool->schedule_days) : [],
                'total_schedule_days' => $eschool->schedule_days ? count(json_decode($eschool->schedule_days) ?: []) : 0,
                'is_active' => $eschool->is_active,
                'members_count' => $membersCount,
                'coordinator' => $coordinatorRole ? [
                    'id' => $coordinatorRole->user->id,
                    'name' => $coordinatorRole->user->name,
                    'email' => $coordinatorRole->user->email,
                ] : null,
                'treasurer' => $treasurerRole ? [
                    'id' => $treasurerRole->user->id,
                    'name' => $treasurerRole->user->name,
                    'email' => $treasurerRole->user->email,
                ] : null,
                'created_at' => $eschool->created_at,
                'updated_at' => $eschool->updated_at,
            ];
            
            return response()->json($enhancedEschool);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error retrieving eschool',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created eschool.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        try {
            // Validate the request
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'schedule_days' => 'nullable|array',
                'schedule_days.*' => 'string|in:Senin,Selasa,Rabu,Kamis,Jumat,Sabtu,Minggu',
                'monthly_fee_amount' => 'nullable|numeric|min:0',
                'is_active' => 'boolean',
            ]);
            
            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }
            
            // Check if user is supervisor (staff) for the school
            $user = Auth::user();
            $profile = $user->profile;
            $teacher = Teacher::where('profile_id', $profile->id)->first();
            
            if (!$teacher) {
                return response()->json([
                    'message' => 'Only teachers can create eschools'
                ], 403);
            }
            
            $userEschoolRoles = $user->userEschoolRoles;
            $isSupervisor = $userEschoolRoles->contains('role', 'supervisor');
            
            if (!$isSupervisor) {
                return response()->json([
                    'message' => 'Only supervisors can create eschools for their school'
                ], 403);
            }
            
            // Automatically determine school_id from the supervisor's teacher record
            $schoolId = $teacher->school_id;
            
            // Create the eschool
            $eschool = Eschool::create([
                'name' => $request->name,
                'description' => $request->description,
                'school_id' => $schoolId,
                'schedule_days' => $request->schedule_days ? json_encode($request->schedule_days) : null,
                'monthly_fee_amount' => $request->monthly_fee_amount ?? 0,
                'is_active' => $request->is_active ?? true,
            ]);
            
            // Load relationships
            $eschool->load(['school', 'userEschoolRoles.user.profile']);
            
            // Get coordinator and treasurer
            $coordinatorRole = $eschool->userEschoolRoles->firstWhere('role', 'coordinator');
            $treasurerRole = $eschool->userEschoolRoles->firstWhere('role', 'treasurer');
            
            // Count members
            $membersCount = $eschool->userEschoolRoles->where('role', 'member')->count();
            
            $enhancedEschool = [
                'id' => $eschool->id,
                'name' => $eschool->name,
                'description' => $eschool->description,
                'school_id' => $eschool->school_id,
                'coordinator_id' => $coordinatorRole ? $coordinatorRole->user_id : null,
                'treasurer_id' => $treasurerRole ? $treasurerRole->user_id : null,
                'monthly_kas_amount' => $eschool->monthly_fee_amount,
                'schedule_days' => $eschool->schedule_days ? json_decode($eschool->schedule_days) : [],
                'total_schedule_days' => $eschool->schedule_days ? count(json_decode($eschool->schedule_days) ?: []) : 0,
                'is_active' => $eschool->is_active,
                'members_count' => $membersCount,
                'coordinator' => $coordinatorRole ? [
                    'id' => $coordinatorRole->user->id,
                    'name' => $coordinatorRole->user->name,
                    'email' => $coordinatorRole->user->email,
                ] : null,
                'treasurer' => $treasurerRole ? [
                    'id' => $treasurerRole->user->id,
                    'name' => $treasurerRole->user->name,
                    'email' => $treasurerRole->user->email,
                ] : null,
                'created_at' => $eschool->created_at,
                'updated_at' => $eschool->updated_at,
            ];
            
            return response()->json($enhancedEschool, 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error creating eschool',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified eschool.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        try {
            // Validate the request
            $validator = Validator::make($request->all(), [
                'name' => 'sometimes|string|max:255',
                'description' => 'nullable|string',
                'coordinator_id' => 'nullable|exists:users,id',
                'treasurer_id' => 'nullable|exists:users,id',
                'schedule_days' => 'nullable|array',
                'schedule_days.*' => 'string|in:Senin,Selasa,Rabu,Kamis,Jumat,Sabtu,Minggu',
                'monthly_fee_amount' => 'nullable|numeric|min:0',
                'is_active' => 'boolean',
            ]);
            
            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }
            
            // Find the eschool
            $eschool = Eschool::findOrFail($id);
            
            // Check if user is supervisor (staff) for the school
            $user = Auth::user();
            $profile = $user->profile;
            $teacher = Teacher::where('profile_id', $profile->id)->first();
            
            if (!$teacher) {
                return response()->json([
                    'message' => 'Only teachers can update eschools'
                ], 403);
            }
            
            $userEschoolRoles = $user->userEschoolRoles;
            $isSupervisor = $userEschoolRoles->contains('role', 'supervisor');
            
            if (!$isSupervisor) {
                return response()->json([
                    'message' => 'Only supervisors can update eschools for their school'
                ], 403);
            }
            
            // Handle coordinator assignment
            if ($request->has('coordinator_id')) {
                $coordinatorId = $request->coordinator_id;
                
                // Check if the coordinator is already assigned to another eschool (only if not null)
                if ($coordinatorId && $coordinatorId !== 'null') {
                    $existingCoordinatorRole = UserEschoolRole::where('user_id', $coordinatorId)
                        ->where('role', 'coordinator')
                        ->where('eschool_id', '!=', $eschool->id)
                        ->first();
                    
                    if ($existingCoordinatorRole) {
                        return response()->json([
                            'message' => 'This coordinator is already assigned to another eschool'
                        ], 422);
                    }
                }
                
                // Remove existing coordinator role if there is one
                $existingEschoolCoordinator = UserEschoolRole::where('eschool_id', $eschool->id)
                    ->where('role', 'coordinator')
                    ->first();
                
                if ($existingEschoolCoordinator) {
                    $existingEschoolCoordinator->delete();
                }
                
                // Assign new coordinator if provided (and not null/'none')
                if ($coordinatorId && $coordinatorId !== 'null' && $coordinatorId !== 'none') {
                    UserEschoolRole::updateOrCreate(
                        [
                            'user_id' => $coordinatorId,
                            'eschool_id' => $eschool->id,
                            'role' => 'coordinator'
                        ],
                        [
                            'user_id' => $coordinatorId,
                            'eschool_id' => $eschool->id,
                            'role' => 'coordinator'
                        ]
                    );
                }
            }
            
            // Handle treasurer assignment
            if ($request->has('treasurer_id')) {
                $treasurerId = $request->treasurer_id;
                
                // Remove existing treasurer role if there is one
                $existingEschoolTreasurer = UserEschoolRole::where('eschool_id', $eschool->id)
                    ->where('role', 'treasurer')
                    ->first();
                
                if ($existingEschoolTreasurer) {
                    $existingEschoolTreasurer->delete();
                }
                
                // Assign new treasurer if provided (and not null/'none')
                if ($treasurerId && $treasurerId !== 'null' && $treasurerId !== 'none') {
                    UserEschoolRole::updateOrCreate(
                        [
                            'user_id' => $treasurerId,
                            'eschool_id' => $eschool->id,
                            'role' => 'treasurer'
                        ],
                        [
                            'user_id' => $treasurerId,
                            'eschool_id' => $eschool->id,
                            'role' => 'treasurer'
                        ]
                    );
                }
            }
            
            // Update the eschool
            $eschool->update([
                'name' => $request->name ?? $eschool->name,
                'description' => $request->description ?? $eschool->description,
                'schedule_days' => $request->schedule_days ? json_encode($request->schedule_days) : $eschool->schedule_days,
                'monthly_fee_amount' => $request->has('monthly_fee_amount') ? $request->monthly_fee_amount : $eschool->monthly_fee_amount,
                'is_active' => $request->has('is_active') ? $request->is_active : $eschool->is_active,
            ]);
            
            // Load relationships
            $eschool->load(['school', 'userEschoolRoles.user.profile']);
            
            // Get coordinator and treasurer
            $coordinatorRole = $eschool->userEschoolRoles->firstWhere('role', 'coordinator');
            $treasurerRole = $eschool->userEschoolRoles->firstWhere('role', 'treasurer');
            
            // Count members
            $membersCount = $eschool->userEschoolRoles->where('role', 'member')->count();
            
            $enhancedEschool = [
                'id' => $eschool->id,
                'name' => $eschool->name,
                'description' => $eschool->description,
                'school_id' => $eschool->school_id,
                'coordinator_id' => $coordinatorRole ? $coordinatorRole->user_id : null,
                'treasurer_id' => $treasurerRole ? $treasurerRole->user_id : null,
                'monthly_kas_amount' => $eschool->monthly_fee_amount,
                'schedule_days' => $eschool->schedule_days ? json_decode($eschool->schedule_days) : [],
                'total_schedule_days' => $eschool->schedule_days ? count(json_decode($eschool->schedule_days) ?: []) : 0,
                'is_active' => $eschool->is_active,
                'members_count' => $membersCount,
                'coordinator' => $coordinatorRole ? [
                    'id' => $coordinatorRole->user->id,
                    'name' => $coordinatorRole->user->name,
                    'email' => $coordinatorRole->user->email,
                ] : null,
                'treasurer' => $treasurerRole ? [
                    'id' => $treasurerRole->user->id,
                    'name' => $treasurerRole->user->name,
                    'email' => $treasurerRole->user->email,
                ] : null,
                'created_at' => $eschool->created_at,
                'updated_at' => $eschool->updated_at,
            ];
            
            return response()->json($enhancedEschool);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error updating eschool',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified eschool.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        try {
            // Find the eschool
            $eschool = Eschool::findOrFail($id);
            
            // Check if user is supervisor (staff) for the school
            $user = Auth::user();
            $profile = $user->profile;
            $teacher = Teacher::where('profile_id', $profile->id)->first();
            
            if (!$teacher) {
                return response()->json([
                    'message' => 'Only teachers can delete eschools'
                ], 403);
            }
            
            $userEschoolRoles = $user->userEschoolRoles;
            $isSupervisor = $userEschoolRoles->contains('role', 'supervisor');
            
            if (!$isSupervisor) {
                return response()->json([
                    'message' => 'Only supervisors can delete eschools for their school'
                ], 403);
            }
            
            // Delete the eschool
            $eschool->delete();
            
            return response()->json([
                'message' => 'Eschool deleted successfully'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error deleting eschool',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get eligible coordinators (teachers who are not already coordinators of other eschools).
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getEligibleCoordinators(Request $request)
    {
        try {
            // Get the authenticated user
            $user = Auth::user();
            $profile = $user->profile;
            $teacher = Teacher::where('profile_id', $profile->id)->first();
            
            if (!$teacher) {
                return response()->json([
                    'message' => 'Only teachers can access coordinator information'
                ], 403);
            }
            
            $userEschoolRoles = $user->userEschoolRoles;
            $isSupervisor = $userEschoolRoles->contains('role', 'supervisor');
            
            if (!$isSupervisor) {
                return response()->json([
                    'message' => 'Only supervisors can access coordinator information'
                ], 403);
            }
            
            // Get school_id from the teacher record
            $schoolId = $teacher->school_id;
            
            // Get all teachers from the same school who are not already coordinators
            // First, get all user IDs that are already coordinators
            $existingCoordinatorIds = UserEschoolRole::where('role', 'coordinator')
                ->pluck('user_id');
            
            // Get all teachers from the same school who are not coordinators
            $eligibleTeachers = Teacher::where('school_id', $schoolId)
                ->whereNotIn('profile_id', function($query) {
                    $query->select('profile_id')
                        ->from('users')
                        ->whereIn('id', function($query2) {
                            $query2->select('user_id')
                                ->from('user_eschool_roles')
                                ->where('role', 'coordinator');
                        });
                })
                ->with('profile')
                ->get()
                ->map(function ($teacher) {
                    return [
                        'id' => $teacher->profile->user->id,
                        'name' => $teacher->profile->name,
                        'email' => $teacher->profile->user->email,
                    ];
                });
            
            return response()->json($eligibleTeachers);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error retrieving eligible coordinators',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}