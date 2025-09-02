<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Eschool;
use App\Models\KasRecord;
use App\Models\AttendanceRecord;
use App\Models\Member;

class AnalyticsController extends Controller
{
    public function getEschoolAnalytics(Request $request)
    {
        try {
            $user = Auth::user();
            
            // Filter berdasarkan role
            $query = Eschool::with(['coordinator', 'treasurer']);
            
            if ($user->role === 'staff') {
                $query->where('school_id', $user->school_id);
            } elseif ($user->role === 'koordinator') {
                $query->where('coordinator_id', $user->id);
            } elseif ($user->role === 'bendahara') {
                $query->where('treasurer_id', $user->id);
            } else {
                // Untuk siswa, hanya tampilkan eschool yang diikuti
                $query->whereHas('members', function($q) use ($user) {
                    $q->where('user_id', $user->id);
                });
            }
            
            $eschools = $query->get();
            
            $totalEschools = $eschools->count();
            $activeEschools = $eschools->where('is_active', true)->count();
            $inactiveEschools = $totalEschools - $activeEschools;
            $activePercentage = $totalEschools > 0 ? round(($activeEschools / $totalEschools) * 100, 2) : 0;
            
            // Distribusi monthly kas amount
            $monthlyKasDistribution = $eschools->groupBy('monthly_kas_amount')->map(function($group) {
                return [
                    'amount' => $group->first()->monthly_kas_amount,
                    'count' => $group->count()
                ];
            })->values();
            
            return response()->json([
                'totalEschools' => $totalEschools,
                'activeEschools' => $activeEschools,
                'inactiveEschools' => $inactiveEschools,
                'activePercentage' => $activePercentage,
                'monthlyKasDistribution' => $monthlyKasDistribution
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to retrieve eschool analytics',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    public function getFinancialAnalytics(Request $request)
    {
        try {
            $user = Auth::user();
            
            // Batasi akses ke financial analytics hanya untuk bendahara, koordinator, dan siswa
            // Staff tidak boleh mengakses data keuangan
            if ($user->role === 'staff') {
                return response()->json([
                    'message' => 'Unauthorized. Staff cannot access financial data.'
                ], 403);
            }
            
            // Filter berdasarkan role
            $eschoolQuery = Eschool::query();
            
            if ($user->role === 'koordinator') {
                $eschoolQuery->where('coordinator_id', $user->id);
            } elseif ($user->role === 'bendahara') {
                $eschoolQuery->where('treasurer_id', $user->id);
            } else {
                // Untuk siswa, hanya tampilkan eschool yang diikuti
                $eschoolQuery->whereHas('members', function($q) use ($user) {
                    $q->where('user_id', $user->id);
                });
            }
            
            $eschoolIds = $eschoolQuery->pluck('id');
            
            // Total income dan expense
            $totalIncome = KasRecord::whereIn('eschool_id', $eschoolIds)
                ->where('type', 'income')
                ->sum('amount');
                
            $totalExpense = KasRecord::whereIn('eschool_id', $eschoolIds)
                ->where('type', 'expense')
                ->sum('amount');
                
            $netBalance = $totalIncome - $totalExpense;
            
            // Monthly trends (12 bulan terakhir)
            $monthlyTrends = [];
            for ($i = 11; $i >= 0; $i--) {
                $date = now()->subMonths($i);
                $month = $date->format('M');
                $year = $date->year;
                $monthNum = $date->month;
                
                $income = KasRecord::whereIn('eschool_id', $eschoolIds)
                    ->where('type', 'income')
                    ->whereMonth('date', $monthNum)
                    ->whereYear('date', $year)
                    ->sum('amount');
                    
                $expense = KasRecord::whereIn('eschool_id', $eschoolIds)
                    ->where('type', 'expense')
                    ->whereMonth('date', $monthNum)
                    ->whereYear('date', $year)
                    ->sum('amount');
                
                $monthlyTrends[] = [
                    'month' => $month,
                    'year' => $year,
                    'income' => (int)$income,
                    'expense' => (int)$expense,
                    'balance' => (int)($income - $expense)
                ];
            }
            
            // Expense categories
            $expenseCategories = KasRecord::whereIn('eschool_id', $eschoolIds)
                ->where('type', 'expense')
                ->whereNotNull('category')
                ->select('category', DB::raw('SUM(amount) as total'))
                ->groupBy('category')
                ->get()
                ->map(function($item) {
                    return [
                        'category' => $item->category,
                        'amount' => (int)$item->total
                    ];
                });
            
            // Top member contributions (ambil 10 terbesar)
            $memberContributions = DB::table('kas_payments')
                ->join('kas_records', 'kas_payments.kas_record_id', '=', 'kas_records.id')
                ->join('members', 'kas_payments.member_id', '=', 'members.id')
                ->whereIn('kas_records.eschool_id', $eschoolIds)
                ->where('kas_records.type', 'income')
                ->select('members.name as member_name', DB::raw('SUM(kas_payments.amount) as contribution'))
                ->groupBy('members.name')
                ->orderBy('contribution', 'desc')
                ->limit(10)
                ->get()
                ->map(function($item) {
                    return [
                        'member' => $item->member_name,
                        'contribution' => (int)$item->contribution
                    ];
                });
            
            return response()->json([
                'totalIncome' => (int)$totalIncome,
                'totalExpense' => (int)$totalExpense,
                'netBalance' => (int)$netBalance,
                'monthlyTrends' => $monthlyTrends,
                'expenseCategories' => $expenseCategories,
                'memberContributions' => $memberContributions
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to retrieve financial analytics',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    public function getAttendanceAnalytics(Request $request)
    {
        try {
            $user = Auth::user();
            
            // Filter berdasarkan role
            $eschoolQuery = Eschool::query();
            
            if ($user->role === 'staff') {
                $eschoolQuery->where('school_id', $user->school_id);
            } elseif ($user->role === 'koordinator') {
                $eschoolQuery->where('coordinator_id', $user->id);
            } elseif ($user->role === 'bendahara') {
                $eschoolQuery->where('treasurer_id', $user->id);
            } else {
                // Untuk siswa, hanya tampilkan eschool yang diikuti
                $eschoolQuery->whereHas('members', function($q) use ($user) {
                    $q->where('user_id', $user->id);
                });
            }
            
            $eschoolIds = $eschoolQuery->pluck('id');
            
            // Average attendance rate per eschool
            $attendanceRates = [];
            foreach ($eschoolQuery->with('members')->get() as $eschool) {
                $totalRecords = AttendanceRecord::where('eschool_id', $eschool->id)->count();
                $presentRecords = AttendanceRecord::where('eschool_id', $eschool->id)->where('is_present', true)->count();
                
                $rate = $totalRecords > 0 ? round(($presentRecords / $totalRecords) * 100, 2) : 0;
                
                $attendanceRates[] = [
                    'eschool' => $eschool->name,
                    'rate' => $rate,
                    'total_records' => $totalRecords,
                    'present_records' => $presentRecords
                ];
            }
            
            // Attendance trends (12 bulan terakhir)
            $attendanceTrends = [];
            for ($i = 11; $i >= 0; $i--) {
                $date = now()->subMonths($i);
                $month = $date->format('M Y');
                $startDate = $date->startOfMonth();
                $endDate = $date->endOfMonth();
                
                $totalRecords = AttendanceRecord::whereIn('eschool_id', $eschoolIds)
                    ->whereBetween('date', [$startDate, $endDate])
                    ->count();
                    
                $presentRecords = AttendanceRecord::whereIn('eschool_id', $eschoolIds)
                    ->whereBetween('date', [$startDate, $endDate])
                    ->where('is_present', true)
                    ->count();
                
                $rate = $totalRecords > 0 ? round(($presentRecords / $totalRecords) * 100, 2) : 0;
                
                $attendanceTrends[] = [
                    'date' => $month,
                    'rate' => $rate,
                    'total_records' => $totalRecords,
                    'present_records' => $presentRecords
                ];
            }
            
            // Member attendance patterns (top 10 members with highest attendance)
            $memberPatterns = DB::table('attendance_records')
                ->join('members', 'attendance_records.member_id', '=', 'members.id')
                ->whereIn('attendance_records.eschool_id', $eschoolIds)
                ->select('members.name as member_name', 
                         DB::raw('COUNT(*) as total_sessions'),
                         DB::raw('SUM(CASE WHEN attendance_records.is_present THEN 1 ELSE 0 END) as present_count'),
                         DB::raw('ROUND((SUM(CASE WHEN attendance_records.is_present THEN 1 ELSE 0 END) / COUNT(*)) * 100, 2) as attendance_rate'))
                ->groupBy('members.name')
                ->orderBy('attendance_rate', 'desc')
                ->limit(10)
                ->get()
                ->map(function($item) {
                    return [
                        'member' => $item->member_name,
                        'attendance_rate' => (float)$item->attendance_rate,
                        'total_sessions' => (int)$item->total_sessions,
                        'present_count' => (int)$item->present_count
                    ];
                });
            
            $averageAttendanceRate = count($attendanceRates) > 0 ? 
                round(array_sum(array_column($attendanceRates, 'rate')) / count($attendanceRates), 2) : 0;
            
            return response()->json([
                'averageAttendanceRate' => $averageAttendanceRate,
                'attendanceRates' => $attendanceRates,
                'trends' => $attendanceTrends,
                'memberPatterns' => $memberPatterns
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to retrieve attendance analytics',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}