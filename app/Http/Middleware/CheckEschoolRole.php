<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

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

        // Get eschool_id from route parameter
        $eschoolId = $request->route('eschool_id');
        
        // If eschool_id is not in route, try to get it from the request
        if (!$eschoolId) {
            $eschoolId = $request->get('eschool_id');
        }

        if (!$eschoolId) {
            return response()->json(['message' => 'Eschool ID is required'], 400);
        }

        // Check if user has any of the required roles in this eschool
        $userHasRole = $request->user()->hasRoleInEschool($eschoolId, $roles);

        if (!$userHasRole) {
            return response()->json([
                'message' => 'Unauthorized. Required roles in this eschool: ' . implode(', ', $roles)
            ], 403);
        }

        return $next($request);
    }
}