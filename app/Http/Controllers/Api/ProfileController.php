<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\UserEschoolRole;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\School;
use App\Models\Profile;

class ProfileController extends Controller
{
    /**
     * Get profile data for the authenticated user
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getProfile(Request $request)
    {
        try {
            // Get the authenticated user with profile and related data
            $user = Auth::user()->load(['profile', 'userEschoolRoles.eschool.school']);
            
            if (!$user) {
                return response()->json([
                    'message' => 'Unauthenticated',
                    'error' => 'No authenticated user found'
                ], 401);
            }
            
            // Get user's profile data
            $profile = $user->profile;
            
            // Check if user is a student or teacher
            $student = Student::where('profile_id', $profile->id)->first();
            $teacher = \App\Models\Teacher::where('profile_id', $profile->id)->first();
            
            // Get user's eschool roles
            $userEschoolRoles = $user->userEschoolRoles;
            
            // Prepare eschools data
            $eschools = [];
            $totalEschools = 0;
            
            foreach ($userEschoolRoles as $userEschoolRole) {
                $eschool = $userEschoolRole->eschool;
                
                // Handle supervisor roles (eschool_id is null for supervisors)
                if (!$eschool) {
                    // For supervisors, get all eschools from the same school
                    if ($userEschoolRole->role === 'supervisor' && $teacher) {
                        // Get all eschools from the same school as the supervisor
                        $schoolEschools = \App\Models\Eschool::where('school_id', $teacher->school_id)->get();
                        
                        foreach ($schoolEschools as $schoolEschool) {
                            $eschools[] = [
                                'id' => $schoolEschool->id,
                                'name' => $schoolEschool->name,
                                'description' => $schoolEschool->description,
                                'is_active' => $schoolEschool->is_active,
                                'monthly_fee_amount' => $schoolEschool->monthly_fee_amount,
                                'schedule_days' => $schoolEschool->schedule_days,
                                'school_name' => $schoolEschool->school->name ?? null,
                                'school_id' => $schoolEschool->school_id,
                                'role' => 'supervisor_view', // Indicate this is a school eschool viewed by supervisor
                                'joined_at' => $userEschoolRole->created_at->format('Y-m-d'),
                            ];
                            $totalEschools++;
                        }
                    }
                    continue;
                }
                
                $school = $eschool->school;
                
                $eschools[] = [
                    'id' => $eschool->id,
                    'name' => $eschool->name,
                    'description' => $eschool->description,
                    'is_active' => $eschool->is_active,
                    'monthly_fee_amount' => $eschool->monthly_fee_amount,
                    'schedule_days' => $eschool->schedule_days,
                    'school_name' => $school->name,
                    'school_id' => $school->id,
                    'role' => $userEschoolRole->role,
                    'joined_at' => $userEschoolRole->created_at->format('Y-m-d'),
                ];
                
                $totalEschools++;
            }
            
            // Prepare response data based on user type
            $responseData = [
                'user' => [
                    'id' => $user->id,
                    'name' => $profile->name,
                    'email' => $user->email,
                    'email_verified_at' => $user->email_verified_at,
                    'role' => $this->getUserRole($userEschoolRoles),
                    'status' => $profile->status,
                    'created_at' => $user->created_at->format('Y-m-d H:i:s'),
                ],
                'profile' => [
                    'id' => $profile->id,
                    'name' => $profile->name,
                    'date_of_birth' => $profile->date_of_birth,
                    'gender' => $profile->gender,
                    'address' => $profile->address,
                    'status' => $profile->status,
                ],
                'student' => $student ? [
                    'id' => $student->id,
                    'profile_id' => $student->profile_id,
                    'school_id' => $student->school_id,
                    'student_id' => $student->student_id,
                    'grade_level' => $student->grade_level,
                    'school_name' => \App\Models\School::find($student->school_id)->name ?? null,
                ] : null,
                'teacher' => $teacher ? [
                    'id' => $teacher->id,
                    'profile_id' => $teacher->profile_id,
                    'school_id' => $teacher->school_id,
                    'license_number' => $teacher->license_number,
                    'school_name' => \App\Models\School::find($teacher->school_id)->name ?? null,
                ] : null,
                'eschools' => $eschools,
                'summary' => [
                    'total_eschools' => $totalEschools,
                    'roles' => $userEschoolRoles->pluck('role')->unique()->values()->toArray(),
                    'schools_involved' => $userEschoolRoles->filter(function($role) {
                        return $role->eschool !== null; // Filter out supervisor roles
                    })->map(function($role) {
                        return $role->eschool->school->name;
                    })->unique()->values()->toArray(),
                ],
            ];
            
            return response()->json($responseData, 200);
            
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error retrieving profile data',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Get user's primary role based on their eschool roles
     *
     * @param \Illuminate\Database\Eloquent\Collection $userEschoolRoles
     * @return string
     */
    private function getUserRole($userEschoolRoles)
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
}