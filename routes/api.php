<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\MultiRoleProfileController;
use App\Http\Controllers\Api\KasRecordController;
use App\Http\Controllers\Api\KasPaymentController;
use App\Http\Controllers\Api\ProfileController;
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
    
    // Dashboard routes
    Route::get('/dashboard/multi-role-profile', [App\Http\Controllers\Api\DashboardController::class, 'getMultiRoleProfile']);
    Route::get('/dashboard/attendance/statistics', [App\Http\Controllers\Api\DashboardController::class, 'getAttendanceStatistics']);
    Route::get('/dashboard/attendance/analytics', [App\Http\Controllers\Api\DashboardController::class, 'getAttendanceAnalytics']);
    Route::get('/dashboard/kas/summary', [App\Http\Controllers\Api\DashboardController::class, 'getKasSummary']);
    Route::get('/dashboard/staff/overview', [App\Http\Controllers\Api\DashboardController::class, 'getStaffOverview']);
    
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
});
