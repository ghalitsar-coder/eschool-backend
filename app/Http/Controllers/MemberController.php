<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Eschool;
use App\Models\Member;


class MemberController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $eschoolId = $request->input('eschool_id');
            // Ambil members yang aktif
            $eschool = Eschool::where('id', $eschoolId)->firstOrFail();
            

            $members = Member::with('user')
                ->where('eschool_id', $eschool->id)
                ->where('is_active', true)
                ->get()
                ->map(function ($member) {
                    return [
                        'id' => $member->id,
                        'student_id' => $member->student_id,
                        'name' => $member->user ? $member->user->name : 'N/A',
                        'email' => $member->user ? $member->user->email : 'N/A',
                        'phone' => $member->phone,
                    ];
                });

            return response()->json([
                'eschool' => [
                    'id' => $eschool->id,
                    'name' => $eschool->name,
                    'monthly_kas_amount' => $eschool->monthly_kas_amount,
                ],
                'members' => $members,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error occurred',
                'error' => $e->getMessage()
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
