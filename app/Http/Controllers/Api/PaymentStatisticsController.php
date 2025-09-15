<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Eschool;
use App\Models\UserEschoolRole;
use App\Models\KasPayment;
use App\Models\KasRecord;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PaymentStatisticsController extends Controller
{
    /**
     * Get payment statistics for all members in an eschool
     */
    public function getEschoolPaymentStatistics(Request $request, $eschoolId)
    {
        try {
            // Get eschool with monthly fee
            $eschool = Eschool::findOrFail($eschoolId);
            $monthlyFee = $eschool->monthly_fee_amount;

            // Get all members of this eschool
            $members = UserEschoolRole::where('eschool_id', $eschoolId)
                ->whereIn('role', ['member', 'treasurer'])
                ->with(['user.profile', 'user.student'])
                ->get();

            $statistics = [];

            foreach ($members as $member) {
                // Get payment summary for this member
                $paymentSummary = $this->getMemberPaymentSummary($member->id, $monthlyFee);
                
                $statistics[] = [
                    'member_id' => $member->id,
                    'user_id' => $member->user_id,
                    'member_name' => $member->user->profile->name,
                    'student_id' => $member->user->student->student_id ?? null,
                    'grade_level' => $member->user->student->grade_level ?? null,
                    'monthly_fee' => $monthlyFee,
                    'total_paid' => $paymentSummary['total_paid'],
                    'total_months_paid' => $paymentSummary['total_months_paid'],
                    'average_percentage' => $paymentSummary['average_percentage'],
                    'periods' => $paymentSummary['periods']
                ];
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'eschool' => [
                        'id' => $eschool->id,
                        'name' => $eschool->name,
                        'monthly_fee_amount' => $monthlyFee
                    ],
                    'members' => $statistics
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch payment statistics',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get detailed payment history for a specific member
     */
    public function getMemberPaymentDetails(Request $request, $memberId)
    {
        try {
            $member = UserEschoolRole::with(['user.profile', 'user.student', 'eschool'])
                ->findOrFail($memberId);

            $monthlyFee = $member->eschool->monthly_fee_amount;
            $paymentSummary = $this->getMemberPaymentSummary($memberId, $monthlyFee);

            return response()->json([
                'success' => true,
                'data' => [
                    'member' => [
                        'id' => $member->id,
                        'user_id' => $member->user_id,
                        'name' => $member->user->profile->name,
                        'student_id' => $member->user->student->student_id ?? null,
                        'grade_level' => $member->user->student->grade_level ?? null,
                        'eschool' => [
                            'id' => $member->eschool->id,
                            'name' => $member->eschool->name,
                            'monthly_fee_amount' => $monthlyFee
                        ]
                    ],
                    'payment_summary' => $paymentSummary
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch member payment details',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get payment details for a specific member and period
     */
    public function getMemberPeriodPayments(Request $request, $memberId, $month, $year)
    {
        try {
            $member = UserEschoolRole::with(['user.profile', 'eschool'])
                ->findOrFail($memberId);

            // Get all payments for this member in the specified period
            $payments = KasPayment::where('member_id', $memberId)
                ->where('month', $month)
                ->where('year', $year)
                ->with(['kasRecord'])
                ->orderBy('paid_date', 'desc')
                ->get();

            $totalPaid = $payments->sum('amount');
            $monthlyFee = $member->eschool->monthly_fee_amount;
            $percentage = $monthlyFee > 0 ? round(($totalPaid / $monthlyFee) * 100, 2) : 0;

            return response()->json([
                'success' => true,
                'data' => [
                    'member' => [
                        'id' => $member->id,
                        'name' => $member->user->profile->name,
                        'eschool_name' => $member->eschool->name
                    ],
                    'period' => [
                        'month' => $month,
                        'year' => $year,
                        'monthly_fee' => $monthlyFee,
                        'total_paid' => $totalPaid,
                        'percentage' => $percentage
                    ],
                    'payments' => $payments->map(function ($payment) {
                        return [
                            'id' => $payment->id,
                            'amount' => $payment->amount,
                            'is_paid' => $payment->is_paid,
                            'paid_date' => $payment->paid_date,
                            'created_at' => $payment->created_at,
                            'kas_record' => [
                                'id' => $payment->kasRecord->id,
                                'description' => $payment->kasRecord->description,
                                'category' => $payment->kasRecord->category
                            ]
                        ];
                    })
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch period payment details',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Helper method to calculate member payment summary
     * Focus on per-month payment status instead of total
     */
    private function getMemberPaymentSummary($memberId, $monthlyFee)
    {
        // Get all payments for this member grouped by month/year
        $payments = KasPayment::where('member_id', $memberId)
            ->select(
                'month',
                'year',
                DB::raw('SUM(amount) as total_amount'),
                DB::raw('COUNT(*) as payment_count'),
                DB::raw('GROUP_CONCAT(paid_date ORDER BY paid_date DESC) as payment_dates')
            )
            ->groupBy('month', 'year')
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->get();

        // Calculate periods with per-month percentage
        $periods = $payments->map(function ($payment) use ($monthlyFee) {
            $periodPercentage = $monthlyFee > 0 ? round(($payment->total_amount / $monthlyFee) * 100, 2) : 0;
            $paymentDates = $payment->payment_dates ? explode(',', $payment->payment_dates) : [];
            
            return [
                'month' => $payment->month,
                'year' => $payment->year,
                'amount_paid' => $payment->total_amount,
                'payment_count' => $payment->payment_count,
                'percentage' => $periodPercentage,
                'payment_dates' => $paymentDates,
                'monthly_fee' => $monthlyFee,
                'remaining_amount' => max(0, $monthlyFee - $payment->total_amount)
            ];
        });

        // Calculate overall summary (for display purposes only)
        $totalPaid = $payments->sum('total_amount');
        $totalMonths = $payments->count();
        $averagePercentage = $totalMonths > 0 ? $payments->avg('total_amount') / $monthlyFee * 100 : 0;

        return [
            'total_paid' => $totalPaid,
            'total_months_paid' => $totalMonths,
            'average_percentage' => round($averagePercentage, 2),
            'periods' => $periods
        ];
    }
}
