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
use Illuminate\Http\Request;

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
                // Verify member belongs to the same eschool
                $memberRole = UserEschoolRole::where('user_id', $payment['member_id'])
                    ->where('eschool_id', $validatedData['eschool_id'])
                    ->first();
                
                if ($memberRole) {
                    // Check if member already paid for the same month and year
                    $existingPayments = KasPayment::where('member_id', $memberRole->id)
                        ->where('month', $payment['month'])
                        ->where('year', $payment['year'])
                        ->get();

                    if ($existingPayments->isNotEmpty()) {
                        // Calculate total amount already paid for this month/year
                        $totalPaid = $existingPayments->sum('amount');
                        
                        // Get eschool to check monthly fee amount
                        $eschool = \App\Models\Eschool::find($validatedData['eschool_id']);
                        $monthlyFee = $eschool ? $eschool->monthly_fee_amount : 0;
                        
                        // If total paid is already equal to or more than monthly fee, it's a duplicate
                        // Only prevent payment if the member has fully paid for the month
                        if ($totalPaid >= $monthlyFee && $monthlyFee > 0) {
                            // Get member name for error message
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

            // Validate if any member payment exceeds the remaining amount they owe
            $excessivePayments = [];
            $eschool = \App\Models\Eschool::find($validatedData['eschool_id']);
            $monthlyFee = $eschool ? $eschool->monthly_fee_amount : 0;
            
            if ($monthlyFee > 0) {
                foreach ($validatedData['payments'] as $payment) {
                    // Check if member already paid for the same month and year
                    $memberRole = UserEschoolRole::where('user_id', $payment['member_id'])
                        ->where('eschool_id', $validatedData['eschool_id'])
                        ->first();
                    
                    if ($memberRole) {
                        // Get existing payments for this member for the same month/year
                        $existingPayments = KasPayment::where('member_id', $memberRole->id)
                            ->where('month', $payment['month'])
                            ->where('year', $payment['year'])
                            ->get();

                        if ($existingPayments->isNotEmpty()) {
                            // Calculate total amount already paid for this month/year
                            $totalPaid = $existingPayments->sum('amount');
                            
                            // Calculate remaining amount they owe
                            $remainingAmount = $monthlyFee - $totalPaid;
                            
                            // If they're trying to pay more than what they owe, it's an error
                            if ($payment['amount'] > $remainingAmount && $remainingAmount > 0) {
                                // Get member name for error message
                                $memberName = $memberRole && $memberRole->user && $memberRole->user->profile 
                                    ? $memberRole->user->profile->name 
                                    : 'Unknown Member';
                                    
                                $excessivePayments[] = [
                                    'member_id' => $payment['member_id'],
                                    'member_name' => $memberName,
                                    'amount_paid' => $payment['amount'],
                                    'remaining_amount' => $remainingAmount,
                                    'total_paid' => $totalPaid,
                                    'monthly_fee' => $monthlyFee
                                ];
                            }
                            
                            // If they've already fully paid, any additional payment is an error
                            if ($remainingAmount <= 0) {
                                // Get member name for error message
                                $memberName = $memberRole && $memberRole->user && $memberRole->user->profile 
                                    ? $memberRole->user->profile->name 
                                    : 'Unknown Member';
                                    
                                $excessivePayments[] = [
                                    'member_id' => $payment['member_id'],
                                    'member_name' => $memberName,
                                    'amount_paid' => $payment['amount'],
                                    'remaining_amount' => 0,
                                    'total_paid' => $totalPaid,
                                    'monthly_fee' => $monthlyFee
                                ];
                            }
                        } else {
                            // No existing payments, check against full monthly fee
                            if ($payment['amount'] > $monthlyFee) {
                                // Get member name for error message
                                $memberName = $memberRole && $memberRole->user && $memberRole->user->profile 
                                    ? $memberRole->user->profile->name 
                                    : 'Unknown Member';
                                    
                                $excessivePayments[] = [
                                    'member_id' => $payment['member_id'],
                                    'member_name' => $memberName,
                                    'amount_paid' => $payment['amount'],
                                    'remaining_amount' => $monthlyFee,
                                    'total_paid' => 0,
                                    'monthly_fee' => $monthlyFee
                                ];
                            }
                        }
                    }
                }
            }

            // If there are excessive payments, return error
            if (!empty($excessivePayments)) {
                $errorMessages = [];
                foreach ($excessivePayments as $excess) {
                    if ($excess['remaining_amount'] <= 0) {
                        $errorMessages[] = "Pembayaran dari {$excess['member_name']} sebesar Rp " . number_format($excess['amount_paid'], 0, ',', '.') . " tidak dapat diproses karena kas untuk periode {$payment['month']}/{$payment['year']} sudah lunas.";
                    } else {
                        $errorMessages[] = "Pembayaran dari {$excess['member_name']} sebesar Rp " . number_format($excess['amount_paid'], 0, ',', '.') . " melebihi sisa kas yang belum dibayar sebesar Rp " . number_format($excess['remaining_amount'], 0, ',', '.') . ". Mohon periksa kembali jumlah pembayaran.";
                    }
                }

                return response()->json([
                    'success' => false,
                    'message' => implode(' ', $errorMessages),
                    'errors' => [
                        'excessive_payments' => $excessivePayments
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
                    $member = UserEschoolRole::where('user_id', $payment['member_id'])
                        ->where('eschool_id', $validatedData['eschool_id'])
                        ->first();
                    \Log::info('INI NYOBA GITA',['member' => $member]);
                    if (!$member || !in_array($member->role, ['member', 'treasurer'])) {
                        DB::rollBack();
                        return response()->json([
                            'success' => false,
                            'message' => 'Invalid member for this eschool.'
                        ], 400);
                    }

                    $kasPayment = KasPayment::create([
                        'kas_record_id' => $kasRecord->id,
                        'member_id' => $member->id, // Use the UserEschoolRole id, not the user_id
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
     * @param \Illuminate\Http\Request $request
     * @return JsonResponse
     */
    public function index($eschoolId, Request $request): JsonResponse
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

            // Get filter parameters
            $type = $request->get('type');
            $search = $request->get('search');
            $month = $request->get('month');
            $year = $request->get('year');
            $dateFrom = $request->get('date_from');
            $dateTo = $request->get('date_to');
            $page = $request->get('page', 1);
            $perPage = $request->get('per_page', 10);

            // Build query for kas records
            $query = KasRecord::where('eschool_id', $eschoolId)
                ->with(['kasPayments.member.user.profile', 'recorder.user.profile']);

            // Apply type filter
            if ($type === 'income') {
                $query->where('amount', '>', 0);
            } elseif ($type === 'expense') {
                $query->where('amount', '<', 0);
            }

            // Apply search filter
            if ($search) {
                $query->where(function($q) use ($search) {
                    $q->where('description', 'LIKE', "%{$search}%")
                      ->orWhere('category', 'LIKE', "%{$search}%");
                });
            }

            // Apply month filter
            if ($month) {
                $query->whereMonth('date', $month);
            }

            // Apply year filter
            if ($year) {
                $query->whereYear('date', $year);
            }

            // Apply date range filter
            if ($dateFrom) {
                $query->where('date', '>=', $dateFrom);
            }

            if ($dateTo) {
                $query->where('date', '<=', $dateTo);
            }

            // Get records with pagination
            $kasRecords = $query->orderBy('date', 'desc')->paginate($perPage, ['*'], 'page', $page);

            // Calculate summary
            $allRecords = KasRecord::where('eschool_id', $eschoolId)->get();
            $totalIncome = $allRecords->where('amount', '>', 0)->sum('amount');
            $totalExpense = $allRecords->where('amount', '<', 0)->sum('amount');
            $balance = $totalIncome + $totalExpense;

            return response()->json([
                'success' => true,
                'message' => 'Financial records retrieved successfully.',
                'data' => [
                    'kas_records' => $kasRecords->items(),
                    'summary' => [
                        'total_income' => $totalIncome,
                        'total_expense' => abs($totalExpense),
                        'balance' => $balance
                    ]
                ],
                'pagination' => [
                    'current_page' => $kasRecords->currentPage(),
                    'last_page' => $kasRecords->lastPage(),
                    'per_page' => $kasRecords->perPage(),
                    'total' => $kasRecords->total(),
                    'from' => $kasRecords->firstItem(),
                    'to' => $kasRecords->lastItem()
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

    /**
     * Export kas records to CSV
     *
     * @param int $eschoolId
     * @param \Illuminate\Http\Request $request
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function export($eschoolId, Request $request)
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
                    'message' => 'You do not have permission to export financial records for this eschool.'
                ], 403);
            }

            // Get filter parameters
            $type = $request->get('type');
            $dateFrom = $request->get('date_from');
            $dateTo = $request->get('date_to');

            // Build query for kas records
            $query = KasRecord::where('eschool_id', $eschoolId)
                ->with(['kasPayments.member.user.profile', 'recorder.user.profile']);

            // Apply type filter
            if ($type === 'income') {
                $query->where('amount', '>', 0);
            } elseif ($type === 'expense') {
                $query->where('amount', '<', 0);
            }

            // Apply date range filter
            if ($dateFrom) {
                $query->where('date', '>=', $dateFrom);
            }

            if ($dateTo) {
                $query->where('date', '<=', $dateTo);
            }

            // Get records
            $kasRecords = $query->orderBy('date', 'desc')->get();

            // Create CSV header
            $csvContent = "ID,Tanggal Transaksi,Tipe,Deskripsi,Jumlah Total,Kategori,Dicatat Oleh,Tanggal Dibuat,Tanggal Diupdate,Nama Anggota,Jumlah Pembayaran,Periode Bulan,Periode Tahun\n";
            
            // Process each record
            foreach ($kasRecords as $record) {
                $type = $record->amount > 0 ? 'income' : 'expense';
                $amount = abs($record->amount);
                $recordedBy = $record->recorder && $record->recorder->user && $record->recorder->user->profile 
                    ? $record->recorder->user->profile->name 
                    : 'Unknown';
                
                // Format dates for better readability
                $transactionDate = date('d/m/Y', strtotime($record->date));
                $createdAt = date('d/m/Y H:i', strtotime($record->created_at));
                $updatedAt = date('d/m/Y H:i', strtotime($record->updated_at));
                
                // For income records with payments, create a row for each payment
                if ($type === 'income' && $record->kasPayments->count() > 0) {
                    foreach ($record->kasPayments as $payment) {
                        $memberName = $payment->member && $payment->member->user && $payment->member->user->profile 
                            ? $payment->member->user->profile->name 
                            : 'Unknown Member';
                        
                        $csvContent .= sprintf(
                            "%d,%s,%s,\"%s\",%s,%s,%s,%s,%s,%s,%s,%s,%s\n",
                            $record->id,
                            $transactionDate,
                            $type,
                            str_replace('"', '""', $record->description),
                            number_format($amount, 2, '.', ''),
                            $record->category ?? '',
                            $recordedBy,
                            $createdAt,
                            $updatedAt,
                            $memberName,
                            number_format($payment->amount, 2, '.', ''),
                            $payment->month,
                            $payment->year
                        );
                    }
                } 
                // For expense records or income records without payments, create a single row
                else {
                    $memberName = '';
                    $paymentAmount = '';
                    $paymentMonth = '';
                    $paymentYear = '';
                    
                    $csvContent .= sprintf(
                        "%d,%s,%s,\"%s\",%s,%s,%s,%s,%s,%s,%s,%s,%s\n",
                        $record->id,
                        $transactionDate,
                        $type,
                        str_replace('"', '""', $record->description),
                        number_format($amount, 2, '.', ''),
                        $record->category ?? '',
                        $recordedBy,
                        $createdAt,
                        $updatedAt,
                        $memberName,
                        $paymentAmount,
                        $paymentMonth,
                        $paymentYear
                    );
                }
            }

            // Create file
            $filename = 'kas_records_' . $eschool->name . '_' . date('Y-m-d_H-i-s') . '.csv';
            $filePath = storage_path('app/' . $filename);
            
            // Write CSV content to file without BOM
            file_put_contents($filePath, $csvContent);

            // Return file as download
            return response()->download($filePath)->deleteFileAfterSend(true);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to export financial records.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}