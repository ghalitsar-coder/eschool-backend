<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

class JWTFromCookie
{
    public function handle(Request $request, Closure $next)
    {
        // Get access token from cookie
        $token = $request->cookie('token');
        
        if ($token) {
            // Set token for JWTAuth
            JWTAuth::setToken($token);
            
            try {
                // Try to authenticate user
                $user = JWTAuth::parseToken()->authenticate();
                
                if ($user) {
                    // Set authenticated user
                    auth()->setUser($user);
                }
            } catch (JWTException $e) {
                // Access token expired or invalid
                // Check if we have refresh token
                $refreshToken = $request->cookie('refresh_token');
                
                if ($refreshToken && !$request->is('api/refresh')) {
                    // Don't auto-refresh on refresh endpoint to avoid loops
                    try {
                        JWTAuth::setToken($refreshToken);
                        $user = JWTAuth::user();
                        
                        if ($user) {
                            // Set user but don't generate new token here
                            // Frontend should call /refresh endpoint
                            auth()->setUser($user);
                        }
                    } catch (JWTException $refreshException) {
                        // Both tokens invalid, continue without authentication
                    }
                }
            }
        }
        
        return $next($request);
    }
}