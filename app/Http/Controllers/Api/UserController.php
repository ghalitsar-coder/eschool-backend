<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Profile;
use App\Models\Teacher;
use App\Models\Student;
use App\Models\School;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    /**
     * Create a new user (teacher or student).
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function createUser(Request $request)
    {
        // Get the authenticated user
        $authUser = Auth::user();
        
        // Check if the authenticated user has a teacher profile
        if (!$authUser->teacher) {
            return response()->json(['error' => 'Unauthorized. Only supervisors can create users.'], 403);
        }
        
        // Get the school_id from the authenticated user's teacher profile
        $schoolId = $authUser->teacher->school_id;

        // Validate the request
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6',
            'user_type' => 'required|in:teacher,student',
            'date_of_birth' => 'required|date',
            'gender' => 'required|in:M,F',
            'address' => 'nullable|string|max:500',
            'license_number' => 'required_if:user_type,teacher|string|max:255',
            'student_id' => 'required_if:user_type,student|string|max:255',
            'grade_level' => 'required_if:user_type,student|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            // Start a database transaction
            \DB::beginTransaction();

            // Create the profile
            $profile = Profile::create([
                'name' => $request->name,
                'date_of_birth' => $request->date_of_birth,
                'gender' => $request->gender,
                'address' => $request->address,
                // status will use default value 'active'
            ]);

            // Create the user
            $user = User::create([
                'profile_id' => $profile->id,
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ]);

            // Create teacher or student record based on user_type
            if ($request->user_type === 'teacher') {
                Teacher::create([
                    'profile_id' => $profile->id,
                    'license_number' => $request->license_number,
                    'school_id' => $schoolId, // Use school_id from authenticated user
                ]);
            } elseif ($request->user_type === 'student') {
                Student::create([
                    'profile_id' => $profile->id,
                    'student_id' => $request->student_id,
                    'grade_level' => $request->grade_level,
                    'school_id' => $schoolId, // Use school_id from authenticated user
                ]);
            }

            // Commit the transaction
            \DB::commit();

            return response()->json([
                'message' => 'User created successfully',
                'data' => $user
            ], 201);
        } catch (\Exception $e) {
            // Rollback the transaction on error
            \DB::rollback();
            
            return response()->json([
                'error' => 'Failed to create user',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}