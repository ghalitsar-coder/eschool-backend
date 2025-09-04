<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Illuminate\Support\Facades\Cookie;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');
        \Log::info('🔐 LOGIN ATTEMPT', ['email' => $credentials['email']]);

        if (!$token = auth()->attempt($credentials)) {
            \Log::error('❌ LOGIN FAILED - Invalid credentials', ['email' => $credentials['email']]);
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        $user = auth()->user();
        \Log::info('✅ AUTH SUCCESS', ['user_id' => $user->id, 'user_email' => $user->email]);

        // NEW MULTI-ROLE LOGIC
        // Get all eschools where user has any role
        $eschoolsData = $user->getEschoolsData();
        \Log::info('📚 ESCHOOLS DATA', ['eschools_data' => $eschoolsData]);
        
        // For backward compatibility, determine primary eschool_id
        $primaryEschoolId = null;
        $primaryRole = null;
        
        if (!empty($eschoolsData)) {
            // Priority: koordinator > bendahara > member
            $koordinatorEschool = collect($eschoolsData)->firstWhere('role_in_eschool', 'koordinator');
            $bendaharaEschool = collect($eschoolsData)->firstWhere('role_in_eschool', 'bendahara');
            $memberEschool = collect($eschoolsData)->first(); // First available if no higher role
            
            \Log::info('🔍 ROLE SEARCH', [
                'koordinator_eschool' => $koordinatorEschool,
                'bendahara_eschool' => $bendaharaEschool,
                'member_eschool' => $memberEschool
            ]);
            
            if ($koordinatorEschool) {
                $primaryEschoolId = $koordinatorEschool['eschool_id'];
                $primaryRole = 'koordinator';
            } elseif ($bendaharaEschool) {
                $primaryEschoolId = $bendaharaEschool['eschool_id'];
                $primaryRole = 'bendahara';
            } elseif ($memberEschool) {
                $primaryEschoolId = $memberEschool['eschool_id'];
                $primaryRole = $memberEschool['role_in_eschool'];
            }
        }
        
        \Log::info('👑 PRIMARY ROLE DETERMINED', [
            'primary_eschool_id' => $primaryEschoolId,
            'primary_role' => $primaryRole
        ]);
        
        // If user has no roles in any eschool, they should not be able to login to this system
        if (!$primaryEschoolId) {
            \Log::error('❌ NO ROLES ASSIGNED', ['user_id' => $user->id]);
            return response()->json([
                'message' => 'User has no assigned roles in any eschool. Please contact administrator.'
            ], 403);
        }
        
        // \Log::info("Primary eschoolID: " . $primaryEschoolId . ", Role: " . $primaryRole);
        
        // Generate access token with short TTL
        $accessToken = JWTAuth::fromUser($user);
        \Log::info('🔑 ACCESS TOKEN GENERATED', [
            'token_length' => strlen($accessToken),
            'token_preview' => substr($accessToken, 0, 50) . '...'
        ]);
        
        // Generate refresh token with longer TTL
        // Create custom claims for refresh token
        $refreshClaims = [
            'sub' => $user->id,
            'iat' => now()->timestamp,
            'exp' => now()->addMinutes(config('jwt.refresh_ttl'))->timestamp,
            'type' => 'refresh' // Mark as refresh token
        ];
        
        $refreshToken = JWTAuth::getJWTProvider()->encode($refreshClaims);
        \Log::info('🔄 REFRESH TOKEN GENERATED', [
            'refresh_token_length' => strlen($refreshToken),
            'refresh_token_preview' => substr($refreshToken, 0, 50) . '...',
            'refresh_claims' => $refreshClaims
        ]);

        // ENHANCED USER DATA with pure multi-role support
        $userData = [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'base_role' => $user->base_role, // Authentication role (user, admin, super_admin)
            'eschool_id' => $primaryEschoolId, // Primary eschool for backward compatibility
            'primary_role' => $primaryRole, // Primary role in primary eschool
            
            // NEW: Multi-role data
            'eschools' => $eschoolsData, // All eschools with roles and permissions
            'total_eschools' => count($eschoolsData),
            'available_roles' => array_unique(array_column($eschoolsData, 'role_in_eschool')),
            
            // For backward compatibility with frontend
            'role' => $primaryRole, // Primary role for legacy support
            'school_id' => !empty($eschoolsData) ? $eschoolsData[0]['school_id'] : null, // First school_id for legacy
        ];
        
        \Log::info('👤 USER DATA PREPARED', [
            'user_data' => $userData,
            'jwt_ttl_minutes' => (int) config('jwt.ttl'),
            'jwt_refresh_ttl_minutes' => (int) config('jwt.refresh_ttl')
        ]);
 
 

        $response = response()->json([
            'data' => [
                'user' => $userData,
                'access_token' => $accessToken, // Also include in JSON for debugging
                'refresh_token' => $refreshToken, // Also include in JSON for debugging
            ],
            'message' => 'Login successful',
            'success' => true,
            'expires_in' => (int) config('jwt.ttl') * 60 // in seconds
        ]);
        
        \Log::info('📦 RESPONSE DATA PREPARED', [
            'includes_access_token_in_json' => true,
            'includes_refresh_token_in_json' => true,
            'expires_in_seconds' => (int) config('jwt.ttl') * 60
        ]);

        // Set access token cookie (short-lived)
        $response->cookie(
            'token',                    // name
            $accessToken,               // JWT access token
            (int) config('jwt.ttl'),    // TTL in minutes
            '/',                        // path
            null,                       // domain
            false,                      // secure (set to true in production with HTTPS)
            true,                       // httpOnly
            false,                      // raw
            'lax'                       // sameSite
        );
        
        \Log::info('🍪 ACCESS TOKEN COOKIE SET', [
            'cookie_name' => 'token',
            'ttl_minutes' => (int) config('jwt.ttl'),
            'path' => '/',
            'httpOnly' => true,
            'sameSite' => 'lax'
        ]);

        // Set refresh token cookie (long-lived)
        $response->cookie(
            'refresh_token',            // name
            $refreshToken,              // JWT refresh token
            (int) config('jwt.refresh_ttl'),  // Refresh TTL (7 days)
            '/',                        // path
            null,                       // domain
            false,                      // secure (set to true in production with HTTPS)
            true,                       // httpOnly
            false,                      // raw
            'lax'                       // sameSite
        );
        
        \Log::info('🍪 REFRESH TOKEN COOKIE SET', [
            'cookie_name' => 'refresh_token',
            'ttl_minutes' => (int) config('jwt.refresh_ttl'),
            'path' => '/',
            'httpOnly' => true,
            'sameSite' => 'lax'
        ]);

        \Log::info('✅ LOGIN COMPLETE', [
            'user_id' => $user->id,
            'primary_role' => $primaryRole,
            'cookies_set' => ['token', 'refresh_token'],
            'response_ready' => true
        ]);

        return $response;
    }

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6',
            'base_role' => 'required|string|in:user,admin,super_admin'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'base_role' => $request->base_role,
        ]);

        return response()->json([
            'data' => $user,
            'message' => 'User registered successfully. Admin needs to assign eschool roles.'
        ], 201);
    }

    public function logout(Request $request)
    {
        try {
            // Invalidate the current token
            JWTAuth::invalidate(JWTAuth::getToken());
        } catch (JWTException $e) {
            // Token was already invalid
        }

        $response = response()->json(['message' => 'Successfully logged out']);

        // Force delete cookies using Cookie facade
        Cookie::queue(Cookie::forget('token'));
        Cookie::queue(Cookie::forget('refresh_token'));
        
        // Also set expired cookies as backup
        $response->cookie('token', '', -1, '/', null, false, true, false, 'lax');
        $response->cookie('refresh_token', '', -1, '/', null, false, true, false, 'lax');
        
        return $response;
    }

    public function refresh(Request $request)
    {
        try {
            // Get refresh token from cookie
            $refreshToken = $request->cookie('refresh_token');
            
            if (!$refreshToken) {
                return response()->json(['message' => 'Refresh token not found'], 401);
            }

            // Decode and validate refresh token
            try {
                $payload = JWTAuth::getJWTProvider()->decode($refreshToken);
                
                // Check if it's a refresh token
                if (!isset($payload['type']) || $payload['type'] !== 'refresh') {
                    return response()->json(['message' => 'Invalid refresh token type'], 401);
                }
                
                // Check if token is expired
                if ($payload['exp'] < now()->timestamp) {
                    return response()->json(['message' => 'Refresh token expired'], 401);
                }
                
                // Get user from token
                $user = User::find($payload['sub']);
                if (!$user) {
                    return response()->json(['message' => 'User not found'], 401);
                }
                
            } catch (\Exception $e) {
                return response()->json(['message' => 'Invalid refresh token'], 401);
            }

            // Generate new access token
            $newAccessToken = JWTAuth::fromUser($user);
            
            // Generate new refresh token
            $refreshClaims = [
                'sub' => $user->id,
                'iat' => now()->timestamp,
                'exp' => now()->addMinutes(config('jwt.refresh_ttl'))->timestamp,
                'type' => 'refresh'
            ];
            
            $newRefreshToken = JWTAuth::getJWTProvider()->encode($refreshClaims);

            // MULTI-ROLE LOGIC for refresh - aligned with new schema
            // Get all eschools where user has any role
            $eschoolsData = $user->getEschoolsData();
            
            // For backward compatibility, determine primary eschool_id
            $primaryEschoolId = null;
            $primaryRole = null;
            
            if (!empty($eschoolsData)) {
                // Priority: koordinator > bendahara > member
                $koordinatorEschool = collect($eschoolsData)->firstWhere('role_in_eschool', 'koordinator');
                $bendaharaEschool = collect($eschoolsData)->firstWhere('role_in_eschool', 'bendahara');
                $memberEschool = collect($eschoolsData)->first(); // First available if no higher role
                
                if ($koordinatorEschool) {
                    $primaryEschoolId = $koordinatorEschool['eschool_id'];
                    $primaryRole = 'koordinator';
                } elseif ($bendaharaEschool) {
                    $primaryEschoolId = $bendaharaEschool['eschool_id'];
                    $primaryRole = 'bendahara';
                } elseif ($memberEschool) {
                    $primaryEschoolId = $memberEschool['eschool_id'];
                    $primaryRole = $memberEschool['role_in_eschool'];
                }
            }
            
            // FALLBACK to old logic if no roles found in new system
            if (!$primaryEschoolId) {
                if ($user->isKoordinator()) {
                    $primaryEschoolId = $user->coordinatedEschool?->id;
                    $primaryRole = 'koordinator';
                } elseif ($user->isBendahara()) {
                    $primaryEschoolId = $user->treasurerEschool?->id;
                    $primaryRole = 'bendahara';
                } elseif ($user->isSiswa()) {
                    $primaryEschoolId = $user->member?->eschool_id;
                    $primaryRole = 'member';
                } elseif ($user->isStaff()) {
                    $primaryEschoolId = $user->eschool?->id;
                    $primaryRole = 'staff';
                }
            }

            // ENHANCED USER DATA with multi-role support for refresh
            $userData = [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'base_role' => $user->base_role, // New field for base role
                'is_system_admin' => $user->is_system_admin, // New field for system admin status
                'school_id' => $user->school_id,
                'eschool_id' => $primaryEschoolId, // Primary eschool for backward compatibility
                'primary_role' => $primaryRole, // Primary role in primary eschool
                
                // NEW: Multi-role data aligned with schema baru
                'eschool_roles' => $eschoolsData, // All eschools with roles and permissions
                'total_eschools' => count($eschoolsData),
                'available_roles' => array_unique(array_column($eschoolsData, 'role_in_eschool')),
            ];

            $response = response()->json([
                'success' => true, // Menyelaraskan dengan format response baru
                'data' => [
                    'user' => $userData,
                ],
                'message' => 'Token refreshed successfully',
                'expires_in' => (int) config('jwt.ttl') * 60
            ]);

            // Set new access token cookie
            $response->cookie(
                'token',
                $newAccessToken,
                (int) config('jwt.ttl'),
                '/',
                null,
                false,
                true,
                false,
                'lax'
            );

            // Set new refresh token cookie
            $response->cookie(
                'refresh_token',
                $newRefreshToken,
                (int) config('jwt.refresh_ttl'),
                '/',
                null,
                false,
                true,
                false,
                'lax'
            );
 
            return $response;

        } catch (\Exception $e) {
            return response()->json([
                'success' => false, // Menyelaraskan dengan format response baru
                'message' => 'Token refresh failed',
                'error' => $e->getMessage()
            ], 401);
        }
    }
}