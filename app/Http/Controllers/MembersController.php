<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\UserEschoolRole;
use App\Models\Profile;
use Illuminate\Support\Facades\Auth;
class MembersController extends Controller
{
    /**
     * Get list of members for a specific eschool
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function list(Request $request)
    {
        // Get eschool ID from middleware
        $user = Auth::user();
        $eschoolId = $user->userEschoolRoles()
        ->where('role', 'coordinator') // Contoh filter role; ganti atau hapus sesuai kasus
        ->with('eschool') // Eager load jika butuh detail eschool
        ->first()
        ->eschool_id ?? null;
        \Log::info(['berikut eschoolid ' => $eschoolId]);
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
    public function getMembers(Request $request)
    {

        try {
            $user = Auth::user();
            $eschoolId = $user->userEschoolRoles()
            ->where('role', 'treasurer') // Contoh filter role; ganti atau hapus sesuai kasus
            ->with('eschool') // Eager load jika butuh detail eschool
            ->first()
            ->eschool_id ?? null;

            \Log::info(["MOOMOOOO ESHCOOL"=> $eschoolId]);
            // Get members with role 'member' or 'treasurer' for the specified eschool
            // Both members and treasurers should be included as they can make payments
            $members = \App\Models\UserEschoolRole::where('eschool_id', $eschoolId)
                ->whereIn('role', ['member', 'treasurer'])
                ->with(['user.profile', 'eschool'])
                ->get()
                ->map(function ($userEschoolRole) {
                    return [
                        'id' => $userEschoolRole->user->id,
                        'name' => $userEschoolRole->user->name,
                        'email' => $userEschoolRole->user->email,
                        'profile' => $userEschoolRole->user->profile,
                        'eschool_role_id' => $userEschoolRole->id,
                        'eschool_id' => $userEschoolRole->eschool_id,
                        'eschool_name' => $userEschoolRole->eschool ? $userEschoolRole->eschool->name : null,
                        'created_at' => $userEschoolRole->created_at,
                        'updated_at' => $userEschoolRole->updated_at
                    ];
                });

            // Get the eschool details
            $eschool = \App\Models\Eschool::find($eschoolId);

            return response()->json([
                'message' => 'Members retrieved successfully',
                'eschool' => $eschool,
                'members' => $members
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to retrieve members',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}