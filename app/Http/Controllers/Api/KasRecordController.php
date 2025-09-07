<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreKasRecordRequest;
use App\Http\Requests\StoreKasIncomeRequest;
use App\Http\Requests\UpdateKasRecordRequest;
use App\Models\KasRecord;
use App\Models\KasPayment;
use App\Models\UserEschoolRole;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class KasRecordController extends Controller
{
    /**
     * Create a new financial record
     *
     * @param StoreKasRecordRequest $request
     * @return JsonResponse
     */
    public function store(StoreKasRecordRequest $request): JsonResponse
    {
        try {
            $validatedData = $request->validated();

            // Check if user has treasurer role for this eschool
            $user = auth()->user();
            $treasurerRole = UserEschoolRole::where('user_id', $user->id)
                ->where('eschool_id', $validatedData['eschool_id'])
                ->where('role', 'treasurer')
                ->first();

            if (!$treasurerRole) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to create financial records for this eschool.'
                ], 403);
            }

            // For expense records, ensure amount is negative
            if ($validatedData['category'] !== 'income') {
                $validatedData['amount'] = abs($validatedData['amount']) * -1;
            }

            // Create the kas record
            $kasRecord = KasRecord::create([
                'eschool_id' => $validatedData['eschool_id'],
                'description' => $validatedData['description'],
                'category' => $validatedData['category'],
                'amount' => $validatedData['amount'],
                'date' => $validatedData['date'],
                'recorder_id' => $treasurerRole->id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Financial record created successfully.',
                'data' => [
                    'kas_record' => $kasRecord
                ]
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create financial record.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create a new income record with bulk payments
     *
     * @param StoreKasIncomeRequest $request
     * @return JsonResponse
     */
    public function storeIncomeWithPayments(StoreKasIncomeRequest $request): JsonResponse
    {
        try {
            $validatedData = $request->validated();
            
            // Check if user has treasurer role for this eschool
            $user = auth()->user();
            $treasurerRole = UserEschoolRole::where('user_id', $user->id)
                ->where('eschool_id', $validatedData['eschool_id'])
                ->where('role', 'treasurer')
                ->first();

            if (!$treasurerRole) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to create financial records for this eschool.'
                ], 403);
            }

            // Validate for duplicate payments before processing
            $duplicatePayments = [];
            foreach ($validatedData['payments'] as $payment) {
                // Check if member already paid for the same month and year
                $existingPayment = KasPayment::where('member_id', $payment['member_id'])
                    ->where('month', $payment['month'])
                    ->where('year', $payment['year'])
                    ->first();

                if ($existingPayment) {
                    // Get member name for error message
                    $memberRole = UserEschoolRole::with('user.profile')->where('user_id', $payment['member_id'])->first();
                    $memberName = $memberRole && $memberRole->user && $memberRole->user->profile 
                        ? $memberRole->user->profile->name 
                        : 'Unknown Member';
                    
                    $duplicatePayments[] = [
                        'member_id' => $payment['member_id'],
                        'member_name' => $memberName,
                        'month' => $payment['month'],
                        'year' => $payment['year']
                    ];
                }
            }

            // If there are duplicate payments, return error
            if (!empty($duplicatePayments)) {
                $errorMessages = [];
                foreach ($duplicatePayments as $dup) {
                    $errorMessages[] = "Member {$dup['member_name']} sudah membayar di bulan {$dup['month']} dan tahun {$dup['year']}";
                }

                return response()->json([
                    'success' => false,
                    'message' => implode('. ', $errorMessages),
                    'errors' => [
                        'duplicate_payments' => $duplicatePayments
                    ]
                ], 422);
            }

            // Calculate total amount from payments
                $totalAmount = collect($validatedData['payments'])->sum('amount');
                
                // Ensure amount is positive for income
                $totalAmount = abs($totalAmount);

            // Use database transaction to ensure data consistency
            DB::beginTransaction();

            try {
                // Create the kas record for income
                $kasRecord = KasRecord::create([
                    'eschool_id' => $validatedData['eschool_id'],
                    'description' => $validatedData['description'],
                    'category' => 'income', // Default category for this endpoint
                    'amount' => $totalAmount,
                    'date' => $validatedData['date'],
                    'recorder_id' => $treasurerRole->id,
                ]);

                // Create kas payments for each member
                $kasPayments = [];
                foreach ($validatedData['payments'] as $payment) {
                    // Verify member belongs to the same eschool
                    $member = UserEschoolRole::where('user_id', $payment['member_id'])->first();
                    if (!$member || $member->eschool_id != $validatedData['eschool_id'] || !in_array($member->role, ['member', 'treasurer'])) {
                        DB::rollBack();
                        return response()->json([
                            'success' => false,
                            'message' => 'Invalid member for this eschool.'
                        ], 400);
                    }

                    $kasPayment = KasPayment::create([
                        'kas_record_id' => $kasRecord->id,
                        'member_id' => $payment['member_id'],
                        'amount' => abs($payment['amount']), // Ensure amount is positive
                        'month' => $payment['month'],
                        'year' => $payment['year'],
                        'is_paid' => true, // Payments through this endpoint are considered paid
                    ]);

                    $kasPayments[] = $kasPayment;
                }

                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => 'Income record and member payments created successfully.',
                    'data' => [
                        'kas_record' => $kasRecord,
                        'kas_payments' => $kasPayments
                    ]
                ], 201);
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create income record and member payments.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all financial records for an eschool
     *
     * @param int $eschoolId
     * @return JsonResponse
     */
    public function index($eschoolId): JsonResponse
    {
        try {
            // Check if eschool exists
            $eschool = \App\Models\Eschool::find($eschoolId);
            if (!$eschool) {
                return response()->json([
                    'success' => false,
                    'message' => 'Eschool not found.'
                ], 404);
            }

            // Check if user has access (treasurer or staff)
            $user = auth()->user();
            $userRole = UserEschoolRole::where('user_id', $user->id)
                ->where('eschool_id', $eschoolId)
                ->first();

            // If user has no role in this eschool, check if they are staff
            $isStaff = false;
            if (!$userRole) {
                $staffRole = UserEschoolRole::where('user_id', $user->id)
                    ->where('role', 'supervisor')
                    ->whereHas('eschool', function ($query) use ($eschoolId) {
                        $query->where('school_id', \App\Models\Eschool::find($eschoolId)->school_id);
                    })
                    ->first();
                
                $isStaff = !!$staffRole;
            }

            // If user is not treasurer, member, or staff of this school, deny access
            if (!$userRole && !$isStaff) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to view financial records for this eschool.'
                ], 403);
            }

            // Get kas records for this eschool
            $kasRecords = KasRecord::where('eschool_id', $eschoolId)
                ->with(['kasPayments.member.user.profile', 'recorder.user.profile'])
                ->get();

            // Calculate summary
            $totalIncome = $kasRecords->where('amount', '>', 0)->sum('amount');
            $totalExpense = $kasRecords->where('amount', '<', 0)->sum('amount');
            $balance = $totalIncome + $totalExpense;

            return response()->json([
                'success' => true,
                'message' => 'Financial records retrieved successfully.',
                'data' => [
                    'kas_records' => $kasRecords,
                    'summary' => [
                        'total_income' => $totalIncome,
                        'total_expense' => abs($totalExpense),
                        'balance' => $balance
                    ]
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve financial records.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update a financial record
     *
     * @param UpdateKasRecordRequest $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(UpdateKasRecordRequest $request, $id): JsonResponse
    {
        try {
            $validatedData = $request->validated();

            // Find the kas record
            $kasRecord = KasRecord::find($id);
            if (!$kasRecord) {
                return response()->json([
                    'success' => false,
                    'message' => 'Financial record not found.'
                ], 404);
            }

            // Check if user has treasurer role for this eschool and is the recorder
            $user = auth()->user();
            if ($kasRecord->recorder_id != UserEschoolRole::where('user_id', $user->id)
                    ->where('eschool_id', $kasRecord->eschool_id)
                    ->where('role', 'treasurer')
                    ->first()->id ?? null) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to update this financial record.'
                ], 403);
            }

            // Update the kas record
            $kasRecord->update($validatedData);

            return response()->json([
                'success' => true,
                'message' => 'Financial record updated successfully.',
                'data' => [
                    'kas_record' => $kasRecord
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update financial record.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a financial record
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy($id): JsonResponse
    {
        try {
            // Find the kas record
            $kasRecord = KasRecord::find($id);
            if (!$kasRecord) {
                return response()->json([
                    'success' => false,
                    'message' => 'Financial record not found.'
                ], 404);
            }

            // Check if user has treasurer role for this eschool and is the recorder
            $user = auth()->user();
            if ($kasRecord->recorder_id != UserEschoolRole::where('user_id', $user->id)
                    ->where('eschool_id', $kasRecord->eschool_id)
                    ->where('role', 'treasurer')
                    ->first()->id ?? null) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to delete this financial record.'
                ], 403);
            }

            // Delete the kas record
            $kasRecord->delete();

            return response()->json([
                'success' => true,
                'message' => 'Financial record deleted successfully.'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete financial record.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}