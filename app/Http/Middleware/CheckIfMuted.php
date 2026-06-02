<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckIfMuted
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
 if (auth()->check() && auth()->user()->is_muted) {
        abort(403, 'Your account is muted. You cannot perform this action.');
    }

    return $next($request);
    }

 
}
