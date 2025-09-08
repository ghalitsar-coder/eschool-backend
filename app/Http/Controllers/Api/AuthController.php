<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\LoginRequest;
use App\Models\User;
use App\Models\Profile;
use App\Models\UserEschoolRole;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

class AuthController extends Controller
{
    /**
     * Register a new user
     *
     * @param RegisterRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(RegisterRequest $request)
    {
        try {
            // Load profile data to include in response
            $profile = Profile::find($request->profile_id);
            
            // Create new user
            $user = User::create([
                'profile_id' => $request->profile_id,
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ]);

            // Load the profile relationship
            $user->load('profile');

            // Generate access token with short TTL
            $token = JWTAuth::fromUser($user);
            
            // Generate refresh token with longer TTL
            // Create custom claims for refresh token
            $refreshClaims = [
                'sub' => $user->id,
                'iat' => now()->timestamp,
                'exp' => now()->addMinutes(config('jwt.refresh_ttl'))->timestamp,
                'type' => 'refresh' // Mark as refresh token
            ];
            
            $refreshToken = JWTAuth::getJWTProvider()->encode($refreshClaims);
            
            // Set refresh token to expire in 2 weeks (default)
            $refreshTokenExpires = config('jwt.refresh_ttl', 20160); // 2 weeks in minutes

            // Get user's roles
            $userRoles = $this->getUserRoles($user);

            // Return response with tokens in cookies
            return response()->json([
                'message' => 'User registered successfully',
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'profile' => $user->profile,
                    'roles' => $userRoles
                ],
                'token_info' => [
                    'type' => 'Bearer',
                    'expires_in' => config('jwt.ttl') * 60 // Convert minutes to seconds
                ]
            ], 201)
            ->cookie('token', $token, config('jwt.ttl'), '/', null, false, true)
            ->cookie('refresh_token', $refreshToken, $refreshTokenExpires, '/', null, false, true);
            
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to register user',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Login user
     *
     * @param LoginRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(LoginRequest $request)
    {
        // Get credentials
        $credentials = $request->only('email', 'password');

        try {
            // Attempt authentication
            if (!$token = JWTAuth::attempt($credentials)) {
                return response()->json([
                    'error' => 'Unauthorized',
                    'message' => 'Invalid email or password'
                ], 401);
            }

            // Get authenticated user with profile
            $user = auth()->user();
            $user->load('profile');
            
            // Generate refresh token with longer TTL
            // Create custom claims for refresh token
            $refreshClaims = [
                'sub' => $user->id,
                'iat' => now()->timestamp,
                'exp' => now()->addMinutes(config('jwt.refresh_ttl'))->timestamp,
                'type' => 'refresh' // Mark as refresh token
            ];
            
            $refreshToken = JWTAuth::getJWTProvider()->encode($refreshClaims);
            
            // Set refresh token to expire in 2 weeks (default)
            $refreshTokenExpires = config('jwt.refresh_ttl', 20160); // 2 weeks in minutes

            // Get user's roles
            $userRoles = $this->getUserRoles($user);

            // Return response with tokens in cookies
            return response()->json([
                'message' => 'User logged in successfully',
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'profile' => $user->profile,
                    'roles' => $userRoles
                ],
                'token_info' => [
                    'type' => 'Bearer',
                    'expires_in' => config('jwt.ttl') * 60 // Convert minutes to seconds
                ]
            ], 200)
            ->cookie('token', $token, config('jwt.ttl'), '/', null, false, true)
            ->cookie('refresh_token', $refreshToken, $refreshTokenExpires, '/', null, false, true);
            
        } catch (JWTException $e) {
            return response()->json([
                'error' => 'Could not create token',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Logout user
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        try {
            // Get current token
            $token = JWTAuth::getToken();
            
            if (!$token) {
                return response()->json([
                    'error' => 'Token not provided',
                    'message' => 'No authentication token found'
                ], 401);
            }
            
            // Logout user and invalidate token
            JWTAuth::invalidate($token);

            // Return response and remove cookies
            return response()->json([
                'message' => 'User logged out successfully'
            ], 200)
            ->withoutCookie('token')
            ->withoutCookie('refresh_token');
            
        } catch (JWTException $e) {
            return response()->json([
                'error' => 'Failed to logout',
                'message' => 'Please try again'
            ], 500);
        }
    }

    /**
     * Refresh token
     *
     * @return \Illuminate\Http\JsonResponse
     */
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

            // Load profile relationship
            $user->load('profile');

            // Get user's roles
            $userRoles = $this->getUserRoles($user);

            $response = response()->json([
                'message' => 'Token refreshed successfully',
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'profile' => $user->profile,
                    'roles' => $userRoles
                ],
                'token_info' => [
                    'type' => 'Bearer',
                    'expires_in' => config('jwt.ttl') * 60
                ]
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

    /**
     * Get authenticated user
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function me()
    {
        try {
            // Get current token
            $token = JWTAuth::getToken();
            
            if (!$token) {
                return response()->json([
                    'error' => 'Token not provided',
                    'message' => 'No authentication token found'
                ], 401);
            }
            
            // Get authenticated user
            $user = JWTAuth::authenticate($token);
            
            if (!$user) {
                return response()->json([
                    'error' => 'User not found',
                    'message' => 'Invalid or expired token'
                ], 404);
            }
            
            // Load profile relationship
            $user->load('profile');
            
            // Get user's roles
            $userRoles = $this->getUserRoles($user);
            
            return response()->json([
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'email_verified_at' => $user->email_verified_at,
                    'profile' => $user->profile,
                    'roles' => $userRoles,
                    'created_at' => $user->created_at,
                    'updated_at' => $user->updated_at
                ]
            ], 200);
            
        } catch (JWTException $e) {
            return response()->json([
                'error' => 'Failed to retrieve user',
                'message' => 'Token may be invalid or expired'
            ], 401);
        }
    }
    
    /**
     * Get user's roles from user_eschool_roles table
     *
     * @param User $user
     * @return array
     */
    private function getUserRoles(User $user)
    {
        // Get all roles for the user
        $userEschoolRoles = UserEschoolRole::where('user_id', $user->id)
            ->with('eschool')
            ->get();
        
        // Transform the roles into the required format
        $roles = [];
        foreach ($userEschoolRoles as $userEschoolRole) {
            $roles[] = [
                'id' => $userEschoolRole->id,
                'role' => $userEschoolRole->role,
                'eschool_id' => $userEschoolRole->eschool_id,
                'eschool_name' => $userEschoolRole->eschool ? $userEschoolRole->eschool->name : null,
                'created_at' => $userEschoolRole->created_at,
                'updated_at' => $userEschoolRole->updated_at
            ];
        }
        
        return $roles;
    }
}
