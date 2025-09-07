<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\UserEschoolRole;

class CheckEschoolRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        if (!$request->user()) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        // Get eschool ID from route parameter
        $eschoolId = $request->route('eschoolId');
        
        if (!$eschoolId) {
            return response()->json(['message' => 'Eschool ID not provided'], 400);
        }

        // Check if user has any of the specified roles for the given eschool
        $userEschoolRoles = UserEschoolRole::where('user_id', $request->user()->id)
            ->where('eschool_id', $eschoolId)
            ->whereIn('role', $roles)
            ->exists();

        if (!$userEschoolRoles) {
            return response()->json([
                'message' => 'Unauthorized. Required roles for this eschool: ' . implode(', ', $roles)
            ], 403);
        }

        return $next($request);
    }
}