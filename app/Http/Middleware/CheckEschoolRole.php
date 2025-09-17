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

        // Get the user's eschool roles
        $userEschoolRoles = $request->user()->userEschoolRoles()
            ->whereIn('role', $roles)
            ->get();

        if ($userEschoolRoles->isEmpty()) {
            return response()->json([
                'message' => 'Unauthorized. Required roles for this eschool: ' . implode(', ', $roles)
            ], 403);
        }

        // For now, we'll use the first matching role's eschool_id
        // In a real implementation, you might want to handle multiple eschools differently
        $eschoolId = $userEschoolRoles->first()->eschool_id;

        // Add eschool_id to the request for use in controllers
        $request->attributes->set('eschool_id', $eschoolId);

        return $next($request);
    }
}