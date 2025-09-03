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

        if (!$token = auth()->attempt($credentials)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        $user = auth()->user();

        // NEW MULTI-ROLE LOGIC
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
                \Log::info("FALLBACK: I AM KOORDINATOR");
                $primaryEschoolId = $user->coordinatedEschool?->id;
                $primaryRole = 'koordinator';
            } elseif ($user->isBendahara()) {
                $primaryEschoolId = $user->treasurerEschool?->id;
                $primaryRole = 'bendahara';
            } elseif ($user->isSiswa()) {
                $primaryEschoolId = $user->member?->eschool_id;
                $primaryRole = 'member';
            } elseif ($user->isStaff()) {
                \Log::info("FALLBACK: I AM staff");
                $primaryEschoolId = $user->eschool?->id;
                $primaryRole = 'staff';
            }
        }
        
        // \Log::info("Primary eschoolID: " . $primaryEschoolId . ", Role: " . $primaryRole);
        
        // Generate access token with short TTL
        $accessToken = JWTAuth::fromUser($user);
        
        // Generate refresh token with longer TTL
        // Create custom claims for refresh token
        $refreshClaims = [
            'sub' => $user->id,
            'iat' => now()->timestamp,
            'exp' => now()->addMinutes(config('jwt.refresh_ttl'))->timestamp,
            'type' => 'refresh' // Mark as refresh token
        ];
        
        $refreshToken = JWTAuth::getJWTProvider()->encode($refreshClaims);

        // ENHANCED USER DATA with multi-role support
        $userData = [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role, // Keep original role for compatibility
            'school_id' => $user->school_id,
            'eschool_id' => $primaryEschoolId, // Primary eschool for backward compatibility
            'primary_role' => $primaryRole, // Primary role in primary eschool
            
            // NEW: Multi-role data
            'eschools' => $eschoolsData, // All eschools with roles and permissions
            'total_eschools' => count($eschoolsData),
            'available_roles' => array_unique(array_column($eschoolsData, 'role_in_eschool')),
        ];
 
 

        $response = response()->json([
            'data' => [
                'user' => $userData,
            ],
            'message' => 'Login successful',
            'expires_in' => (int) config('jwt.ttl') * 60 // in seconds
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

        return $response;
    }

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6',
            'role' => 'required|string|in:siswa,bendahara,koordinator,staff'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
        ]);

        return response()->json([
            'data' => $user,
            'message' => 'User registered successfully'
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