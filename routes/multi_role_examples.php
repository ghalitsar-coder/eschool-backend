<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\KasController;
use App\Http\Controllers\MemberController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\MemberManagementController;
use App\Http\Controllers\EschoolController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/**
 * MULTI-ROLE API ROUTES - TESTING & DEBUG EXAMPLES ONLY
 * 
 * ⚠️  WARNING: These routes are for TESTING and DEBUGGING purposes only!
 * ⚠️  Production attendance routes have been moved to api.php
 * 
 * These routes demonstrate how to use the new multi-role middleware:
 * - eschool.role: Check if user has specific role in specific eschool
 * - eschool.permission: Check if user has specific permission in specific eschool
 * 
 * Usage patterns:
 * - eschool.role:koordinator,1 = User must be koordinator in eschool 1
 * - eschool.role:koordinator|bendahara,{eschool_id} = User must be koordinator OR bendahara in dynamic eschool from route param
 * - eschool.permission:manage_kas,1 = User must have manage_kas permission in eschool 1
 * - eschool.permission:view_all_reports|manage_members,{eschool_id} = User must have view_all_reports OR manage_members permission
 */

// Example: Multi-role protected routes
Route::middleware('auth:api')->group(function () {
    
    // KOORDINATOR-only routes for specific eschool
    Route::prefix('eschool/{eschool_id}/koordinator')->middleware('eschool.role:koordinator,{eschool_id}')->group(function () {
        Route::get('/dashboard', function (Request $request) {
            $eschoolId = $request->route('eschool_id');
            return response()->json([
                'message' => 'Koordinator Dashboard',
                'eschool_id' => $eschoolId,
                'user' => $request->user()->name,
                'access' => 'Full coordination control for this eschool'
            ]);
        });
        
        Route::post('/assign-role', function (Request $request) {
            return response()->json([
                'message' => 'Only koordinator can assign roles',
                'eschool_id' => $request->route('eschool_id')
            ]);
        });
    });

    // BENDAHARA-only routes for specific eschool
    Route::prefix('eschool/{eschool_id}/bendahara')->middleware('eschool.role:bendahara,{eschool_id}')->group(function () {
        Route::get('/kas-dashboard', function (Request $request) {
            $eschoolId = $request->route('eschool_id');
            return response()->json([
                'message' => 'Bendahara Kas Dashboard',
                'eschool_id' => $eschoolId,
                'user' => $request->user()->name,
                'access' => 'Financial management for this eschool'
            ]);
        });
        
        Route::post('/approve-transaction', function (Request $request) {
            return response()->json([
                'message' => 'Only bendahara can approve transactions',
                'eschool_id' => $request->route('eschool_id')
            ]);
        });
    });
    
    // KOORDINATOR OR BENDAHARA routes (management level)
    Route::prefix('eschool/{eschool_id}/management')->middleware('eschool.role:koordinator|bendahara,{eschool_id}')->group(function () {
        Route::get('/reports', function (Request $request) {
            $eschoolId = $request->route('eschool_id');
            $user = $request->user();
            $userRole = $user->getRoleInEschool($eschoolId);
            
            return response()->json([
                'message' => 'Management Reports',
                'eschool_id' => $eschoolId,
                'user' => $user->name,
                'role_in_eschool' => $userRole->role ?? 'unknown',
                'access' => 'Management level reports for this eschool'
            ]);
        });
        
        Route::get('/members', function (Request $request) {
            return response()->json([
                'message' => 'Member management available to koordinator and bendahara',
                'eschool_id' => $request->route('eschool_id')
            ]);
        });
    });
    
    // Permission-based routes
    Route::prefix('eschool/{eschool_id}/kas')->middleware('eschool.permission:manage_kas|view_all_kas,{eschool_id}')->group(function () {
        Route::get('/records', function (Request $request) {
            $eschoolId = $request->route('eschool_id');
            $user = $request->user();
            $userRole = $user->getRoleInEschool($eschoolId);
            
            return response()->json([
                'message' => 'Kas records access',
                'eschool_id' => $eschoolId,
                'user' => $user->name,
                'role_in_eschool' => $userRole->role ?? 'unknown',
                'permissions' => $userRole ? $userRole->getPermissions() : [],
                'access' => 'User has kas management or view permissions'
            ]);
        });
    });
    
    // MEMBER-level routes (accessible to any role)
    Route::prefix('eschool/{eschool_id}/member')->middleware('eschool.role:member|bendahara|koordinator,{eschool_id}')->group(function () {
        Route::get('/profile', function (Request $request) {
            $eschoolId = $request->route('eschool_id');
            $user = $request->user();
            $userRole = $user->getRoleInEschool($eschoolId);
            
            return response()->json([
                'message' => 'Member profile access',
                'eschool_id' => $eschoolId,
                'user' => $user->name,
                'role_in_eschool' => $userRole->role ?? 'unknown',
                'access' => 'Basic member access to this eschool'
            ]);
        });
        
        Route::get('/attendance', function (Request $request) {
            return response()->json([
                'message' => 'View attendance - available to all eschool members',
                'eschool_id' => $request->route('eschool_id')
            ]);
        });
    });
    
    // Multi-eschool dashboard - shows all user's accessible eschools
    Route::get('/my-eschools', function (Request $request) {
        $user = $request->user();
        $eschoolsData = $user->getEschoolsData();
        
        return response()->json([
            'message' => 'Your accessible eschools',
            'user' => $user->name,
            'total_eschools' => count($eschoolsData),
            'eschools' => $eschoolsData
        ]);
    });
    
    // Test route for checking access without eschool_id parameter
    Route::get('/test-multi-role', function (Request $request) {
        $user = $request->user();
        
        return response()->json([
            'message' => 'Multi-role system test',
            'user' => $user->name,
            'user_type' => $user->role,
            'total_eschool_roles' => $user->eschoolRoles->count(),
            'all_roles' => $user->eschoolRoles->map(function ($role) {
                return [
                    'eschool' => $role->eschool->name,
                    'role' => $role->role,
                    'permissions' => $role->getPermissions()
                ];
            })
        ]);
    });
});
