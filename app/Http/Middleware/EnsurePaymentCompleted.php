<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class EnsurePayment
{
    public function handle($request, Closure $next)
    {
        $user = Auth::user();

        // If user is Superadmin or Admin or Accounting, skip payment check
        if ($user->hasRole('superadmin') || $user->hasRole('admin') || $user->hasRole('accounting')) {
            return $next($request);
        }

        // If user is a regular member and hasn't paid
        if ($user->payment_status !== 'approved') {
            return redirect()->route('user.payment.page');
        }

        return $next($request);
    }
}
