<?php

namespace App\Http\Controllers;

use App\Models\Eschool;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class EschoolController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $user = Auth::user();
            
            // Different access levels based on role
            $query = Eschool::with(['coordinator', 'treasurer']);
            
            // Apply filters based on user role
            if ($user->role === 'koordinator') {
                // Koordinator can only see their own eschool
                $query->where('coordinator_id', $user->id);
            } elseif ($user->role === 'bendahara') {
                // Bendahara can only see their own eschool
                $query->where('treasurer_id', $user->id);
            } elseif ($user->role === 'staff') {
                // Staff can see all eschools in their school
                $query->where('school_id', $user->school_id);
            } elseif ($user->role !== 'siswa') {
                // For other roles, return empty collection
                return response()->json([]);
            }
            
            // Add member count to each eschool
            $eschools = $query->get()->map(function ($eschool) {
                $eschool->members_count = $eschool->members()->count();
                return $eschool;
            });
            
            return response()->json($eschools);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to retrieve eschools',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $user = Auth::user();
            
            // Only staff can create eschools
            if ($user->role !== 'staff') {
                return response()->json([
                    'message' => 'Unauthorized. Only staff can create eschools.'
                ], 403);
            }
            
            // Validate input
            $validator = Validator::make($request->all(), [
                'school_id' => 'required|exists:schools,id',
                // New coordinator fields (always required for new eschool)
                'new_coordinator_name' => 'required|string|max:255',
                'new_coordinator_email' => 'required|email|unique:users,email',
                'new_coordinator_nip' => 'nullable|string|max:255',
                'new_coordinator_date_of_birth' => 'nullable|date',
                'new_coordinator_gender' => 'nullable|string|in:L,P',
                'new_coordinator_address' => 'nullable|string',
                'new_coordinator_phone' => 'nullable|string|max:20',
                // Treasurer option
                'treasurer_option' => 'required|in:existing,new',
                'treasurer_id' => 'required_if:treasurer_option,existing|exists:users,id',
                'new_treasurer_name' => 'required_if:treasurer_option,new|string|max:255',
                'new_treasurer_email' => 'required_if:treasurer_option,new|email|unique:users,email',
                'new_treasurer_nip' => 'nullable|string|max:255',
                'new_treasurer_date_of_birth' => 'nullable|date',
                'new_treasurer_gender' => 'nullable|string|in:L,P',
                'new_treasurer_address' => 'nullable|string',
                'new_treasurer_phone' => 'nullable|string|max:20',
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'monthly_kas_amount' => 'nullable|integer|min:0',
                'schedule_days' => 'nullable|array',
                'total_schedule_days' => 'nullable|integer|min:0',
                'is_active' => 'nullable|boolean',
            ]);
            
            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }
            
            // Ensure the school belongs to the staff's school
            if ($request->school_id != $user->school_id) {
                return response()->json([
                    'message' => 'Unauthorized. You can only create eschools for your own school.'
                ], 403);
            }
            
            // Create new coordinator (always for new eschool)
            $coordinator = User::create([
                'name' => $request->new_coordinator_name,
                'email' => $request->new_coordinator_email,
                'password' => Hash::make('password'), // Default password
                'role' => 'koordinator',
                'school_id' => $request->school_id,
            ]);
            
            // Create member record for coordinator
            $coordinatorMember = \App\Models\Member::create([
                'user_id' => $coordinator->id,
                'school_id' => $request->school_id,
                'nip' => $request->new_coordinator_nip,
                'name' => $request->new_coordinator_name,
                'date_of_birth' => $request->new_coordinator_date_of_birth,
                'gender' => $request->new_coordinator_gender,
                'address' => $request->new_coordinator_address,
                'phone' => $request->new_coordinator_phone,
                'status' => 'active',
                'is_active' => true,
            ]);
            $coordinatorId = $coordinator->id;
            
            // Handle treasurer creation/validation
            if ($request->treasurer_option === 'new') {
                // Create new treasurer
                $treasurer = User::create([
                    'name' => $request->new_treasurer_name,
                    'email' => $request->new_treasurer_email,
                    'password' => Hash::make('password'), // Default password
                    'role' => 'bendahara',
                    'school_id' => $request->school_id,
                ]);
                
                // Create member record for treasurer
                $treasurerMember = \App\Models\Member::create([
                    'user_id' => $treasurer->id,
                    'school_id' => $request->school_id,
                    'nip' => $request->new_treasurer_nip,
                    'name' => $request->new_treasurer_name,
                    'date_of_birth' => $request->new_treasurer_date_of_birth,
                    'gender' => $request->new_treasurer_gender,
                    'address' => $request->new_treasurer_address,
                    'phone' => $request->new_treasurer_phone,
                    'status' => 'active',
                    'is_active' => true,
                ]);
                $treasurerId = $treasurer->id;
            } else {
                // Validate existing treasurer
                $treasurer = User::find($request->treasurer_id);
                if (!$treasurer || $treasurer->school_id !== $request->school_id || $treasurer->role !== 'bendahara') {
                    return response()->json([
                        'message' => 'Invalid treasurer. Treasurer must be from the same school and have bendahara role.'
                    ], 422);
                }
                
                // Check if treasurer is already assigned to another eschool
                if ($treasurer->treasuredEschool) {
                    return response()->json([
                        'message' => 'This treasurer is already assigned to another eschool.'
                    ], 422);
                }
                
                $treasurerId = $request->treasurer_id;
            }
            
            // Create eschool
            $eschool = Eschool::create([
                'school_id' => $request->school_id,
                'coordinator_id' => $coordinatorId,
                'treasurer_id' => $treasurerId,
                'name' => $request->name,
                'description' => $request->description,
                'monthly_kas_amount' => $request->monthly_kas_amount ?? 20000, // Default value
                'schedule_days' => $request->schedule_days ? json_encode($request->schedule_days) : null,
                'total_schedule_days' => $request->total_schedule_days ?? 3, // Default value
                'is_active' => $request->is_active ?? true,
            ]);
            
            // Load relationships
            $eschool->load(['coordinator', 'treasurer']);
            $eschool->members_count = 0;
            
            return response()->json($eschool, 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to create eschool',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $user = Auth::user();
            
            // Find eschool
            $eschool = Eschool::with(['coordinator', 'treasurer'])->findOrFail($id);
            
            // Check access based on user role
            if ($user->role === 'koordinator' && $eschool->coordinator_id !== $user->id) {
                return response()->json([
                    'message' => 'Unauthorized. You can only access your own eschool.'
                ], 403);
            } elseif ($user->role === 'bendahara' && $eschool->treasurer_id !== $user->id) {
                return response()->json([
                    'message' => 'Unauthorized. You can only access your own eschool.'
                ], 403);
            } elseif ($user->role === 'staff' && $eschool->school_id !== $user->school_id) {
                return response()->json([
                    'message' => 'Unauthorized. You can only access eschools in your own school.'
                ], 403);
            }
            
            // Add member count
            $eschool->members_count = $eschool->members()->count();
            
            return response()->json($eschool);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to retrieve eschool',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            $user = Auth::user();
            
            // Find eschool
            $eschool = Eschool::findOrFail($id);
            
            // Check access based on user role
            if ($user->role !== 'staff') {
                return response()->json([
                    'message' => 'Unauthorized. Only staff can update eschools.'
                ], 403);
            }
            
            if ($eschool->school_id !== $user->school_id) {
                return response()->json([
                    'message' => 'Unauthorized. You can only update eschools in your own school.'
                ], 403);
            }
            
            // Validate input
            $validator = Validator::make($request->all(), [
                // Coordinator cannot be changed
                // Treasurer option
                'treasurer_option' => 'sometimes|required|in:existing,new,no_change',
                'treasurer_id' => 'required_if:treasurer_option,existing|exists:users,id',
                'new_treasurer_name' => 'required_if:treasurer_option,new|string|max:255',
                'new_treasurer_email' => 'required_if:treasurer_option,new|email|unique:users,email',
                'new_treasurer_nip' => 'nullable|string|max:255',
                'new_treasurer_date_of_birth' => 'nullable|date',
                'new_treasurer_gender' => 'nullable|string|in:L,P',
                'new_treasurer_address' => 'nullable|string',
                'new_treasurer_phone' => 'nullable|string|max:20',
                'name' => 'sometimes|required|string|max:255',
                'description' => 'nullable|string',
                'monthly_kas_amount' => 'nullable|integer|min:0',
                'schedule_days' => 'nullable|array',
                'total_schedule_days' => 'nullable|integer|min:0',
                'is_active' => 'nullable|boolean',
            ]);
            
            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }
            
            // Prepare data for update
            $updateData = [];
            
            // Handle treasurer update/validation (only if user wants to change it)
            if ($request->has('treasurer_option') && $request->treasurer_option !== 'no_change') {
                if ($request->treasurer_option === 'new') {
                    // Create new treasurer
                    $treasurer = User::create([
                        'name' => $request->new_treasurer_name,
                        'email' => $request->new_treasurer_email,
                        'password' => Hash::make(Str::random(12)), // Generate random password
                        'role' => 'bendahara',
                        'school_id' => $eschool->school_id,
                    ]);
                    
                    // Create member record for treasurer
                    $treasurerMember = \App\Models\Member::create([
                        'user_id' => $treasurer->id,
                        'school_id' => $eschool->school_id,
                        'nip' => $request->new_treasurer_nip,
                        'name' => $request->new_treasurer_name,
                        'date_of_birth' => $request->new_treasurer_date_of_birth,
                        'gender' => $request->new_treasurer_gender,
                        'address' => $request->new_treasurer_address,
                        'phone' => $request->new_treasurer_phone,
                        'position' => 'Bendahara',
                        'status' => 'active',
                        'is_active' => true,
                    ]);
                    $updateData['treasurer_id'] = $treasurer->id;
                } else {
                    // Validate existing treasurer
                    $treasurer = User::find($request->treasurer_id);
                    if (!$treasurer || $treasurer->school_id !== $eschool->school_id || $treasurer->role !== 'bendahara') {
                        return response()->json([
                            'message' => 'Invalid treasurer. Treasurer must be from the same school and have bendahara role.'
                        ], 422);
                    }
                    
                    // Check if treasurer is already assigned to another eschool (unless it's this one)
                    if ($treasurer->treasuredEschool && $treasurer->treasuredEschool->id !== $eschool->id) {
                        return response()->json([
                            'message' => 'This treasurer is already assigned to another eschool.'
                        ], 422);
                    }
                    
                    $updateData['treasurer_id'] = $request->treasurer_id;
                }
            }
            
            // Handle other fields
            if ($request->has('name')) $updateData['name'] = $request->name;
            if ($request->has('description')) $updateData['description'] = $request->description;
            if ($request->has('monthly_kas_amount')) $updateData['monthly_kas_amount'] = $request->monthly_kas_amount;
            if ($request->has('total_schedule_days')) $updateData['total_schedule_days'] = $request->total_schedule_days;
            if ($request->has('is_active')) $updateData['is_active'] = $request->is_active;
            
            // Handle schedule_days
            if ($request->has('schedule_days')) {
                $updateData['schedule_days'] = $request->schedule_days ? json_encode($request->schedule_days) : null;
            }
            
            // Update eschool
            $eschool->update($updateData);
            
            // Load relationships
            $eschool->load(['coordinator', 'treasurer']);
            $eschool->members_count = $eschool->members()->count();
            
            return response()->json($eschool);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to update eschool',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $user = Auth::user();
            
            // Find eschool
            $eschool = Eschool::findOrFail($id);
            
            // Check access based on user role
            if ($user->role !== 'staff') {
                return response()->json([
                    'message' => 'Unauthorized. Only staff can delete eschools.'
                ], 403);
            }
            
            if ($eschool->school_id !== $user->school_id) {
                return response()->json([
                    'message' => 'Unauthorized. You can only delete eschools in your own school.'
                ], 403);
            }
            
            // Delete eschool
            $eschool->delete();
            
            return response()->json([
                'message' => 'Eschool deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to delete eschool',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get list of eligible members for a school (those who can be selected as treasurer)
     */
    public function getEligibleTreasurers(Request $request)
    {
        try {
            $user = Auth::user();
            
            // Only staff can access this endpoint
            if ($user->role !== 'staff') {
                return response()->json([
                    'message' => 'Unauthorized. Only staff can access this endpoint.'
                ], 403);
            }
            
            $schoolId = $request->query('school_id', $user->school_id);
            
            // Ensure the school belongs to the staff's school
            if ($schoolId != $user->school_id) {
                return response()->json([
                    'message' => 'Unauthorized. You can only get members for your own school.'
                ], 403);
            }
            
            // Get users with siswa base_role from the specified school
            // These are students who can be selected as treasurer
            $members = User::where('school_id', $schoolId)
                            ->where('base_role', 'siswa')  // Changed from 'role' to 'base_role'
                            ->select('id', 'name', 'email')
                            ->get();
            
            return response()->json($members);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to retrieve members',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}