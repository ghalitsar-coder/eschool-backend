<?php

namespace App\Http\Middleware;

use Closure;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class JWTFromCookie
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // Log untuk debugging
        // Log::info('JWTFromCookie middleware called', [
        //     'has_cookie' => $request->hasCookie('token'),
        //     'cookie_names' => array_keys($request->cookies->all())
        // ]);
        
        // Cek apakah token ada di cookie
        if ($request->hasCookie('token')) {
            $token = $request->cookie('token');
            
            // Log::info('Token found in cookie', ['token' => substr($token, 0, 20) . '...']);
            
            try {
                // Set token ke JWTAuth
                JWTAuth::setToken($token);
                
                // Coba autentikasi user
                $user = JWTAuth::authenticate();
                
                // Log::info('User authenticated', ['user_id' => $user ? $user->id : null]);
                
                // Set user ke request jika user ditemukan
                if ($user) {
                    $request->setUserResolver(function () use ($user) {
                        return $user;
                    });
                }
            } catch (TokenExpiredException $e) {
                // Token expired
                Log::warning('Token expired', ['exception' => $e->getMessage()]);
                return response()->json(['error' => 'Token has expired'], 401);
            } catch (TokenInvalidException $e) {
                // Token invalid
                Log::warning('Token invalid', ['exception' => $e->getMessage()]);
                return response()->json(['error' => 'Token is invalid'], 401);
            } catch (JWTException $e) {
                // Token not found
                Log::warning('Token not found', ['exception' => $e->getMessage()]);
                return response()->json(['error' => 'Token not found'], 401);
            }
        } else {
            Log::info('No token cookie found');
        }
        
        return $next($request);
    }
}