<?php

namespace Packages\EzKnowledgeBase\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Packages\EzKnowledgeBase\Helpers\ApiErrorResponse;

class ApiAuthenticate
{
    public function handle(Request $request, Closure $next)
    {
        // 1. Check static API key from .env
        $apiKey = config('kb.api.key');

        if ($apiKey && hash_equals($apiKey, $request->header('X-KB-API-Key') ?? '')) {
            return $next($request);
        }

        // 2. Fall back to Sanctum bearer token
        if ($request->bearerToken()) {
            $guard = Auth::guard('sanctum');

            if ($guard->check()) {
                return $next($request);
            }
        }

        return ApiErrorResponse::make('Unauthenticated.', 'unauthenticated', 401);
    }
}
