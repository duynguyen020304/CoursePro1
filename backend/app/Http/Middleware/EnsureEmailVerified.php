<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureEmailVerified
{
    public function handle(Request $request, Closure $next): Response
    {
        $userAccount = $request->user();

        if (!$userAccount || $userAccount->is_verified) {
            return $next($request);
        }

        return response()->json([
            'success' => false,
            'message' => 'Please verify your email before accessing this resource.',
            'data' => [
                'reason' => 'email_unverified',
            ],
        ], 403);
    }
}
