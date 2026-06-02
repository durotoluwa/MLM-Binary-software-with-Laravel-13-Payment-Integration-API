<?php

namespace App\Http\Controllers;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


 

class LoginController extends Controller
{
     // Show login form
    public function showLoginForm()
    {
        return view('auth.login');
    }

       public function mainLoginForm()
    {
        return view('auth.login');
    }

 public function loginPost(Request $request)
    {
        $credentials = $request->validate([
            'username' => ['required'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials, $request->remember)) {
            $request->session()->regenerate();

            // Redirect based on role
            if (Auth::user()->hasRole('superadmin')) {
                return redirect()->route('superadmin.dashboard');
            } elseif (Auth::user()->hasRole('admin')) {
                return redirect()->route('admin.dashboard');
            } elseif (Auth::user()->hasRole('accounting')) {
                return redirect()->route('accounting.dashboard');
            } else {
                return redirect()->route('user.dashboard');
            }
        }

        return back()->withErrors([
            'username' => 'Invalid credentials provided.',
        ]);
    }

    // Handle logout
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
