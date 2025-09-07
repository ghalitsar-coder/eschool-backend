<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\UserEschoolRole;

class MemberController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }
    public function getMembersByEschool(Request $request, $eschoolId)
    {
        \Log::info("INI ESCHOOL ID ".$eschoolId);

        try {
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

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }


    
}
