<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use App\Models\product_order;

class CheckPendingProductOrder
{
    public function handle($request, Closure $next)
    {
        if (Auth::check()) {
            $hasPendingOrder = product_order::where('status', 'pending')
                ->where('user_id', Auth::id())
                ->exists();

            if ($hasPendingOrder && !$request->is('user/pendingproductorder')) {
                return redirect()->route('user.pendingproductorder')
                    ->with('warning', 'You have a pending product order. Please complete it before continuing.');
            }
        }

        return $next($request);
    }
}
