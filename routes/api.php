<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\MultiRoleProfileController;
use App\Http\Controllers\Api\KasRecordController;
use App\Http\Controllers\Api\KasPaymentController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\AttendanceController;
use App\Http\Controllers\MembersController;
use App\Http\Controllers\Api\UserController; // Add this line
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Public routes for authentication
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/refresh', [AuthController::class, 'refresh']); // Refresh token endpoint

// Protected routes
Route::middleware('auth:api')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);
    
    // Profile routes
    Route::get('/member/profile', [ProfileController::class, 'getProfile']);
    Route::get('/multi-role/profile', [MultiRoleProfileController::class, 'getMultiRoleProfile']);
    
    // User management routes
    Route::post('/users', [UserController::class, 'createUser']);
    
    // Eschool management routes
    Route::get('/eschools', [App\Http\Controllers\Api\EschoolController::class, 'index']);
    Route::get('/eschools/{id}', [App\Http\Controllers\Api\EschoolController::class, 'show']);
    Route::post('/eschools', [App\Http\Controllers\Api\EschoolController::class, 'store']);
    Route::put('/eschools/{id}', [App\Http\Controllers\Api\EschoolController::class, 'update']);
    Route::delete('/eschools/{id}', [App\Http\Controllers\Api\EschoolController::class, 'destroy']);
    Route::get('/eschools/users/coordinators', [App\Http\Controllers\Api\EschoolController::class, 'getEligibleCoordinators']);
    
    // Supervisor routes
    Route::prefix('supervisor')->group(function () {
        Route::get('/eligible-treasurers', [App\Http\Controllers\Api\SupervisorController::class, 'getEligibleTreasurers']);
        Route::get('/eligible-coordinators', [App\Http\Controllers\Api\SupervisorController::class, 'getEligibleCoordinators']);
    });
    
    // Dashboard routes
    Route::get('/dashboard/multi-role-profile', [App\Http\Controllers\Api\DashboardController::class, 'getMultiRoleProfile']);
    Route::get('/dashboard/attendance/statistics', [App\Http\Controllers\Api\DashboardController::class, 'getAttendanceStatistics']);
    Route::get('/dashboard/attendance/analytics', [App\Http\Controllers\Api\DashboardController::class, 'getAttendanceAnalytics']);
    Route::get('/dashboard/kas/summary', [App\Http\Controllers\Api\DashboardController::class, 'getKasSummary']);
    
    // Analytics routes
    Route::get('/analytics/eschools', [App\Http\Controllers\Api\DashboardController::class, 'getEschoolAnalytics']);
    Route::get('/analytics/financial', [App\Http\Controllers\Api\DashboardController::class, 'getFinancialAnalytics']);
    Route::get('/analytics/attendance', [App\Http\Controllers\Api\DashboardController::class, 'getAttendanceAnalyticsData']);

    
   Route::middleware(['eschool.role:coordinator,treasurer'])->group(function () {
    // Semua route di dalam group ini pakai middleware "eschool.role"
    
    Route::get('/members/{eschoolId}', [App\Http\Controllers\MemberController::class, 'getMembersByEschool']);
    // Route::post('/members/{eschoolId}', [App\Http\Controllers\MemberController::class, 'addMember']);
    // Route::delete('/members/{eschoolId}/{userId}', [App\Http\Controllers\MemberController::class, 'removeMember']);
    });
    // Kas management routes
    // Kas Record routes
    Route::post('/kas/records', [KasRecordController::class, 'store']);
    Route::post('/kas/income', [KasRecordController::class, 'storeIncomeWithPayments']);
    Route::get('/kas/records/{eschoolId}', [KasRecordController::class, 'index']);
    Route::put('/kas/records/{id}', [KasRecordController::class, 'update']);
    Route::delete('/kas/records/{id}', [KasRecordController::class, 'destroy']);
    Route::get('/kas/export/{eschoolId}', [KasRecordController::class, 'export']);
    
    // Kas Payment routes
    Route::post('/kas/payments', [KasPaymentController::class, 'store']);
    Route::get('/kas/payments/member/{userEschoolRoleId}', [KasPaymentController::class, 'getMemberPayments']);
    Route::get('/kas/payments/summary/{eschoolId}', [KasPaymentController::class, 'getEschoolPaymentSummary']);
    Route::put('/kas/payments/{id}', [KasPaymentController::class, 'update']);
    
    // Eschool-scoped attendance management routes
    Route::prefix('eschool/{eschoolId}')->group(function () {
        
        // Members list route for attendance system
        Route::get('/members/list', [MembersController::class, 'list']);
        
        // Attendance CRUD routes
        Route::prefix('attendance')->group(function () {
            // List attendance records with filtering and pagination
            Route::get('/records', [AttendanceController::class, 'index']);
            
            // Create attendance records (with file upload support) - frontend expects /record
            Route::post('/record', [AttendanceController::class, 'store']);
            Route::post('/records', [AttendanceController::class, 'store']);
            
            // Individual attendance record management
            Route::get('/records/{id}', [AttendanceController::class, 'show']);
            Route::put('/records/{id}', [AttendanceController::class, 'update']);
            Route::delete('/records/{id}', [AttendanceController::class, 'destroy']);
            
            // Statistics and analytics
            Route::get('/statistics', [AttendanceController::class, 'statistics']);
            Route::get('/analytics', [AttendanceController::class, 'analytics']);
            
            // Export functionality
            Route::get('/export/csv', [AttendanceController::class, 'exportCsv']);
            Route::get('/export/pdf', [AttendanceController::class, 'exportPdf']);
        });
    });
});
