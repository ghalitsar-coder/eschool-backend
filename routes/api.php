<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\MultiRoleProfileController;
use App\Http\Controllers\Api\KasRecordController;
use App\Http\Controllers\Api\KasPaymentController;
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

// Protected routes
Route::middleware('auth:api')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/refresh', [AuthController::class, 'refresh']);
    Route::get('/me', [AuthController::class, 'me']);
    
    // Multi-role profile routes
    Route::get('/profile/multi-role', [MultiRoleProfileController::class, 'getMultiRoleProfile']);
    
   Route::middleware(['eschool.role:coordinator,treasurer'])->group(function () {
    // Semua route di dalam group ini pakai middleware "eschool.role"
    
    Route::get('/members/{eschoolId}', [\App\Http\Controllers\MemberController::class, 'getMembersByEschool']);
    // Route::post('/members/{eschoolId}', [\App\Http\Controllers\MemberController::class, 'addMember']);
    // Route::delete('/members/{eschoolId}/{userId}', [\App\Http\Controllers\MemberController::class, 'removeMember']);
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
