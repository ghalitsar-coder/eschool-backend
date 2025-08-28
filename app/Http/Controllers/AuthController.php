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


      // Tentukan eschool_id berdasarkan role
    if ($user->isKoordinator()) {
        \Log::info("I AM KOORDINATOR`");
        $eschoolId = $user->coordinatedEschool?->id;
    } elseif ($user->isBendahara()) {
        $eschoolId = $user->treasurerEschool?->id;
    } elseif ($user->isSiswa()) {
        $eschoolId = $user->member?->eschool_id;
    } elseif ($user->isStaff()) {
        \Log::info("I AM staff`");
        // misalnya staff juga punya relasi langsung ke eschool
        $eschoolId = $user->eschool?->id;
    }
   \Log::info("eschoolID: " . $eschoolId);

        
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

        $userData = [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
            'eschool_id' => $eschoolId,
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

            if ($user->isKoordinator()) {
                $eschoolId = $user->coordinatedEschool?->id;
            } elseif ($user->isBendahara()) {
                $eschoolId = $user->treasurerEschool?->id;
            } elseif ($user->isSiswa()) {
                $eschoolId = $user->member?->eschool_id;
            } elseif ($user->isStaff()) {
                // misalnya staff juga punya relasi langsung ke eschool
                $eschoolId = $user->eschool?->id;
            }

            $userData = [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
            'eschool_id' => $eschoolId,
        ];
 

            $response = response()->json([
                'data' => [
                    'user' => $userData,
                    
                ],
                'message' => 'refresh success',
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
                'message' => 'Token refresh failed',
                'error' => $e->getMessage()
            ], 401);
        }
    }
}