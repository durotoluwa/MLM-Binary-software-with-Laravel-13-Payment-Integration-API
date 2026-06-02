<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
public function store(LoginRequest $request): RedirectResponse
{
    // Authenticate user
    $request->authenticate();

    // Regenerate session
    $request->session()->regenerate();

    $user = Auth::user();

    // Redirect based on roles
    if ($user->hasRole('superadmin')) {
        return redirect()->route('superadmin.dashboard');
    } elseif ($user->hasRole('admin')) {
        return redirect()->route('admin.dashboard');
    } elseif ($user->hasRole('accounting')) {
        return redirect()->route('accounting.dashboard');
    } elseif ($user->hasRole('user')) {
        // Check payment status
        if ($user->payment_status === 'approved') {
            return redirect()->route('user.dashboard');
        } else {
            return redirect()->route('user.paymentPage');
        }
    }

    // Default fallback
    return redirect('/');
}




    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
