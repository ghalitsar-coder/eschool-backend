<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\KasController;
use App\Http\Controllers\MemberController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\MemberManagementController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Include debug routes
require_once 'debug.php';

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
    
    // Routes khusus siswa
    Route::middleware(['role:siswa'])->group(function () {
        Route::get('/siswa/dashboard', function () {
            return response()->json([
                'message' => 'Welcome to Siswa Dashboard',
                'access' => 'Siswa-specific content'
            ]);
        });
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
        Route::post('/kas/income', [KasController::class, 'storeIncome']);
        Route::post('/kas/expense', [KasController::class, 'storeExpense']);
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
            Route::get('members/available', [AttendanceController::class, 'available']);
            // Record attendance
            Route::post('record', [AttendanceController::class, 'store']);
            
            // Get attendance records
            Route::get('records', [AttendanceController::class, 'index']);
            Route::get('records/{attendance}', [AttendanceController::class, 'show']);
            
            // Update/Delete attendance
            Route::put('records/{attendance}', [AttendanceController::class, 'update']);
            Route::delete('records/{attendance}', [AttendanceController::class, 'destroy']);

            // Export attendance records
            Route::get('export/csv', [AttendanceController::class, 'exportCsv']);
            Route::get('export/pdf', [AttendanceController::class, 'exportPdf']);

            Route::get('statistics', [AttendanceController::class, 'AttendanceStatistics']);
        });

        // Member management routes
        Route::prefix('members')->group(function () {
            Route::get('/', [MemberManagementController::class, 'index']);
            Route::post('/', [MemberManagementController::class, 'store']);
            Route::get('/{id}', [MemberManagementController::class, 'show']);
            Route::put('/{id}', [MemberManagementController::class, 'update']);
            Route::delete('/{id}', [MemberManagementController::class, 'destroy']);
            
            // Helper routes
            Route::get('/users/available', [MemberManagementController::class, 'getAvailableUsers']);
            Route::get('/schools', [MemberManagementController::class, 'getSchools']);
            Route::get('/eschools', [MemberManagementController::class, 'getEschools']);
        });
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