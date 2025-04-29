<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class TokenMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $token = $request->bearerToken();
        if(!$token || !cache()->has("token:$token"))
        {
            return response()->json(['message' => 'Token invalido'], 401);
        }
        return $next($request);
    }
}
