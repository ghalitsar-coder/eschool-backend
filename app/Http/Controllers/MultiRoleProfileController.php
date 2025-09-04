<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Eschool;
use App\Models\KasRecord;
use App\Models\AttendanceRecord;
use App\Models\Member;

class MultiRoleProfileController extends Controller
{
    public function getMultiRoleProfile(Request $request)
    {
        try {
            $user = Auth::user();
            
            // Get all eschool roles for this user
            $eschoolRoles = $user->eschoolRoles()->with('eschool')->get();
            
            // If user has no roles, return basic info
            if ($eschoolRoles->isEmpty()) {
                return response()->json([
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'base_role' => $user->role,
                        'is_system_admin' => false
                    ],
                    'eschool_roles' => [],
                    'overall_summary' => [
                        'total_eschools' => 0,
                        'roles' => [
                            'koordinator' => 0,
                            'bendahara' => 0,
                            'member' => 0
                        ],
                        'performance' => [
                            'avg_attendance_rate' => 0,
                            'total_kas_managed' => 0,
                            'total_personal_kas' => 0,
                            'overall_activity_score' => 0
                        ]
                    ],
                    'recent_activities' => []
                ]);
            }
            
            // Collect data for each eschool role
            $eschoolRolesData = [];
            $roleCounts = [
                'koordinator' => 0,
                'bendahara' => 0,
                'member' => 0
            ];
            
            $totalAttendanceRate = 0;
            $totalKasManaged = 0;
            $totalPersonalKas = 0;
            $allActivities = [];
            
            foreach ($eschoolRoles as $role) {
                $eschool = $role->eschool;
                $roleCounts[$role->role]++;
                
                // Get attendance summary
                $attendanceSummary = $this->getAttendanceSummary($user, $eschool, $role->role);
                
                // Get kas summary
                $kasSummary = $this->getKasSummary($user, $eschool, $role->role);
                
                // Collect activities
                $activities = $this->getRecentActivities($user, $eschool, $role->role);
                $allActivities = array_merge($allActivities, $activities);
                
                // Add to totals for overall summary
                $totalAttendanceRate += $attendanceSummary['attendance_rate'];
                if ($role->role === 'bendahara') {
                    $totalKasManaged += $kasSummary['total_balance'] ?? 0;
                }
                $totalPersonalKas += $kasSummary['personal_balance'] ?? 0;
                
                $eschoolRolesData[] = [
                    'eschool_id' => $eschool->id,
                    'eschool_name' => $eschool->name,
                    'school_id' => $eschool->school_id,
                    'role_in_eschool' => $role->role,
                    'permissions' => $role->getPermissions(),
                    'assigned_at' => $role->created_at->toISOString(),
                    'status' => 'active',
                    'kas_summary' => $kasSummary,
                    'attendance_summary' => $attendanceSummary
                ];
            }
            
            // Sort activities by date (newest first) and take top 10
            usort($allActivities, function($a, $b) {
                return strtotime($b['date']) - strtotime($a['date']);
            });
            $recentActivities = array_slice($allActivities, 0, 10);
            
            // Calculate overall performance metrics
            $avgAttendanceRate = count($eschoolRolesData) > 0 ? 
                round($totalAttendanceRate / count($eschoolRolesData), 2) : 0;
                
            $overallActivityScore = $this->calculateActivityScore($eschoolRolesData);
            
            return response()->json([
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'base_role' => $user->role,
                    'is_system_admin' => false
                ],
                'eschool_roles' => $eschoolRolesData,
                'overall_summary' => [
                    'total_eschools' => count($eschoolRolesData),
                    'roles' => $roleCounts,
                    'performance' => [
                        'avg_attendance_rate' => $avgAttendanceRate,
                        'total_kas_managed' => $totalKasManaged,
                        'total_personal_kas' => $totalPersonalKas,
                        'overall_activity_score' => $overallActivityScore
                    ]
                ],
                'recent_activities' => $recentActivities
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to retrieve multi-role profile data',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    private function getAttendanceSummary($user, $eschool, $role)
    {
        $member = $user->member;
        if (!$member) {
            return [
                'total_meetings' => 0,
                'attended' => 0,
                'attendance_rate' => 0
            ];
        }
        
        // Get total attendance records for this eschool
        $totalRecords = AttendanceRecord::where('eschool_id', $eschool->id)
            ->where('member_id', $member->id)
            ->count();
            
        // Get present records
        $presentRecords = AttendanceRecord::where('eschool_id', $eschool->id)
            ->where('member_id', $member->id)
            ->where('is_present', true)
            ->count();
            
        $attendanceRate = $totalRecords > 0 ? 
            round(($presentRecords / $totalRecords) * 100, 2) : 0;
            
        return [
            'total_meetings' => $totalRecords,
            'attended' => $presentRecords,
            'attendance_rate' => $attendanceRate
        ];
    }
    
    private function getKasSummary($user, $eschool, $role)
    {
        $member = $user->member;
        if (!$member) {
            return [
                'total_balance' => 0,
                'monthly_target' => $eschool->monthly_kas_amount,
                'collection_rate' => 0,
                'pending_approvals' => 0,
                'personal_balance' => 0,
                'payment_status' => 'up_to_date'
            ];
        }
        
        // Get kas records for this eschool
        $kasRecords = KasRecord::where('eschool_id', $eschool->id)
            ->where('type', 'income')
            ->get();
            
        $totalExpected = 0;
        $totalPaid = 0;
        
        // Calculate expected and paid amounts
        foreach ($kasRecords as $record) {
            $payments = $record->payments()->where('member_id', $member->id)->get();
            foreach ($payments as $payment) {
                $totalExpected += $payment->amount;
                if ($payment->is_paid) {
                    $totalPaid += $payment->amount;
                }
            }
        }
        
        $collectionRate = $totalExpected > 0 ? 
            round(($totalPaid / $totalExpected) * 100, 2) : 100;
            
        // Determine payment status
        $paymentStatus = 'up_to_date';
        if ($collectionRate < 100) {
            $paymentStatus = $collectionRate < 75 ? 'overdue' : 'partial';
        }
        
        return [
            'total_balance' => $totalPaid,
            'monthly_target' => $eschool->monthly_kas_amount,
            'collection_rate' => $collectionRate,
            'pending_approvals' => 0, // For future implementation
            'personal_balance' => $totalPaid,
            'payment_status' => $paymentStatus
        ];
    }
    
    private function getRecentActivities($user, $eschool, $role)
    {
        $activities = [];
        $member = $user->member;
        
        if (!$member) {
            return $activities;
        }
        
        // Get recent attendance records
        $attendanceRecords = AttendanceRecord::with('eschool')
            ->where('eschool_id', $eschool->id)
            ->where('member_id', $member->id)
            ->orderBy('date', 'desc')
            ->limit(5)
            ->get();
            
        foreach ($attendanceRecords as $record) {
            $activities[] = [
                'type' => 'attendance',
                'eschool_name' => $eschool->name,
                'description' => $record->is_present ? 'Hadir di pertemuan' : 'Tidak hadir di pertemuan',
                'date' => $record->date,
                'role_context' => $role
            ];
        }
        
        // Get recent kas payments
        $kasRecords = KasRecord::with('payments')
            ->where('eschool_id', $eschool->id)
            ->where('type', 'income')
            ->orderBy('date', 'desc')
            ->limit(5)
            ->get();
            
        foreach ($kasRecords as $record) {
            $payments = $record->payments()->where('member_id', $member->id)->get();
            foreach ($payments as $payment) {
                if ($payment->is_paid) {
                    $activities[] = [
                        'type' => 'kas_transaction',
                        'eschool_name' => $eschool->name,
                        'description' => "Membayar kas bulan {$payment->month} {$payment->year}",
                        'amount' => $payment->amount,
                        'date' => $payment->paid_date ?? $record->date,
                        'role_context' => $role
                    ];
                }
            }
        }
        
        return $activities;
    }
    
    private function calculateActivityScore($eschoolRolesData)
    {
        if (empty($eschoolRolesData)) {
            return 0;
        }
        
        $totalScore = 0;
        $count = 0;
        
        foreach ($eschoolRolesData as $roleData) {
            // Score based on attendance rate (40% weight)
            $attendanceScore = $roleData['attendance_summary']['attendance_rate'] * 0.4;
            
            // Score based on kas payment rate (40% weight)
            $kasScore = ($roleData['kas_summary']['collection_rate'] ?? 0) * 0.4;
            
            // Score based on role responsibility (20% weight)
            $roleScore = 0;
            if ($roleData['role_in_eschool'] === 'koordinator') {
                $roleScore = 20; // Full points for coordinator
            } elseif ($roleData['role_in_eschool'] === 'bendahara') {
                $roleScore = 15; // High points for treasurer
            } else {
                $roleScore = 10; // Base points for member
            }
            
            $totalScore += ($attendanceScore + $kasScore + $roleScore);
            $count++;
        }
        
        return round($totalScore / $count, 2);
    }
}