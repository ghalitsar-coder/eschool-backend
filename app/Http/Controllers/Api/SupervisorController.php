<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Eschool;
use App\Models\School;


class SupervisorController extends Controller
{
    /**
     * Get list of eligible users for treasurer role in supervisor's school.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getEligibleTreasurers()
    {
        $user = Auth::user();
        $schoolId = $user->school_id;

        // Validasi role supervisor
        if (!$schoolId) {
            return response()->json(['error' => 'Akses ditolak. Anda bukan supervisor.'], 403);
        }

        // Dapatkan semua user yang merupakan siswa di sekolah ini
        // dan belum menjadi bendahara di eschool manapun
        $eligibleStudents = User::whereHas('student', function ($query) use ($schoolId) {
            $query->where('school_id', $schoolId);
        })
        ->whereDoesntHave('userEschoolRoles', function ($query) {
            $query->where('role', 'treasurer');
        })
        ->with(['profile', 'profile.student']) // Load profile and student data
        ->get()
        ->map(function ($user) {
            // Combine user, profile, and student data
            return [
                'user_id' => $user->id,
                'name' => $user->profile->name,
                'email' => $user->email,
                'profile_id' => $user->profile->id,
                'date_of_birth' => $user->profile->date_of_birth,
                'gender' => $user->profile->gender,
                'address' => $user->profile->address,
                'student_id' => $user->profile->student->id,
                'school_id' => $user->profile->student->school_id,
                'student_number' => $user->profile->student->student_id,
                'grade_level' => $user->profile->student->grade_level,
                'student_created_at' => $user->profile->student->created_at,
                'student_updated_at' => $user->profile->student->updated_at,
            ];
        });

        return response()->json([
            'message' => 'Daftar user yang memenuhi syarat untuk menjadi bendahara',
            'data' => $eligibleStudents
        ]);
    }

    /**
     * Get list of eligible users for coordinator role in supervisor's school.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getEligibleCoordinators()
    {
        $user = Auth::user();
        $schoolId = $user->school_id;

        // Validasi role supervisor
        if (!$schoolId) {
            return response()->json(['error' => 'Akses ditolak. Anda bukan supervisor.'], 403);
        }

        // Dapatkan semua user yang merupakan guru di sekolah ini
        // dan belum menjadi koordinator di eschool manapun
        $eligibleTeachers = User::whereHas('teacher', function ($query) use ($schoolId) {
            $query->where('school_id', $schoolId);
        })
        ->whereDoesntHave('userEschoolRoles', function ($query) {
            // Filter out users who are already coordinators or supervisors
            $query->whereIn('role', ['coordinator', 'supervisor']);
        })
        ->with(['profile', 'profile.teacher']) // Load profile and teacher data
        ->get()
        ->map(function ($user) {
            // Combine user, profile, and teacher data
            return [
                'user_id' => $user->id,
                'name' => $user->profile->name,
                'email' => $user->email,
                'profile_id' => $user->profile->id,
                'date_of_birth' => $user->profile->date_of_birth,
                'gender' => $user->profile->gender,
                'address' => $user->profile->address,
                'teacher_id' => $user->profile->teacher->id,
                'license_number' => $user->profile->teacher->license_number,
                'school_id' => $user->profile->teacher->school_id,
                'teacher_created_at' => $user->profile->teacher->created_at,
                'teacher_updated_at' => $user->profile->teacher->updated_at,
            ];
        });

        return response()->json([
            'message' => 'Daftar user yang memenuhi syarat untuk menjadi koordinator',
            'data' => $eligibleTeachers
        ]);
    }
}