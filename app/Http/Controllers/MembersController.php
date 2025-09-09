<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\UserEschoolRole;
use App\Models\Profile;

class MembersController extends Controller
{
    /**
     * Get list of members for a specific eschool
     * 
     * @param int $eschoolId
     * @return \Illuminate\Http\JsonResponse
     */
    public function list($eschoolId)
    {
        try {
            // Get active members for the specified eschool
            // Include members and treasurers as they are both considered members for attendance purposes
            $members = UserEschoolRole::where('eschool_id', $eschoolId)
                ->whereIn('role', ['member', 'treasurer'])
                ->with([
                    'user.profile' => function ($query) {
                        // Filter for active profiles only
                        $query->where('status', 'active');
                    },
                    'user.student'
                ])
                ->get()
                ->filter(function ($userEschoolRole) {
                    // Additional filtering to ensure we only get users with active profiles
                    return $userEschoolRole->user && 
                           $userEschoolRole->user->profile && 
                           $userEschoolRole->user->profile->status === 'active';
                })
                ->map(function ($userEschoolRole) {
                    return [
                        'user_id' => $userEschoolRole->user->id,
                        'name' => $userEschoolRole->user->profile->name,
                        'student_id' => $userEschoolRole->user->student->student_id ?? 'N/A'
                    ];
                })
                ->values(); // Reset array keys after filtering

            return response()->json([
                'success' => true,
                'data' => $members
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve members',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}