<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class V1ApiAuth
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
        if (Auth::guard('api')->check()) {
            Auth::shouldUse('api');
            return $next($request);
        }

        return response()->json([
            'ok' => false,
            'data' => null,
            'meta' => null,
            'error' => [
                'code' => 'UNAUTHORIZED',
                'message' => 'Missing or invalid access token',
                'fields' => null
            ]
        ], 401);
    }
}
