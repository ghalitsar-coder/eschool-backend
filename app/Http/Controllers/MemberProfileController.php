<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Eschool;
use App\Models\KasRecord;
use App\Models\AttendanceRecord;
use App\Models\Member;

class MemberProfileController extends Controller
{
    public function getMemberProfileData(Request $request)
    {
        try {
            $user = Auth::user();
            
            // Handle berbagai role yang bisa mengakses profile data
            if ($user->role === 'siswa') {
                return $this->getSiswaProfileData($user);
            } elseif ($user->role === 'koordinator') {
                return $this->getKoordinatorProfileData($user);
            } elseif ($user->role === 'staff') {
                return $this->getStaffProfileData($user);
            } elseif ($user->role === 'bendahara') {
                return $this->getBendaharaProfileData($user);
            } else {
                return response()->json([
                    'message' => 'Unauthorized. Role not supported.'
                ], 403);
            }
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to retrieve profile data',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    private function getSiswaProfileData($user)
    {
        // Original logic untuk member (siswa)
        $member = $user->member;
        if (!$member) {
            return response()->json([
                'message' => 'Member record not found'
            ], 404);
        }
        
        // Dapatkan semua eschool yang diikuti member ini
        $eschools = $member->eschools()->with(['coordinator', 'treasurer'])->get();
        
        if ($eschools->isEmpty()) {
            return response()->json([
                'message' => 'You are not enrolled in any eschools',
                'eschools' => [],
                'statistics' => []
            ]);
        }
        
        return $this->getMemberStats($member, $eschools);
    }
    
    private function getKoordinatorProfileData($user)
    {
        // Koordinator melihat data eschool yang mereka koordinatori
        $eschool = $user->coordinatedEschool;
        
        // Alternative: cari via member relationship jika direct relationship tidak ada
        if (!$eschool && $user->member) {
            $eschool = Eschool::where('coordinator_id', $user->member->id)->first();
        }
        
        if (!$eschool) {
            // Debug information
            $allEschools = Eschool::where('coordinator_id', $user->id)->get();
            $memberEschools = $user->member ? Eschool::where('coordinator_id', $user->member->id)->get() : collect();
            
            return response()->json([
                'message' => 'No eschool assigned to coordinate',
                'debug' => [
                    'user_id' => $user->id,
                    'user_role' => $user->role,
                    'has_member' => $user->member ? true : false,
                    'member_id' => $user->member ? $user->member->id : null,
                    'coordinator_id_in_eschools' => $allEschools->pluck('coordinator_id'),
                    'all_eschools_count' => $allEschools->count(),
                    'member_coordinator_eschools' => $memberEschools->pluck('id')
                ]
            ], 404);
        }
        
        // Statistik eschool yang dikoordinatori
        $members = $eschool->members()->get();
        $totalMembers = $members->count();
        
        // Statistik kehadiran
        $totalAttendanceRecords = AttendanceRecord::where('eschool_id', $eschool->id)->count();
        $presentRecords = AttendanceRecord::where('eschool_id', $eschool->id)
            ->where('is_present', true)->count();
        $attendanceRate = $totalAttendanceRecords > 0 ? 
            round(($presentRecords / $totalAttendanceRecords) * 100, 2) : 0;
        
        // Statistik kas
        $totalExpectedKas = 0;
        $totalPaidKas = 0;
        $kasRecords = KasRecord::where('eschool_id', $eschool->id)->where('type', 'income')->get();
        
        foreach ($kasRecords as $record) {
            $payments = $record->payments()->get();
            foreach ($payments as $payment) {
                $totalExpectedKas += $payment->amount;
                if ($payment->is_paid) {
                    $totalPaidKas += $payment->amount;
                }
            }
        }
        
        // Recent activities (attendance + kas)
        $recentAttendance = AttendanceRecord::with('member')
            ->where('eschool_id', $eschool->id)
            ->orderBy('date', 'desc')
            ->limit(5)
            ->get();
        
        $recentKasPayments = DB::table('kas_payments')
            ->join('kas_records', 'kas_payments.kas_record_id', '=', 'kas_records.id')
            ->join('members', 'kas_payments.member_id', '=', 'members.id')
            ->where('kas_records.eschool_id', $eschool->id)
            ->where('kas_payments.is_paid', true)
            ->orderBy('kas_payments.paid_date', 'desc')
            ->limit(5)
            ->select('members.name as member_name', 'kas_payments.amount', 'kas_payments.paid_date')
            ->get();
        
        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'profile_type' => 'coordinator'
            ],
            'eschool' => [
                'id' => $eschool->id,
                'name' => $eschool->name,
                'description' => $eschool->description,
                'monthly_kas_amount' => $eschool->monthly_kas_amount,
                'coordinator_name' => $eschool->coordinator->name ?? 'N/A',
                'treasurer_name' => $eschool->treasurer->name ?? 'N/A'
            ],
            'statistics' => [
                'total_members' => $totalMembers,
                'attendance_rate' => $attendanceRate,
                'total_sessions' => $totalAttendanceRecords,
                'present_sessions' => $presentRecords,
                'kas_collection_rate' => $totalExpectedKas > 0 ? round(($totalPaidKas / $totalExpectedKas) * 100, 2) : 0,
                'total_kas_expected' => $totalExpectedKas,
                'total_kas_collected' => $totalPaidKas,
                'outstanding_kas' => $totalExpectedKas - $totalPaidKas
            ],
            'recent_activities' => [
                'recent_attendance' => $recentAttendance,
                'recent_payments' => $recentKasPayments
            ]
        ]);
    }
    
    private function getStaffProfileData($user)
    {
        // Staff melihat overview semua eschool (atau sekolahnya)
        // Untuk saat ini, kita ambil semua eschool (bisa disesuaikan dengan logic bisnis)
        $eschools = Eschool::with(['coordinator', 'treasurer'])->get();
        
        $totalMembers = 0;
        $totalEschools = $eschools->count();
        $overallAttendanceRate = 0;
        $totalKasExpected = 0;
        $totalKasCollected = 0;
        
        $eschoolStats = [];
        
        foreach ($eschools as $eschool) {
            $members = $eschool->members()->count();
            $totalMembers += $members;
            
            // Attendance stats
            $attendanceRecords = AttendanceRecord::where('eschool_id', $eschool->id)->count();
            $presentRecords = AttendanceRecord::where('eschool_id', $eschool->id)
                ->where('is_present', true)->count();
            $attendanceRate = $attendanceRecords > 0 ? 
                round(($presentRecords / $attendanceRecords) * 100, 2) : 0;
            $overallAttendanceRate += $attendanceRate;
            
            // Kas stats
            $expectedKas = 0;
            $paidKas = 0;
            $kasRecords = KasRecord::where('eschool_id', $eschool->id)->where('type', 'income')->get();
            
            foreach ($kasRecords as $record) {
                $payments = $record->payments()->get();
                foreach ($payments as $payment) {
                    $expectedKas += $payment->amount;
                    if ($payment->is_paid) {
                        $paidKas += $payment->amount;
                    }
                }
            }
            
            $totalKasExpected += $expectedKas;
            $totalKasCollected += $paidKas;
            
            $eschoolStats[] = [
                'eschool_id' => $eschool->id,
                'eschool_name' => $eschool->name,
                'coordinator_name' => $eschool->coordinator->name ?? 'N/A',
                'treasurer_name' => $eschool->treasurer->name ?? 'N/A',
                'total_members' => $members,
                'attendance_rate' => $attendanceRate,
                'kas_collection_rate' => $expectedKas > 0 ? round(($paidKas / $expectedKas) * 100, 2) : 0,
                'total_kas_expected' => $expectedKas,
                'total_kas_collected' => $paidKas
            ];
        }
        
        $averageAttendanceRate = $totalEschools > 0 ? 
            round($overallAttendanceRate / $totalEschools, 2) : 0;
        
        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'profile_type' => 'staff_overview'
            ],
            'overview_statistics' => [
                'total_eschools' => $totalEschools,
                'total_members' => $totalMembers,
                'average_attendance_rate' => $averageAttendanceRate,
                'total_kas_expected' => $totalKasExpected,
                'total_kas_collected' => $totalKasCollected,
                'overall_collection_rate' => $totalKasExpected > 0 ? round(($totalKasCollected / $totalKasExpected) * 100, 2) : 0
            ],
            'eschool_breakdown' => $eschoolStats
        ]);
    }
    
    private function getBendaharaProfileData($user)
    {
        // Bendahara melihat data keuangan eschool yang mereka kelola
        $member = $user->member;
        if (!$member) {
            return response()->json([
                'message' => 'Member record not found'
            ], 404);
        }
        
        // Cari eschool dimana user ini adalah bendahara
        \Log::info("member".$member);
        $eschool = Eschool::where('treasurer_id', 6)->with(['coordinator', 'treasurer'])->first();
        \Log::info("ESCHOOL PROFILE ".$eschool);
        \Log::info("treasurer id ".$member->id);
        if (!$eschool) {
            return response()->json([
                'message' => 'No eschool assigned as treasurer'
            ], 404);
        }
        
        // Statistik keuangan detail
        $kasRecords = KasRecord::where('eschool_id', $eschool->id)->get();
        $totalIncome = 0;
        $totalExpense = 0;
        $totalExpectedKas = 0;
        $totalPaidKas = 0;
        $totalOutstanding = 0;
        
        $paymentStats = [];
        $monthlyTrend = [];
        
        foreach ($kasRecords as $record) {
            if ($record->type === 'income') {
                $totalIncome += $record->amount;
                $payments = $record->payments()->get();
                foreach ($payments as $payment) {
                    $totalExpectedKas += $payment->amount;
                    if ($payment->is_paid) {
                        $totalPaidKas += $payment->amount;
                    } else {
                        $totalOutstanding += $payment->amount;
                    }
                }
            } else {
                $totalExpense += $record->amount;
            }
        }
        
        $currentBalance = $totalPaidKas - $totalExpense;
        
        // Recent transactions
        $recentPayments = DB::table('kas_payments')
            ->join('kas_records', 'kas_payments.kas_record_id', '=', 'kas_records.id')
            ->join('members', 'kas_payments.member_id', '=', 'members.id')
            ->where('kas_records.eschool_id', $eschool->id)
            ->where('kas_payments.is_paid', true)
            ->orderBy('kas_payments.paid_date', 'desc')
            ->limit(10)
            ->select('members.name as member_name', 'kas_payments.amount', 'kas_payments.paid_date', 'kas_records.description')
            ->get();
        
        // Outstanding payments
        $outstandingPayments = DB::table('kas_payments')
            ->join('kas_records', 'kas_payments.kas_record_id', '=', 'kas_records.id')
            ->join('members', 'kas_payments.member_id', '=', 'members.id')
            ->where('kas_records.eschool_id', $eschool->id)
            ->where('kas_payments.is_paid', false)
            ->select('members.name as member_name', 'kas_payments.amount', 'kas_payments.month', 'kas_payments.year', 'kas_records.description')
            ->get();
        
        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'profile_type' => 'treasurer'
            ],
            'eschool' => [
                'id' => $eschool->id,
                'name' => $eschool->name,
                'description' => $eschool->description,
                'monthly_kas_amount' => $eschool->monthly_kas_amount,
                'coordinator_name' => $eschool->coordinator->name ?? 'N/A',
                'treasurer_name' => $eschool->treasurer->name ?? 'N/A'
            ],
            'financial_summary' => [
                'current_balance' => $currentBalance,
                'total_income' => $totalPaidKas,
                'total_expense' => $totalExpense,
                'total_expected' => $totalExpectedKas,
                'total_outstanding' => $totalOutstanding,
                'collection_rate' => $totalExpectedKas > 0 ? round(($totalPaidKas / $totalExpectedKas) * 100, 2) : 0
            ],
            'recent_payments' => $recentPayments,
            'outstanding_payments' => $outstandingPayments
        ]);
    }
    
    private function getMonthlyKasTrend($memberId)
    {
        // Ambil 6 bulan terakhir
        $trend = [];
        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $month = $date->month;
            $year = $date->year;
            $monthName = $date->format('M Y');
            
            // Hitung total kas yang diharapkan untuk bulan ini
            $expected = DB::table('kas_payments')
                ->join('kas_records', 'kas_payments.kas_record_id', '=', 'kas_records.id')
                ->where('kas_payments.member_id', $memberId)
                ->where('kas_payments.month', $month)
                ->where('kas_payments.year', $year)
                ->sum('kas_payments.amount');
            
            // Hitung total kas yang sudah dibayar untuk bulan ini
            $paid = DB::table('kas_payments')
                ->join('kas_records', 'kas_payments.kas_record_id', '=', 'kas_records.id')
                ->where('kas_payments.member_id', $memberId)
                ->where('kas_payments.month', $month)
                ->where('kas_payments.year', $year)
                ->where('kas_payments.is_paid', true)
                ->sum('kas_payments.amount');
            
            $trend[] = [
                'month' => $monthName,
                'expected' => (int)$expected,
                'paid' => (int)$paid,
                'outstanding' => (int)($expected - $paid)
            ];
        }
        
        return $trend;
    }
    
    private function getMemberStats($member, $eschools)
    {
        // Kumpulkan statistik untuk setiap eschool
        $statistics = [];
        $totalKasOwed = 0;
        $totalKasPaid = 0;
        $overallAttendanceRate = 0;
        
        // Data untuk chart
        $attendanceChartData = [];
        $kasPaymentChartData = [];
        
        foreach ($eschools as $eschool) {
            // Statistik kas untuk eschool ini
            $kasRecords = KasRecord::where('eschool_id', $eschool->id)
                ->where('type', 'income')
                ->get();
            
            $totalExpectedKas = 0;
            $totalPaidKas = 0;
            
            // Hitung total kas yang diharapkan dan yang sudah dibayar
            foreach ($kasRecords as $record) {
                $payments = $record->payments()->where('member_id', $member->id)->get();
                foreach ($payments as $payment) {
                    $totalExpectedKas += $payment->amount;
                    if ($payment->is_paid) {
                        $totalPaidKas += $payment->amount;
                    }
                }
            }
            
            $totalKasOwed += ($totalExpectedKas - $totalPaidKas);
            $totalKasPaid += $totalPaidKas;
            
            // Statistik kehadiran
            $totalAttendanceRecords = AttendanceRecord::where('eschool_id', $eschool->id)
                ->where('member_id', $member->id)
                ->count();
                
            $presentRecords = AttendanceRecord::where('eschool_id', $eschool->id)
                ->where('member_id', $member->id)
                ->where('is_present', true)
                ->count();
            
            $attendanceRate = $totalAttendanceRecords > 0 ? 
                round(($presentRecords / $totalAttendanceRecords) * 100, 2) : 0;
            
            $overallAttendanceRate += $attendanceRate;
            
            // Data untuk chart kehadiran
            $attendanceChartData[] = [
                'eschool' => $eschool->name,
                'present' => $presentRecords,
                'absent' => $totalAttendanceRecords - $presentRecords,
                'rate' => $attendanceRate
            ];
            
            // Data untuk chart pembayaran kas
            $kasPaymentChartData[] = [
                'eschool' => $eschool->name,
                'paid' => $totalPaidKas,
                'outstanding' => $totalExpectedKas - $totalPaidKas
            ];
            
            $statistics[] = [
                'eschool_id' => $eschool->id,
                'eschool_name' => $eschool->name,
                'coordinator_name' => $eschool->coordinator->name ?? 'N/A',
                'treasurer_name' => $eschool->treasurer->name ?? 'N/A',
                'monthly_kas_amount' => $eschool->monthly_kas_amount,
                'total_expected_kas' => $totalExpectedKas,
                'total_paid_kas' => $totalPaidKas,
                'total_outstanding_kas' => $totalExpectedKas - $totalPaidKas,
                'attendance_rate' => $attendanceRate,
                'total_sessions' => $totalAttendanceRecords,
                'present_sessions' => $presentRecords,
                'absent_sessions' => $totalAttendanceRecords - $presentRecords
            ];
        }
        
        $averageAttendanceRate = count($eschools) > 0 ? 
            round($overallAttendanceRate / count($eschools), 2) : 0;
        
        // Statistik keseluruhan
        $overallStats = [
            'total_eschools' => $eschools->count(),
            'total_kas_paid' => $totalKasPaid,
            'total_kas_outstanding' => $totalKasOwed,
            'average_attendance_rate' => $averageAttendanceRate,
            'total_sessions_attended' => array_sum(array_column($statistics, 'present_sessions')),
            'total_sessions_missed' => array_sum(array_column($statistics, 'absent_sessions'))
        ];
        
        // Data untuk chart tren bulanan kas (ambil 6 bulan terakhir)
        $monthlyKasTrend = $this->getMonthlyKasTrend($member->id);
        
        return response()->json([
            'user' => [
                'id' => auth()->user()->id,
                'name' => auth()->user()->name,
                'email' => auth()->user()->email,
                'role' => auth()->user()->role
            ],
            'member' => [
                'id' => $member->id,
                'name' => $member->name,
                'nip' => $member->nip,
                'date_of_birth' => $member->date_of_birth,
                'gender' => $member->gender,
                'address' => $member->address,
                'phone' => $member->phone
            ],
            'eschools' => $eschools,
            'statistics' => $statistics,
            'overall_stats' => $overallStats,
            'chart_data' => [
                'attendance' => $attendanceChartData,
                'kas_payment' => $kasPaymentChartData,
                'monthly_kas_trend' => $monthlyKasTrend
            ]
        ]);
    }
    
    private function getEschoolStats($user, $eschool)
    {
        // Untuk koordinator dan staff, tampilkan statistik eschool yang mereka kelola
        $members = $eschool->members()->get();
        
        // Statistik kas keseluruhan
        $totalExpectedKas = 0;
        $totalPaidKas = 0;
        $totalOutstandingKas = 0;
        
        $kasRecords = KasRecord::where('eschool_id', $eschool->id)
            ->where('type', 'income')
            ->get();
        
        foreach ($kasRecords as $record) {
            $payments = $record->payments()->get();
            foreach ($payments as $payment) {
                $totalExpectedKas += $payment->amount;
                if ($payment->is_paid) {
                    $totalPaidKas += $payment->amount;
                } else {
                    $totalOutstandingKas += $payment->amount;
                }
            }
        }
        
        // Statistik kehadiran keseluruhan
        $totalAttendanceRecords = AttendanceRecord::where('eschool_id', $eschool->id)->count();
        $presentRecords = AttendanceRecord::where('eschool_id', $eschool->id)
            ->where('is_present', true)->count();
        $attendanceRate = $totalAttendanceRecords > 0 ? 
            round(($presentRecords / $totalAttendanceRecords) * 100, 2) : 0;
        
        // Data chart untuk member performance
        $memberStats = [];
        $attendanceChartData = [];
        $kasPaymentChartData = [];
        
        foreach ($members as $member) {
            // Statistik member individual
            $memberAttendance = AttendanceRecord::where('eschool_id', $eschool->id)
                ->where('member_id', $member->id)->count();
            $memberPresent = AttendanceRecord::where('eschool_id', $eschool->id)
                ->where('member_id', $member->id)
                ->where('is_present', true)->count();
            $memberRate = $memberAttendance > 0 ? 
                round(($memberPresent / $memberAttendance) * 100, 2) : 0;
            
            // Kas member
            $memberExpected = 0;
            $memberPaid = 0;
            foreach ($kasRecords as $record) {
                $payments = $record->payments()->where('member_id', $member->id)->get();
                foreach ($payments as $payment) {
                    $memberExpected += $payment->amount;
                    if ($payment->is_paid) {
                        $memberPaid += $payment->amount;
                    }
                }
            }
            
            $memberStats[] = [
                'member_id' => $member->id,
                'member_name' => $member->name,
                'attendance_rate' => $memberRate,
                'total_sessions' => $memberAttendance,
                'present_sessions' => $memberPresent,
                'expected_kas' => $memberExpected,
                'paid_kas' => $memberPaid,
                'outstanding_kas' => $memberExpected - $memberPaid
            ];
            
            $attendanceChartData[] = [
                'member' => $member->name,
                'rate' => $memberRate
            ];
            
            $kasPaymentChartData[] = [
                'member' => $member->name,
                'paid' => $memberPaid,
                'outstanding' => $memberExpected - $memberPaid
            ];
        }
        
        $overallStats = [
            'total_members' => $members->count(),
            'total_kas_expected' => $totalExpectedKas,
            'total_kas_paid' => $totalPaidKas,
            'total_kas_outstanding' => $totalOutstandingKas,
            'attendance_rate' => $attendanceRate,
            'total_sessions' => $totalAttendanceRecords,
            'present_sessions' => $presentRecords,
            'absent_sessions' => $totalAttendanceRecords - $presentRecords
        ];
        
        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role
            ],
            'eschool' => [
                'id' => $eschool->id,
                'name' => $eschool->name,
                'description' => $eschool->description,
                'monthly_kas_amount' => $eschool->monthly_kas_amount,
                'coordinator_name' => $eschool->coordinator->name ?? 'N/A',
                'treasurer_name' => $eschool->treasurer->name ?? 'N/A'
            ],
            'members' => $memberStats,
            'overall_stats' => $overallStats,
            'chart_data' => [
                'member_attendance' => $attendanceChartData,
                'member_kas_payment' => $kasPaymentChartData
            ]
        ]);
    }
    
    public function getFilteredAttendanceData(Request $request)
    {
        try {
            $user = Auth::user();
            
            // Handle berbagai role
            if ($user->role === 'siswa') {
                return $this->getMemberAttendanceData($user, $request);
            } elseif ($user->role === 'koordinator') {
                return $this->getKoordinatorAttendanceData($user, $request);
            } elseif ($user->role === 'staff') {
                return $this->getStaffAttendanceData($user, $request);
            } elseif ($user->role === 'bendahara') {
                return $this->getBendaharaAttendanceData($user, $request);
            } else {
                return response()->json([
                    'message' => 'Unauthorized role'
                ], 403);
            }
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to retrieve attendance data',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    private function getMemberAttendanceData($user, $request)
    {
        // Cari member record untuk user ini
        $member = $user->member;
        if (!$member) {
            return response()->json([
                'message' => 'Member record not found'
            ], 404);
        }
        
        // Filter berdasarkan eschool_id jika disediakan
        $eschoolId = $request->query('eschool_id');
        
        // Query dasar
        $query = AttendanceRecord::with('eschool')
            ->where('member_id', $member->id);
        
        // Terapkan filter eschool jika disediakan
        if ($eschoolId) {
            $query->where('eschool_id', $eschoolId);
        }
        
        // Pagination
        $perPage = $request->query('per_page', 10);
        $attendanceRecords = $query->orderBy('date', 'desc')->paginate($perPage);
        
        return response()->json($attendanceRecords);
    }
    
    private function getKoordinatorAttendanceData($user, $request)
    {
        // Koordinator melihat data absensi dari eschool yang mereka koordinatori
        $eschool = $user->coordinatedEschool;
        if (!$eschool) {
            return response()->json([
                'message' => 'No eschool assigned to coordinate'
            ], 404);
        }
        
        // Query attendance records untuk eschool yang dikoordinatori
        $query = AttendanceRecord::with(['member', 'eschool'])
            ->where('eschool_id', $eschool->id);
        
        // Filter tambahan
        $memberId = $request->query('member_id');
        $date = $request->query('date');
        $isPresent = $request->query('is_present');
        
        if ($memberId) {
            $query->where('member_id', $memberId);
        }
        
        if ($date) {
            $query->whereDate('date', $date);
        }
        
        if ($isPresent !== null) {
            $query->where('is_present', $isPresent);
        }
        
        // Pagination
        $perPage = $request->query('per_page', 10);
        $attendanceRecords = $query->orderBy('date', 'desc')->paginate($perPage);
        
        return response()->json($attendanceRecords);
    }
    
    private function getStaffAttendanceData($user, $request)
    {
        // Staff melihat data absensi dari semua eschool di sekolahnya
        // Asumsi: staff terhubung ke sekolah melalui relasi atau field school_id
        // Untuk saat ini, kita ambil semua eschool yang ada (bisa disesuaikan dengan logic bisnis)
        
        $query = AttendanceRecord::with(['member', 'eschool']);
        
        // Filter
        $eschoolId = $request->query('eschool_id');
        $memberId = $request->query('member_id');
        $date = $request->query('date');
        $isPresent = $request->query('is_present');
        
        if ($eschoolId) {
            $query->where('eschool_id', $eschoolId);
        }
        
        if ($memberId) {
            $query->where('member_id', $memberId);
        }
        
        if ($date) {
            $query->whereDate('date', $date);
        }
        
        if ($isPresent !== null) {
            $query->where('is_present', $isPresent);
        }
        
        // Pagination
        $perPage = $request->query('per_page', 10);
        $attendanceRecords = $query->orderBy('date', 'desc')->paginate($perPage);
        
        return response()->json($attendanceRecords);
    }
    
    private function getBendaharaAttendanceData($user, $request)
    {
        // Bendahara melihat data absensi dari eschool yang mereka kelola kasnya
        $member = $user->member;
        if (!$member) {
            return response()->json([
                'message' => 'Member record not found'
            ], 404);
        }
        
        // Cari eschool dimana user ini adalah bendahara
        $eschool = Eschool::where('treasurer_id', $member->id)->first();
        if (!$eschool) {
            return response()->json([
                'message' => 'No eschool assigned as treasurer'
            ], 404);
        }
        
        // Query attendance records untuk eschool yang dibendaharai
        $query = AttendanceRecord::with(['member', 'eschool'])
            ->where('eschool_id', $eschool->id);
        
        // Filter tambahan
        $memberId = $request->query('member_id');
        $date = $request->query('date');
        $isPresent = $request->query('is_present');
        
        if ($memberId) {
            $query->where('member_id', $memberId);
        }
        
        if ($date) {
            $query->whereDate('date', $date);
        }
        
        if ($isPresent !== null) {
            $query->where('is_present', $isPresent);
        }
        
        // Pagination
        $perPage = $request->query('per_page', 10);
        $attendanceRecords = $query->orderBy('date', 'desc')->paginate($perPage);
        
        return response()->json($attendanceRecords);
    }
    
    public function getFilteredKasData(Request $request)
    {
        try {
            $user = Auth::user();
            
            // Handle berbagai role
            if ($user->role === 'siswa') {
                return $this->getMemberKasData($user, $request);
            } elseif ($user->role === 'koordinator') {
                return $this->getKoordinatorKasData($user, $request);
            } elseif ($user->role === 'staff') {
                return $this->getStaffKasData($user, $request);
            } elseif ($user->role === 'bendahara') {
                return $this->getBendaharaKasData($user, $request);
            } else {
                return response()->json([
                    'message' => 'Unauthorized role'
                ], 403);
            }
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to retrieve kas data',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    private function getMemberKasData($user, $request)
    {
        // Cari member record untuk user ini
        $member = $user->member;
        if (!$member) {
            return response()->json([
                'message' => 'Member record not found'
            ], 404);
        }
        
        // Filter berdasarkan eschool_id jika disediakan
        $eschoolId = $request->query('eschool_id');
        
        // Query dasar
        $query = DB::table('kas_payments')
            ->join('kas_records', 'kas_payments.kas_record_id', '=', 'kas_records.id')
            ->join('eschools', 'kas_records.eschool_id', '=', 'eschools.id')
            ->select(
                'eschools.name as eschool_name',
                'kas_records.description',
                'kas_records.date',
                'kas_payments.amount',
                'kas_payments.month',
                'kas_payments.year',
                'kas_payments.is_paid',
                'kas_payments.paid_date'
            )
            ->where('kas_payments.member_id', $member->id);
        
        // Terapkan filter eschool jika disediakan
        if ($eschoolId) {
            $query->where('kas_records.eschool_id', $eschoolId);
        }
        
        // Pagination
        $perPage = $request->query('per_page', 10);
        $kasRecords = $query->orderBy('kas_records.date', 'desc')->paginate($perPage);
        
        return response()->json($kasRecords);
    }
    
    private function getKoordinatorKasData($user, $request)
    {
        // Koordinator melihat data kas dari eschool yang mereka koordinatori
        $eschool = $user->coordinatedEschool;
        if (!$eschool) {
            return response()->json([
                'message' => 'No eschool assigned to coordinate'
            ], 404);
        }
        
        // Query kas records untuk eschool yang dikoordinatori
        $query = DB::table('kas_payments')
            ->join('kas_records', 'kas_payments.kas_record_id', '=', 'kas_records.id')
            ->join('eschools', 'kas_records.eschool_id', '=', 'eschools.id')
            ->join('members', 'kas_payments.member_id', '=', 'members.id')
            ->select(
                'members.name as member_name',
                'members.nip as member_nip',
                'eschools.name as eschool_name',
                'kas_records.description',
                'kas_records.date',
                'kas_payments.amount',
                'kas_payments.month',
                'kas_payments.year',
                'kas_payments.is_paid',
                'kas_payments.paid_date'
            )
            ->where('kas_records.eschool_id', $eschool->id);
        
        // Filter tambahan
        $memberId = $request->query('member_id');
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');
        $isPaid = $request->query('is_paid');
        
        if ($memberId) {
            $query->where('kas_payments.member_id', $memberId);
        }
        
        if ($startDate) {
            $query->where('kas_records.date', '>=', $startDate);
        }
        
        if ($endDate) {
            $query->where('kas_records.date', '<=', $endDate);
        }
        
        if ($isPaid !== null) {
            $query->where('kas_payments.is_paid', $isPaid);
        }
        
        // Pagination
        $perPage = $request->query('per_page', 10);
        $kasRecords = $query->orderBy('kas_records.date', 'desc')->paginate($perPage);
        
        return response()->json($kasRecords);
    }
    
    private function getStaffKasData($user, $request)
    {
        // Staff melihat data kas dari semua eschool di sekolahnya
        $query = DB::table('kas_payments')
            ->join('kas_records', 'kas_payments.kas_record_id', '=', 'kas_records.id')
            ->join('eschools', 'kas_records.eschool_id', '=', 'eschools.id')
            ->join('members', 'kas_payments.member_id', '=', 'members.id')
            ->select(
                'members.name as member_name',
                'members.nip as member_nip',
                'eschools.name as eschool_name',
                'kas_records.description',
                'kas_records.date',
                'kas_payments.amount',
                'kas_payments.month',
                'kas_payments.year',
                'kas_payments.is_paid',
                'kas_payments.paid_date'
            );
        
        // Filter
        $eschoolId = $request->query('eschool_id');
        $memberId = $request->query('member_id');
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');
        $isPaid = $request->query('is_paid');
        
        if ($eschoolId) {
            $query->where('kas_records.eschool_id', $eschoolId);
        }
        
        if ($memberId) {
            $query->where('kas_payments.member_id', $memberId);
        }
        
        if ($startDate) {
            $query->where('kas_records.date', '>=', $startDate);
        }
        
        if ($endDate) {
            $query->where('kas_records.date', '<=', $endDate);
        }
        
        if ($isPaid !== null) {
            $query->where('kas_payments.is_paid', $isPaid);
        }
        
        // Pagination
        $perPage = $request->query('per_page', 10);
        $kasRecords = $query->orderBy('kas_records.date', 'desc')->paginate($perPage);
        
        return response()->json($kasRecords);
    }
    
    private function getBendaharaKasData($user, $request)
    {
        // Bendahara melihat data kas dari eschool yang mereka kelola kasnya
        $member = $user->member;
        if (!$member) {
            return response()->json([
                'message' => 'Member record not found'
            ], 404);
        }
        
        // Cari eschool dimana user ini adalah bendahara
        $eschool = Eschool::where('treasurer_id', $member->id)->first();
        if (!$eschool) {
            return response()->json([
                'message' => 'No eschool assigned as treasurer'
            ], 404);
        }
        
        // Query kas records untuk eschool yang dibendaharai
        $query = DB::table('kas_payments')
            ->join('kas_records', 'kas_payments.kas_record_id', '=', 'kas_records.id')
            ->join('eschools', 'kas_records.eschool_id', '=', 'eschools.id')
            ->join('members', 'kas_payments.member_id', '=', 'members.id')
            ->select(
                'members.name as member_name',
                'members.nip as member_nip',
                'eschools.name as eschool_name',
                'kas_records.description',
                'kas_records.date',
                'kas_payments.amount',
                'kas_payments.month',
                'kas_payments.year',
                'kas_payments.is_paid',
                'kas_payments.paid_date'
            )
            ->where('kas_records.eschool_id', $eschool->id);
        
        // Filter tambahan
        $memberId = $request->query('member_id');
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');
        $isPaid = $request->query('is_paid');
        
        if ($memberId) {
            $query->where('kas_payments.member_id', $memberId);
        }
        
        if ($startDate) {
            $query->where('kas_records.date', '>=', $startDate);
        }
        
        if ($endDate) {
            $query->where('kas_records.date', '<=', $endDate);
        }
        
        if ($isPaid !== null) {
            $query->where('kas_payments.is_paid', $isPaid);
        }
        
        // Pagination
        $perPage = $request->query('per_page', 10);
        $kasRecords = $query->orderBy('kas_records.date', 'desc')->paginate($perPage);
        
        return response()->json($kasRecords);
    }
    
    public function exportAttendanceData(Request $request)
    {
        try {
            $user = Auth::user();
            
            // Handle berbagai role
            if ($user->role === 'siswa') {
                return $this->exportMemberAttendance($user, $request);
            } elseif (in_array($user->role, ['koordinator', 'staff', 'bendahara'])) {
                return $this->exportManagementAttendance($user, $request);
            } else {
                return response()->json([
                    'message' => 'Unauthorized role'
                ], 403);
            }
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to export attendance data',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    private function exportMemberAttendance($user, $request)
    {
        // Cari member record untuk user ini
        $member = $user->member;
        if (!$member) {
            return response()->json([
                'message' => 'Member record not found'
            ], 404);
        }
        
        // Filter berdasarkan eschool_id jika disediakan
        $eschoolId = $request->query('eschool_id');
        
        // Query dasar
        $query = DB::table('attendance_records')
            ->join('eschools', 'attendance_records.eschool_id', '=', 'eschools.id')
            ->select(
                'eschools.name as eschool_name',
                'attendance_records.date',
                'attendance_records.is_present',
                'attendance_records.notes'
            )
            ->where('attendance_records.member_id', $member->id);
        
        // Terapkan filter eschool jika disediakan
        if ($eschoolId) {
            $query->where('attendance_records.eschool_id', $eschoolId);
        }
        
        $attendanceRecords = $query->orderBy('attendance_records.date', 'desc')->get();
        
        // Format data untuk CSV
        $csvData = "Eschool,Date,Status,Notes\n";
        foreach ($attendanceRecords as $record) {
            $status = $record->is_present ? 'Present' : 'Absent';
            $notes = $record->notes ? '"' . str_replace('"', '""', $record->notes) . '"' : '';
            $csvData .= "\"{$record->eschool_name}\",\"{$record->date}\",\"{$status}\",{$notes}\n";
        }
        
        return response($csvData)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename="my-attendance-export.csv"');
    }
    
    private function exportManagementAttendance($user, $request)
    {
        // Tentukan eschool berdasarkan role
        $eschoolId = null;
        if ($user->role === 'koordinator') {
            $eschool = $user->coordinatedEschool;
            if (!$eschool) {
                return response()->json(['message' => 'No eschool assigned to coordinate'], 404);
            }
            $eschoolId = $eschool->id;
        } elseif ($user->role === 'bendahara') {
            $member = $user->member;
            if (!$member) {
                return response()->json(['message' => 'Member record not found'], 404);
            }
            $eschool = Eschool::where('treasurer_id', $member->id)->first();
            if (!$eschool) {
                return response()->json(['message' => 'No eschool assigned as treasurer'], 404);
            }
            $eschoolId = $eschool->id;
        }
        // Staff bisa akses semua eschool, jadi eschoolId tetap null
        
        // Query data
        $query = DB::table('attendance_records')
            ->join('eschools', 'attendance_records.eschool_id', '=', 'eschools.id')
            ->join('members', 'attendance_records.member_id', '=', 'members.id')
            ->select(
                'eschools.name as eschool_name',
                'members.name as member_name',
                'members.nip as member_nip',
                'attendance_records.date',
                'attendance_records.is_present',
                'attendance_records.notes'
            );
        
        if ($eschoolId) {
            $query->where('attendance_records.eschool_id', $eschoolId);
        }
        
        // Filter tambahan dari request
        if ($request->query('eschool_id')) {
            $query->where('attendance_records.eschool_id', $request->query('eschool_id'));
        }
        
        $attendanceRecords = $query->orderBy('attendance_records.date', 'desc')->get();
        
        // Format data untuk CSV
        $csvData = "Eschool,Member Name,Member NIP,Date,Status,Notes\n";
        foreach ($attendanceRecords as $record) {
            $status = $record->is_present ? 'Present' : 'Absent';
            $notes = $record->notes ? '"' . str_replace('"', '""', $record->notes) . '"' : '';
            $csvData .= "\"{$record->eschool_name}\",\"{$record->member_name}\",\"{$record->member_nip}\",\"{$record->date}\",\"{$status}\",{$notes}\n";
        }
        
        $filename = $user->role . "-attendance-export.csv";
        return response($csvData)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', "attachment; filename=\"{$filename}\"");
    }
    
    public function exportKasData(Request $request)
    {
        try {
            $user = Auth::user();
            
            // Handle berbagai role
            if ($user->role === 'siswa') {
                return $this->exportMemberKas($user, $request);
            } elseif (in_array($user->role, ['koordinator', 'staff', 'bendahara'])) {
                return $this->exportManagementKas($user, $request);
            } else {
                return response()->json([
                    'message' => 'Unauthorized role'
                ], 403);
            }
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to export kas data',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    private function exportMemberKas($user, $request)
    {
        // Cari member record untuk user ini
        $member = $user->member;
        if (!$member) {
            return response()->json([
                'message' => 'Member record not found'
            ], 404);
        }
        
        // Filter berdasarkan eschool_id jika disediakan
        $eschoolId = $request->query('eschool_id');
        
        // Query dasar
        $query = DB::table('kas_payments')
            ->join('kas_records', 'kas_payments.kas_record_id', '=', 'kas_records.id')
            ->join('eschools', 'kas_records.eschool_id', '=', 'eschools.id')
            ->select(
                'eschools.name as eschool_name',
                'kas_records.description',
                'kas_records.date',
                'kas_payments.amount',
                'kas_payments.month',
                'kas_payments.year',
                'kas_payments.is_paid',
                'kas_payments.paid_date'
            )
            ->where('kas_payments.member_id', $member->id);
        
        // Terapkan filter eschool jika disediakan
        if ($eschoolId) {
            $query->where('kas_records.eschool_id', $eschoolId);
        }
        
        $kasRecords = $query->orderBy('kas_records.date', 'desc')->get();
        
        // Format data untuk CSV
        $csvData = "Eschool,Description,Date,Period,Amount,Status,Paid Date\n";
        foreach ($kasRecords as $record) {
            $status = $record->is_paid ? 'Paid' : 'Pending';
            $paidDate = $record->paid_date ? $record->paid_date : '';
            $period = "{$record->month}/{$record->year}";
            $amount = number_format($record->amount, 0, '', '');
            $csvData .= "\"{$record->eschool_name}\",\"{$record->description}\",\"{$record->date}\",\"{$period}\",\"{$amount}\",\"{$status}\",\"{$paidDate}\"\n";
        }
        
        return response($csvData)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename="my-kas-export.csv"');
    }
    
    private function exportManagementKas($user, $request)
    {
        // Tentukan eschool berdasarkan role
        $eschoolId = null;
        if ($user->role === 'koordinator') {
            $eschool = $user->coordinatedEschool;
            if (!$eschool) {
                return response()->json(['message' => 'No eschool assigned to coordinate'], 404);
            }
            $eschoolId = $eschool->id;
        } elseif ($user->role === 'bendahara') {
            $member = $user->member;
            if (!$member) {
                return response()->json(['message' => 'Member record not found'], 404);
            }
            $eschool = Eschool::where('treasurer_id', $member->id)->first();
            if (!$eschool) {
                return response()->json(['message' => 'No eschool assigned as treasurer'], 404);
            }
            $eschoolId = $eschool->id;
        }
        // Staff bisa akses semua eschool, jadi eschoolId tetap null
        
        // Query data
        $query = DB::table('kas_payments')
            ->join('kas_records', 'kas_payments.kas_record_id', '=', 'kas_records.id')
            ->join('eschools', 'kas_records.eschool_id', '=', 'eschools.id')
            ->join('members', 'kas_payments.member_id', '=', 'members.id')
            ->select(
                'eschools.name as eschool_name',
                'members.name as member_name',
                'members.nip as member_nip',
                'kas_records.description',
                'kas_records.date',
                'kas_payments.amount',
                'kas_payments.month',
                'kas_payments.year',
                'kas_payments.is_paid',
                'kas_payments.paid_date'
            );
        
        if ($eschoolId) {
            $query->where('kas_records.eschool_id', $eschoolId);
        }
        
        // Filter tambahan dari request
        if ($request->query('eschool_id')) {
            $query->where('kas_records.eschool_id', $request->query('eschool_id'));
        }
        
        $kasRecords = $query->orderBy('kas_records.date', 'desc')->get();
        
        // Format data untuk CSV
        $csvData = "Eschool,Member Name,Member NIP,Description,Date,Period,Amount,Status,Paid Date\n";
        foreach ($kasRecords as $record) {
            $status = $record->is_paid ? 'Paid' : 'Pending';
            $paidDate = $record->paid_date ? $record->paid_date : '';
            $period = "{$record->month}/{$record->year}";
            $amount = number_format($record->amount, 0, '', '');
            $csvData .= "\"{$record->eschool_name}\",\"{$record->member_name}\",\"{$record->member_nip}\",\"{$record->description}\",\"{$record->date}\",\"{$period}\",\"{$amount}\",\"{$status}\",\"{$paidDate}\"\n";
        }
        
        $filename = $user->role . "-kas-export.csv";
        return response($csvData)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', "attachment; filename=\"{$filename}\"");
    }
}