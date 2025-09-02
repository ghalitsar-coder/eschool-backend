<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Models\Eschool;
use App\Models\Member;
use App\Models\School;


class MemberController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $user = Auth::user();
            $eschoolId = $request->input('eschool_id');
            
            // Jika eschool_id diberikan, filter berdasarkan eschool
            if ($eschoolId) {
                $eschool = Eschool::where('id', $eschoolId)->firstOrFail();
                
                // Validasi bahwa user memiliki akses ke eschool ini
                if ($user->role === 'koordinator') {
                    // Koordinator hanya bisa mengakses eschool yang mereka koordinatori
                    if ($eschool->coordinator_id !== $user->id) {
                        return response()->json([
                            'message' => 'Unauthorized. You can only access your own eschool.'
                        ], 403);
                    }
                } elseif ($user->role === 'bendahara') {
                    // Bendahara hanya bisa mengakses eschool yang mereka bendaharai
                    if ($eschool->treasurer_id !== $user->id) {
                        return response()->json([
                            'message' => 'Unauthorized. You can only access your own eschool.'
                        ], 403);
                    }
                } elseif ($user->role !== 'staff') {
                    // Hanya staff, koordinator, dan bendahara yang bisa mengakses dengan eschool_id
                    return response()->json([
                        'message' => 'Unauthorized. Only staff, coordinator, or treasurer can access members by eschool.'
                    ], 403);
                }
                
                // Mengambil members yang terkait dengan eschool tertentu melalui relasi many-to-many
                // DAN memastikan mereka berasal dari sekolah yang sama dengan eschool
                $members = $eschool->members()
                    ->with('user')
                    ->where('is_active', true)
                    ->where('school_id', $eschool->school_id) // Filter by school_id to ensure consistency
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
            } 
            // Jika tidak, kembalikan members berdasarkan role pengguna
            else {
                $query = Member::with('user', 'eschools', 'school');
                
                // Filter berdasarkan role pengguna
                if ($user->role === 'koordinator') {
                    // Untuk koordinator, hanya tampilkan members dari sekolah yang sama
                    $coordinatorEschool = Eschool::where('coordinator_id', $user->id)->first();
                    if ($coordinatorEschool) {
                        $schoolId = $coordinatorEschool->school_id;
                        $query->where('school_id', $schoolId);
                    } else {
                        // Jika koordinator tidak memiliki eschool, kembalikan array kosong
                        $query->where('id', -1); // Tidak akan cocok dengan apa pun
                    }
                } elseif ($user->role === 'staff') {
                    // Untuk staff, hanya tampilkan members dari sekolah mereka
                    if ($user->school_id) {
                        $query->where('school_id', $user->school_id);
                    } else {
                        // Jika staff tidak memiliki school_id, kembalikan array kosong
                        $query->where('id', -1); // Tidak akan cocok dengan apa pun
                    }
                } elseif ($user->role === 'bendahara') {
                    // Untuk bendahara, hanya tampilkan members dari sekolah yang sama
                    $treasurerEschool = Eschool::where('treasurer_id', $user->id)->first();
                    if ($treasurerEschool) {
                        $schoolId = $treasurerEschool->school_id;
                        $query->where('school_id', $schoolId);
                    } else {
                        // Jika bendahara tidak memiliki eschool, kembalikan array kosong
                        $query->where('id', -1); // Tidak akan cocok dengan apa pun
                    }
                }
                // Untuk siswa, tidak perlu filter tambahan
                
                $members = $query->where('is_active', true)->paginate(20);

                return response()->json($members);
            }
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
        try {
            // Validasi input
            $validator = Validator::make($request->all(), [
                'school_id' => 'required|exists:schools,id',
                'eschool_ids' => 'required|array|min:1', // Array of eschool IDs
                'eschool_ids.*' => 'exists:eschools,id', // Each ID must exist in eschools table
                'nip' => 'nullable|string|max:255',
                'name' => 'required|string|max:255',
                'date_of_birth' => 'nullable|date',
                'gender' => 'nullable|string|in:L,P',
                'address' => 'nullable|string',
                'phone' => 'nullable|string|max:20',
                'status' => 'nullable|string|in:active,inactive',
                'user_id' => 'nullable|exists:users,id'
                // Tambahkan validasi lain sesuai kebutuhan
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            // Pastikan semua eschool yang dipilih berasal dari school yang sama
            $schoolId = $request->input('school_id');
            $eschoolIds = $request->input('eschool_ids');
            
            $validEschools = Eschool::whereIn('id', $eschoolIds)
                                    ->where('school_id', $schoolId)
                                    ->pluck('id')
                                    ->toArray();
            
            if (count($validEschools) !== count($eschoolIds)) {
                return response()->json([
                    'message' => 'One or more selected eschools do not belong to the specified school.'
                ], 400);
            }

            // Buat member baru
            $memberData = $request->only([
                'school_id', 'user_id', 'nip', 'name', 'date_of_birth', 'gender', 
                'address', 'phone', 'status'
            ]);
            // Set default status jika tidak disediakan
            $memberData['status'] = $memberData['status'] ?? 'active';
            $memberData['is_active'] = true; // Sesuaikan dengan field yang ada
            
            // If name is not provided but user_id is, get name from user
            if (empty($memberData['name']) && !empty($memberData['user_id'])) {
                $user = \App\Models\User::find($memberData['user_id']);
                if ($user) {
                    $memberData['name'] = $user->name;
                }
            }
            
            $member = Member::create($memberData);

            // Sinkronkan relasi many-to-many dengan eschools
            $member->eschools()->sync($eschoolIds);

            return response()->json([
                'message' => 'Member created successfully',
                'member' => $member->load('eschools') // Load relasi untuk respons
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error occurred while creating member',
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
            $member = Member::with('eschools', 'school', 'user')->findOrFail($id);
            return response()->json($member);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Member not found',
                'error' => $e->getMessage()
            ], 404);
        }
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
        try {
            $member = Member::findOrFail($id);

            // Validasi input
            $validator = Validator::make($request->all(), [
                'school_id' => 'sometimes|required|exists:schools,id',
                'eschool_ids' => 'sometimes|required|array|min:1', // Array of eschool IDs
                'eschool_ids.*' => 'exists:eschools,id', // Each ID must exist in eschools table
                'nip' => 'nullable|string|max:255',
                'name' => 'sometimes|required|string|max:255',
                'date_of_birth' => 'nullable|date',
                'gender' => 'nullable|string|in:L,P',
                'address' => 'nullable|string',
                'phone' => 'nullable|string|max:20',
                'status' => 'nullable|string|in:active,inactive',
                // Tambahkan validasi lain sesuai kebutuhan
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            // Jika school_id diupdate, pastikan eschool_ids juga valid
            $schoolId = $request->input('school_id', $member->school_id);
            $eschoolIds = $request->input('eschool_ids');
            
            if ($eschoolIds) {
                $validEschools = Eschool::whereIn('id', $eschoolIds)
                                        ->where('school_id', $schoolId)
                                        ->pluck('id')
                                        ->toArray();
                
                if (count($validEschools) !== count($eschoolIds)) {
                    return response()->json([
                        'message' => 'One or more selected eschools do not belong to the specified school.'
                    ], 400);
                }
                
                // Sinkronkan relasi many-to-many dengan eschools
                $member->eschools()->sync($eschoolIds);
            } elseif ($request->has('eschool_ids')) {
                // Jika eschool_ids dikirim sebagai array kosong, hapus semua relasi
                $member->eschools()->sync([]);
            }

            // Update data member
            $memberData = $request->only([
                'school_id', 'nip', 'name', 'date_of_birth', 'gender', 
                'address', 'phone', 'status'
            ]);
            
            // Hanya update school_id jika diberikan dan berbeda
            if ($request->has('school_id')) {
                $memberData['school_id'] = $schoolId;
            }
            
            // If name is not provided but user_id is being updated, get name from user
            if ($request->has('user_id') && empty($memberData['name'])) {
                $user = \App\Models\User::find($request->input('user_id'));
                if ($user) {
                    $memberData['name'] = $user->name;
                }
            }
            
            $member->update($memberData);

            return response()->json([
                'message' => 'Member updated successfully',
                'member' => $member->load('eschools') // Load relasi untuk respons
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error occurred while updating member',
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
            $member = Member::findOrFail($id);
            
            // Hapus relasi many-to-many
            $member->eschools()->detach();
            
            // Hapus member
            $member->delete();

            return response()->json(['message' => 'Member deleted successfully']);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error occurred while deleting member',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
