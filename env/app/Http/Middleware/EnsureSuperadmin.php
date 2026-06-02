<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class EnsureSuperadmin
{
    public function handle($request, Closure $next)
    {
        $user = Auth::user();

        // If this is your superadmin ID, always allow
        if ($user && $user->id === 1) {   // change 1 to your real superadmin id
            return $next($request);
        }

        // Otherwise rely on roles
        if ($user && $user->hasRole('superadmin')) {
            return $next($request);
        }

        abort(403, 'User does not have the right roles.');
    }
}

