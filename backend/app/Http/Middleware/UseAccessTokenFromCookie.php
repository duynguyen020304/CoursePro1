<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class UseAccessTokenFromCookie
{
    /**
     * Mirror the access token cookie into the Authorization header so
     * Sanctum's token guard can keep protecting API routes.
     */
    public function handle(Request $request, Closure $next)
    {
        if (!$request->bearerToken()) {
            $accessToken = $request->cookie('access_token');

            if (is_string($accessToken) && $accessToken !== '') {
                $request->headers->set('Authorization', 'Bearer '.urldecode($accessToken));
            }
        }

        return $next($request);
    }
}
