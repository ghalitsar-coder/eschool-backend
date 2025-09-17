<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreKasPaymentRequest;
use App\Http\Requests\UpdateKasPaymentRequest;
use App\Models\KasPayment;
use App\Models\KasRecord;
use App\Models\UserEschoolRole;
use Illuminate\Http\JsonResponse;

class KasPaymentController extends Controller
{
    /**
     * Create a payment record
     *
     * @param StoreKasPaymentRequest $request
     * @return JsonResponse
     */
    public function store(StoreKasPaymentRequest $request): JsonResponse
    {
        try {
            $validatedData = $request->validated();

            // Get the kas record
            $kasRecord = KasRecord::find($validatedData['kas_record_id']);
            
            // Check if user has treasurer role for this eschool
            $user = auth()->user();
            $treasurerRole = UserEschoolRole::where('user_id', $user->id)
                ->where('eschool_id', $kasRecord->eschool_id)
                ->where('role', 'treasurer')
                ->first();

            if (!$treasurerRole) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to create payment records for this eschool.'
                ], 403);
            }

            // Check if member belongs to the same eschool
            $member = UserEschoolRole::find($validatedData['member_id']);
            if (!$member || $member->eschool_id != $kasRecord->eschool_id || !in_array($member->role, ['member', 'treasurer'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid member for this eschool.'
                ], 400);
            }

            // Create the payment record
            $kasPayment = KasPayment::create($validatedData);

            return response()->json([
                'success' => true,
                'message' => 'Payment record created successfully.',
                'data' => [
                    'kas_payment' => $kasPayment
                ]
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create payment record.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get payment status for a member
     *
     * @param int $userEschoolRoleId
     * @return JsonResponse
     */
    public function getMemberPayments($userEschoolRoleId): JsonResponse
    {
        try {
            // Find the member
            $member = UserEschoolRole::find($userEschoolRoleId);
            if (!$member) {
                return response()->json([
                    'success' => false,
                    'message' => 'Member not found.'
                ], 404);
            }

            // Check if authenticated user has access (the member themselves, treasurer, or staff)
            $user = auth()->user();
            $hasAccess = false;

            // Check if user is the member
            if ($member->user_id == $user->id) {
                $hasAccess = true;
            }

            // Check if user is treasurer of the same eschool
            $treasurerRole = UserEschoolRole::where('user_id', $user->id)
                ->where('eschool_id', $member->eschool_id)
                ->where('role', 'treasurer')
                ->first();

            if ($treasurerRole) {
                $hasAccess = true;
            }

            // Check if user is staff of the same school
            $staffRole = UserEschoolRole::where('user_id', $user->id)
                ->where('role', 'supervisor')
                ->whereHas('eschool', function ($query) use ($member) {
                    $query->where('school_id', $member->eschool->school_id);
                })
                ->first();

            if ($staffRole) {
                $hasAccess = true;
            }

            if (!$hasAccess) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to view payment information for this member.'
                ], 403);
            }

            // Get payment records for this member
            $payments = KasPayment::where('member_id', $userEschoolRoleId)
                ->with('kasRecord')
                ->get();

            // Calculate outstanding balance
            $totalPaid = $payments->where('is_paid', true)->sum('amount');
            $totalExpected = $member->eschool->monthly_fee_amount * $payments->count();
            $outstandingBalance = $totalExpected - $totalPaid;

            return response()->json([
                'success' => true,
                'message' => 'Payment information retrieved successfully.',
                'data' => [
                    'member' => $member->load('user.profile', 'eschool'),
                    'payments' => $payments,
                    'outstanding_balance' => $outstandingBalance
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve payment information.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get payment summary for an eschool
     *
     * @param int $eschoolId
     * @return JsonResponse
     */
    public function getEschoolPaymentSummary($eschoolId): JsonResponse
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
                    'message' => 'You do not have permission to view payment summary for this eschool.'
                ], 403);
            }

            // Get all members for this eschool
            $members = UserEschoolRole::where('eschool_id', $eschoolId)
                ->whereIn('role', ['member', 'treasurer'])
                ->get();

            $totalMembers = $members->count();
            
            // Get all payment records for this eschool
            $kasRecords = \App\Models\KasRecord::where('eschool_id', $eschoolId)->get();
            $paymentRecords = \App\Models\KasPayment::whereIn('kas_record_id', $kasRecords->pluck('id'))
                ->get();

            $paidMembers = $paymentRecords->where('is_paid', true)->unique('member_id')->count();
            $unpaidMembers = $totalMembers - $paidMembers;
            $paymentPercentage = $totalMembers > 0 ? ($paidMembers / $totalMembers) * 100 : 0;

            $totalExpected = $totalMembers * $eschool->monthly_fee_amount;
            $totalCollected = $paymentRecords->where('is_paid', true)->sum('amount');
            
            // Calculate total income and expense
            $totalIncome = $kasRecords->where('amount', '>', 0)->sum('amount');
            $totalExpense = $kasRecords->where('amount', '<', 0)->sum('amount');
            $balance = $totalIncome + $totalExpense; // Expenses are negative, so we add them

            return response()->json([
                'success' => true,
                'message' => 'Payment summary retrieved successfully.',
                'data' => [
                    'eschool' => $eschool,
                    'summary' => [
                        'total_members' => $totalMembers,
                        'paid_members' => $paidMembers,
                        'unpaid_members' => $unpaidMembers,
                        'payment_percentage' => $paymentPercentage,
                        'total_expected' => $totalExpected,
                        'total_collected' => $totalCollected,
                        'total_income' => abs($totalIncome), // Make it positive for display
                        'total_expense' => abs($totalExpense), // Make it positive for display
                        'balance' => $balance
                    ]
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve payment summary.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update payment status
     *
     * @param UpdateKasPaymentRequest $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(UpdateKasPaymentRequest $request, $id): JsonResponse
    {
        try {
            $validatedData = $request->validated();

            // Find the payment record
            $kasPayment = KasPayment::find($id);
            if (!$kasPayment) {
                return response()->json([
                    'success' => false,
                    'message' => 'Payment record not found.'
                ], 404);
            }

            // Check if user has treasurer role for this eschool
            $user = auth()->user();
            $kasRecord = $kasPayment->kasRecord;
            $treasurerRole = UserEschoolRole::where('user_id', $user->id)
                ->where('eschool_id', $kasRecord->eschool_id)
                ->where('role', 'treasurer')
                ->first();

            if (!$treasurerRole) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to update payment records for this eschool.'
                ], 403);
            }

            // Update the payment record
            $kasPayment->update($validatedData);

            return response()->json([
                'success' => true,
                'message' => 'Payment record updated successfully.',
                'data' => [
                    'kas_payment' => $kasPayment
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update payment record.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}