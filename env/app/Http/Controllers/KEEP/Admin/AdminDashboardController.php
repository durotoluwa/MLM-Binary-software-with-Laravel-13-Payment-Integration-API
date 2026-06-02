<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminDashboardController extends Controller
{
  public function index()
{
    $user = Auth::user(); // or Auth::guard('web')->user();

    if (!$user) {
        abort(403, 'Unauthorized access.');
    }

    return view('admin.dashboard', compact('user'));
}
}
