<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class ImpersonateMiddleware
{
    public function handle($request, Closure $next)
    {
        // If impersonating, bypass role restrictions for "superadmin"
        if (session()->has('impersonate')) {
            return $next($request);
        }

        return $next($request);
    }
}
