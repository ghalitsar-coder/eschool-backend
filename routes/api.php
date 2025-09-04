<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\KasController;
use App\Http\Controllers\MemberController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\MemberManagementController;
use App\Http\Controllers\EschoolController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Include debug routes
require_once 'debug.php';

// Include multi-role example routes
require_once 'multi_role_examples.php';

Route::get('/test', function () {
    return response()->json(['message' => 'API Route Working!']);
});
Route::post('/test-login', function (Request $request) {
    return response()->json([
        'message' => 'Test login endpoint',
        'data_received' => $request->all()
    ]);
});

// Public routes
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);
Route::post('/refresh', [AuthController::class, 'refresh']); // Refresh token endpoint

// Debug route untuk test token
Route::get('/debug-token', function (Request $request) {
    return response()->json([
        'cookies' => $request->cookies->all(),
        'headers' => $request->headers->all(),
        'token_from_cookie' => $request->cookie('token'),
        'bearer_token' => $request->bearerToken(),
        'auth_header' => $request->header('Authorization'),
    ]);
});

// Protected routes
Route::middleware('auth:api')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', function (Request $request) {
        return response()->json([
            'user' => $request->user(),
            'role' => $request->user()->role
        ]);
    });
    
    // MULTI-ROLE ATTENDANCE MANAGEMENT ROUTES (Production Ready)
    // These routes handle attendance management with proper multi-role security
    Route::prefix('eschool/{eschool_id}')->middleware('auth:api')->group(function () {
        // Members list for attendance (koordinator access)
        Route::get('/members/list', [AttendanceController::class, 'getMembersList'])
            ->middleware('eschool.role:koordinator,{eschool_id}');
        
        // Attendance analytics (koordinator access)
        Route::get('/attendance/analytics', [AttendanceController::class, 'getAnalytics'])
            ->middleware('eschool.role:koordinator,{eschool_id}');
        
        // Attendance statistics (koordinator access)
        Route::get('/attendance/statistics', [AttendanceController::class, 'getStatistics'])
            ->middleware('eschool.role:koordinator,{eschool_id}');
        
        // Attendance records (koordinator access)
        Route::get('/attendance/records', [AttendanceController::class, 'getRecords'])
            ->middleware('eschool.role:koordinator,{eschool_id}');
        
        // Create attendance record (koordinator access)
        Route::post('/attendance/record', [AttendanceController::class, 'createRecord'])
            ->middleware('eschool.role:koordinator,{eschool_id}');
        
        // Update attendance record (koordinator access)
        Route::put('/attendance/records/{attendance}', [AttendanceController::class, 'update'])
            ->middleware('eschool.role:koordinator,{eschool_id}');
        
        // Delete attendance record (koordinator access) - REMOVED TO AVOID CONFLICT
        // Route::delete('/attendance/records/{attendance}', [AttendanceController::class, 'destroy'])
        //     ->middleware('eschool.role:koordinator,{eschool_id}');
        
        // Export attendance CSV (koordinator access)
        Route::get('/attendance/export/csv', [AttendanceController::class, 'exportCsv'])
            ->middleware('eschool.role:koordinator,{eschool_id}');
        
        // Delete attendance record (koordinator access) - NEW ROUTE
        Route::delete('/attendance/records/{recordId}', [AttendanceController::class, 'destroyRecord'])
            ->middleware('eschool.role:koordinator,{eschool_id}');
            
        // MULTI-ROLE MEMBER MANAGEMENT ROUTES
        // Get members for management (koordinator access)
        Route::get('/members/manage', [App\Http\Controllers\MultiRoleMemberController::class, 'index'])
            ->middleware('eschool.role:koordinator,{eschool_id}');
            
        // Get available users for assignment to this eschool
        Route::get('/users/available-for-eschool', [App\Http\Controllers\MultiRoleMemberController::class, 'getAvailableUsers'])
            ->middleware('eschool.role:koordinator,{eschool_id}');
            
        // Assign role to user in this eschool
        Route::post('/members/assign-role', [App\Http\Controllers\MultiRoleMemberController::class, 'assignRole'])
            ->middleware('eschool.role:koordinator,{eschool_id}');
            
        // Update user role in this eschool
        Route::put('/members/{user_id}/update-role', [App\Http\Controllers\MultiRoleMemberController::class, 'updateRole'])
            ->middleware('eschool.role:koordinator,{eschool_id}');
            
        // Remove user role from this eschool
        Route::delete('/members/{user_id}/remove-role', [App\Http\Controllers\MultiRoleMemberController::class, 'removeRole'])
            ->middleware('eschool.role:koordinator,{eschool_id}');
    });
    
    // Eschool routes (accessible by all authenticated users)
    Route::prefix('eschools')->group(function () {
        Route::get('/', [EschoolController::class, 'index']);
        Route::get('/{id}', [EschoolController::class, 'show']);
        
        // Staff-only routes
        Route::middleware('role:staff')->group(function () {
            Route::post('/', [EschoolController::class, 'store']);
            Route::put('/{id}', [EschoolController::class, 'update']);
            Route::delete('/{id}', [EschoolController::class, 'destroy']);
            
            // Additional routes for staff
            Route::get('/users/treasurers', [EschoolController::class, 'getEligibleTreasurers']);
        });
    });
    
    // Routes khusus siswa
    Route::middleware(['role:siswa'])->group(function () {
        Route::get('/siswa/dashboard', function () {
            return response()->json([
                'message' => 'Welcome to Siswa Dashboard',
                'access' => 'Siswa-specific content'
            ]);
        });
    });

    // Member profile routes (accessible by siswa, koordinator, staff)
    Route::middleware(['role:siswa,koordinator,staff,bendahara'])->group(function () {
        Route::get('/member/profile', [\App\Http\Controllers\MemberProfileController::class, 'getMemberProfileData']);
        Route::get('/member/attendance', [\App\Http\Controllers\MemberProfileController::class, 'getFilteredAttendanceData']);
        Route::get('/member/kas', [\App\Http\Controllers\MemberProfileController::class, 'getFilteredKasData']);
        Route::get('/member/attendance/export', [\App\Http\Controllers\MemberProfileController::class, 'exportAttendanceData']);
        Route::get('/member/kas/export', [\App\Http\Controllers\MemberProfileController::class, 'exportKasData']);
        
        // Multi-role profile route
        Route::get('/profile/multi-role', [\App\Http\Controllers\MultiRoleProfileController::class, 'getMultiRoleProfile']);
    });

    // Routes khusus bendahara & koordinator (termasuk member management)
    Route::middleware('role:bendahara,koordinator')->group(function () {
        Route::get('/members', [MemberController::class, 'index']);
        Route::post('/members', [MemberController::class, 'store']);
        Route::get('/members/{id}', [MemberController::class, 'show']);
        Route::put('/members/{id}', [MemberController::class, 'update']);
        Route::delete('/members/{id}', [MemberController::class, 'destroy']);
        
        // Bendahara specific routes
        Route::get('/bendahara/dashboard', function () {
            return response()->json([
                'message' => 'Welcome to Bendahara Dashboard',
                'access' => 'Financial management tools'
            ]);
        });
        
        // Kas management routes
        Route::get('/kas/check-payment', [KasController::class, 'checkPayment']);
        Route::get('/kas/summary', [KasController::class, 'getSummary']);
        Route::get('/kas/records', [KasController::class, 'getKasRecords']);
        Route::get('/kas/members', [KasController::class, 'getMembers']);
        Route::post('/kas/income', [KasController::class, 'storeIncome']);
        Route::post('/kas/expense', [KasController::class, 'storeExpense']);
        Route::put('/kas/records/{id}', [KasController::class, 'update']);
        Route::get('/kas/export/csv', [KasController::class, 'exportCsv']);
    });

    // Routes khusus koordinator
    Route::middleware('role:koordinator')->group(function () {
        Route::get('/koordinator/dashboard', function () {
            return response()->json([
                'message' => 'Welcome to Koordinator Dashboard',
                'access'  => 'Coordination tools'
            ]);
        });

        Route::get('/koordinator/activities', function () {
            return response()->json(['message' => 'Activity management']);
        });

        // Attendance routes
        Route::prefix('attendance')->group(function () {
            // Get members for attendance taking
            // Get members for attendance page
            // Route::get('members', [AttendanceController::class, 'getMembers']);
            // Record attendance
            Route::post('record', [AttendanceController::class, 'store']);
            
            // Get attendance records
            Route::get('records', [AttendanceController::class, 'index']);
            Route::get('records/{attendance}', [AttendanceController::class, 'show']);
            
            // Update attendance
            Route::put('records/{attendance}', [AttendanceController::class, 'update']);
            // DELETE route removed to avoid conflict with multi-role route
            
            // Direct attendance access (for frontend compatibility)
            Route::get('{id}', [AttendanceController::class, 'show']);
            Route::put('{id}', [AttendanceController::class, 'update']);
            // DELETE route removed to avoid conflict with multi-role route
            
            // Export attendance records
            Route::get('export/csv', [AttendanceController::class, 'exportCsv']);
            Route::get('export/pdf', [AttendanceController::class, 'exportPdf']);
            
            // Analytics
            Route::get('analytics', [AttendanceController::class, 'analytics']);
            
            Route::get('statistics', [AttendanceController::class, 'AttendanceStatistics']);
            Route::prefix('members')->group(function () {
                Route::get('/', [MemberManagementController::class, 'index']);
                Route::post('/', [MemberManagementController::class, 'store']);
                Route::get('/available', [AttendanceController::class, 'available']);
                Route::get('/{id}', [MemberManagementController::class, 'show']);
                Route::put('/{id}', [MemberManagementController::class, 'update']);
                Route::delete('/{id}', [MemberManagementController::class, 'destroy']);
                
                // Helper routes
                Route::get('/users/available', [MemberManagementController::class, 'getAvailableUsers']);
                Route::get('/schools', [MemberManagementController::class, 'getSchools']);
                Route::get('/eschools', [MemberManagementController::class, 'getEschools']);
            });
        });

        // Member management routes
    });

    // Routes khusus staff
    Route::middleware('role:staff')->group(function () {
        Route::get('/staff/dashboard', function () {
            return response()->json([
                'message' => 'Welcome to Staff Dashboard',
                'access' => 'Staff administration tools'
            ]);
        });
        
        Route::get('/staff/tasks', function () {
            return response()->json(['message' => 'Staff tasks management']);
        });
    });

    // Route untuk multiple roles (bendahara dan koordinator)
    Route::middleware('role:bendahara,koordinator')->group(function () {
        Route::get('/management/reports', function () {
            return response()->json(['message' => 'Management reports accessed']);
        });
        
        Route::get('/management/analytics', function () {
            return response()->json(['message' => 'Analytics dashboard']);
        });
    });
    
    // Analytics routes (accessible by all authenticated users)
    Route::prefix('analytics')->group(function () {
        Route::get('/eschools', [\App\Http\Controllers\AnalyticsController::class, 'getEschoolAnalytics']);
        Route::get('/financial', [\App\Http\Controllers\AnalyticsController::class, 'getFinancialAnalytics']);
        Route::get('/attendance', [\App\Http\Controllers\AnalyticsController::class, 'getAttendanceAnalytics']);
    });

    // Route untuk testing semua roles
    Route::get('/test/role-access', function (Request $request) {
        return response()->json([
            'message' => 'Role access test successful',
            'user_role' => $request->user()->role,
            'is_siswa' => $request->user()->isSiswa(),
            'is_bendahara' => $request->user()->isBendahara(),
            'is_koordinator' => $request->user()->isKoordinator(),
            'is_staff' => $request->user()->isStaff(),
        ]);
    })->middleware('role:siswa,bendahara,koordinator,staff');
});