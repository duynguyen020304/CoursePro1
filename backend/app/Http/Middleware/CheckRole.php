<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     * @param  string  ...$roles  The roles to check against
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 401);
        }

        if (!$user->role) {
            return response()->json([
                'success' => false,
                'message' => 'User has no role assigned',
            ], 403);
        }

        // Flatten comma-separated roles (Laravel may pass "admin,instructor" as single param)
        $roles = collect($roles)->flatMap(function ($role) {
            return array_map('trim', explode(',', $role));
        })->all();

        // Check if user has any of the required roles
        foreach ($roles as $role) {
            if ($user->hasRole($role)) {
                return $next($request);
            }
        }

        return response()->json([
            'success' => false,
            'message' => 'You do not have the required role to access this resource',
        ], 403);
    }
}
