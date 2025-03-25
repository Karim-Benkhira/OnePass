<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckIpAccess
{
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();
        $ip = $request->ip();

        if ($user->blacklistedIps()->where('ip_address', $ip)->exists()) {
            return response()->json([
                'message' => 'Access denied from this IP address'
            ], 403);
        }

        if ($user->whitelistedIps()->count() > 0) {
            if (!$user->whitelistedIps()->where('ip_address', $ip)->exists()) {
                return response()->json([
                    'message' => 'IP not in whitelist'
                ], 403);
            }
        }

        return $next($request);
    }
} 