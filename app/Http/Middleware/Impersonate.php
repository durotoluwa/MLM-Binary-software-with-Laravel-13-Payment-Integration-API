<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class Impersonate
{
    public function handle($request, Closure $next)
    {
        if (session()->has('impersonate_user')) {
            $impersonatedId = session('impersonate_user');
            $impersonatedUser = User::find($impersonatedId);

            if ($impersonatedUser) {
                // Temporarily act as this user
                Auth::onceUsingId($impersonatedId);
            }
        }

        return $next($request);
    }
}
