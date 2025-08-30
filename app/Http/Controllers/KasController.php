<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Eschool;
use App\Models\KasRecord;
use App\Models\KasPayment;
use App\Models\Member;

class KasController extends Controller
{
    /**
     * Get members for current treasurer's eschool
     */
   

    public function storeIncome(Request $request)
    {
        try {
            // Validasi input
            $validated = $request->validate([
                'description' => 'required|string|max:255',
                'date' => 'required|date',
                'payments' => 'required|array|min:1',
                'payments.*.member_id' => 'required|integer|exists:members,id',
                'payments.*.amount' => 'required|numeric|min:1',
                'payments.*.month' => 'required|integer|between:1,12',
                'payments.*.year' => 'required|integer|min:2020',
            ]);

            $userId = Auth::id();
                // Tentukan eschool_id berdasarkan role
            $user = auth()->user();
            if ($user->isKoordinator()) {
                $eschool = Eschool::where('coordinator_id', $userId)->first();
            } elseif ($user->isBendahara()) {
                $eschool = Eschool::where('treasurer_id', $userId)->first();
            }
            
             
            
            if (!$eschool) {
                return response()->json(['message' => 'Eschool tidak ditemukan'], 404);
            }

            DB::beginTransaction();

            // Buat kas record untuk income
            $kasRecord = KasRecord::create([
                'eschool_id' => $eschool->id,
                'type' => 'income',
                'amount' => collect($validated['payments'])->sum('amount'),
                'description' => $validated['description'],
                'date' => $validated['date'],
                'recorder_id' => $userId,
            ]);

            // Buat payment records untuk setiap member
            foreach ($validated['payments'] as $payment) {
                KasPayment::create([
                    'kas_record_id' => $kasRecord->id,
                    'member_id' => $payment['member_id'],
                    'amount' => $payment['amount'],
                    'month' => $payment['month'],
                    'year' => $payment['year'],
                    'is_paid' => true, // Set as paid since it's an income record
                    'paid_date' => $validated['date'],
                ]);
            }

            DB::commit();

            return response()->json([
                'data' => [
                    'kas_record_id' => $kasRecord->id,
                    'total_amount' => $kasRecord->amount,
                    'payments_count' => count($validated['payments']),
                ],
                'message' => 'Income added successfully'
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error occurred',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function storeExpense(Request $request)
    {
        try {
            // Validasi input untuk pengeluaran
            $validated = $request->validate([
                'amount' => 'required|numeric|min:1',
                'description' => 'required|string|max:255',
                'date' => 'required|date',
            ]);

            $userId = Auth::id();
                $user = auth()->user();
            if ($user->isKoordinator()) {
                $eschool = Eschool::where('coordinator_id', $userId)->first();
            } elseif ($user->isBendahara()) {
                $eschool = Eschool::where('treasurer_id', $userId)->first();
            }
            
            if (!$eschool) {
                return response()->json(['message' => 'Eschool tidak ditemukan'], 404);
            }

            // Buat kas record untuk expense
            $kasRecord = KasRecord::create([
                'eschool_id' => $eschool->id,
                'type' => 'expense',
                'amount' => $validated['amount'],
                'description' => $validated['description'],
                'date' => $validated['date'],
                'recorder_id' => $userId,
            ]);

            return response()->json([
                'data' => [
                    'kas_record_id' => $kasRecord->id,
                    'amount' => $kasRecord->amount,
                ],
                'message' => 'Expense added successfully'
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error occurred',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get kas records history
     */
    public function getKasRecords(Request $request)
    {
        try {
            $userId = Auth::id();
            $user = auth()->user();
            if ($user->isKoordinator()) {
                $eschool = Eschool::where('coordinator_id', $userId)->first();
            } elseif ($user->isBendahara()) {
                $eschool = Eschool::where('treasurer_id', $userId)->first();
            }
            
            if (!$eschool) {
                return response()->json(['message' => 'Eschool tidak ditemukan'], 404);
            }

            $perPage = $request->get('per_page', 20);
            $page = $request->get('page', 1);

            $query = KasRecord::with(['payments.member.user', 'recorder'])
                ->where('eschool_id', $eschool->id)
                ->orderBy('date', 'desc')
                ->orderBy('created_at', 'desc');

            // Filter by type if provided
            if ($request->has('type') && in_array($request->type, ['income', 'expense'])) {
                $query->where('type', $request->type);
            }

            // Filter by date range if provided
            if ($request->has('date_from')) {
                $query->where('date', '>=', $request->date_from);
            }
            if ($request->has('date_to')) {
                $query->where('date', '<=', $request->date_to);
            }

            $records = $query->paginate($perPage, ['*'], 'page', $page);

            $formattedRecords = $records->getCollection()->map(function ($record) {
                $data = [
                    'id' => $record->id,
                    'type' => $record->type,
                    'amount' => $record->amount,
                    'description' => $record->description,
                    'date' => $record->date,
                    'created_at' => $record->created_at->format('Y-m-d H:i:s'),
                    'created_by' => $record->recorder ? $record->recorder->name : 'N/A',
                ];

                // Add payment details for income records
                if ($record->type === 'income' && $record->payments) {
                    $data['payments'] = $record->payments->map(function ($payment) {
                        return [
                            'member_name' => $payment->member && $payment->member->user 
                                ? $payment->member->user->name 
                                : 'N/A',
                            'amount' => $payment->amount,
                            'month' => $payment->month,
                            'year' => $payment->year,
                        ];
                    });
                }

                return $data;
            });

            return response()->json([
                'data' => $formattedRecords,
                'pagination' => [
                    'current_page' => $records->currentPage(),
                    'last_page' => $records->lastPage(),
                    'per_page' => $records->perPage(),
                    'total' => $records->total(),
                    'from' => $records->firstItem(),
                    'to' => $records->lastItem(),
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error occurred',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get kas summary for dashboard
     */
    public function getSummary()
    {
        try {
            $userId = Auth::id();
                $user = auth()->user();
            if ($user->isKoordinator()) {
                $eschool = Eschool::where('coordinator_id', $userId)->first();
            } elseif ($user->isBendahara()) {
                $eschool = Eschool::where('treasurer_id', $userId)->first();
            }
            
            if (!$eschool) {
                return response()->json(['message' => 'Eschool tidak ditemukan'], 404);
            }

            // Calculate total income and expense
            $totalIncome = KasRecord::where('eschool_id', $eschool->id)
                ->where('type', 'income')
                ->sum('amount');

            $totalExpense = KasRecord::where('eschool_id', $eschool->id)
                ->where('type', 'expense')
                ->sum('amount');

            $balance = $totalIncome - $totalExpense;

            // Count total active members using many-to-many relationship
            $totalMembers = $eschool->members()->where('is_active', true)->count();

            // Current month payment statistics
            $currentMonth = date('n');
            $currentYear = date('Y');

            $paidThisMonth = KasPayment::whereHas('kasRecord', function ($query) use ($eschool) {
                    $query->where('eschool_id', $eschool->id);
                })
                ->where('month', $currentMonth)
                ->where('year', $currentYear)
                ->distinct('member_id')
                ->count('member_id');

            $unpaidThisMonth = $totalMembers - $paidThisMonth;
            $paymentPercentage = $totalMembers > 0 ? ($paidThisMonth / $totalMembers) * 100 : 0;

            return response()->json([
                'eschool' => [
                    'name' => $eschool->name,
                    'monthly_kas_amount' => $eschool->monthly_kas_amount,
                ],
                'summary' => [
                    'total_income' => $totalIncome,
                    'total_expense' => $totalExpense,
                    'balance' => $balance,
                    'total_members' => $totalMembers,
                ],
                'current_month' => [
                    'month' => $currentMonth,
                    'year' => $currentYear,
                    'paid_count' => $paidThisMonth,
                    'unpaid_count' => $unpaidThisMonth,
                    'payment_percentage' => round($paymentPercentage, 2),
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error occurred',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    public function checkPayment(Request $request)
{
    $request->validate([
        'member_id' => 'required|integer|exists:members,id',
        'month' => 'required|integer|between:1,12',
        'year' => 'required|integer|min:2020|max:2100',
    ]);

    $userId = Auth::id();
    $user = auth()->user();

    // Tentukan eschool_id berdasarkan role
    if ($user->isKoordinator()) {
        $eschool = Eschool::where('coordinator_id', $userId)->first();
    } elseif ($user->isBendahara()) {
        $eschool = Eschool::where('treasurer_id', $userId)->first();
    } else {
        return response()->json(['message' => 'Unauthorized. Only coordinator or treasurer can access this.'], 403);
    }

    if (!$eschool) {
        return response()->json(['message' => 'Eschool tidak ditemukan untuk user ini'], 404);
    }

    // Cek jika member terkait eschool menggunakan relasi many-to-many
    $member = $eschool->members()->where('members.id', $request->member_id)->first();

    if (!$member) {
        return response()->json(['exists' => false, 'message' => 'Member tidak terdaftar di eschool ini'], 422);
    }

    // Cek jika pembayaran sudah ada
    $exists = KasPayment::where('member_id', $request->member_id)
        ->where('month', $request->month)
        ->where('year', $request->year)
        ->where('is_paid', true)
        ->exists();

    return response()->json(['exists' => $exists]);
}
}