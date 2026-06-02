<?php

namespace App\Http\Controllers\SuperAdmin;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Mail;

class RegistrationAdminController extends Controller
{
   

public function checkUpline(Request $request)
{
    $username = $request->username;
    $user = User::where('username', $username)->first();

    if (!$user) {
        return response()->json(['exists' => false]);
    }

    $leftOccupied = User::where('parent_id', $user->id)->where('position', 'left')->exists();
    $rightOccupied = User::where('parent_id', $user->id)->where('position', 'right')->exists();

    return response()->json([
        'exists' => true,
        'user' => [
            'first_name' => $user->first_name,
            'last_name' => $user->last_name
        ],
        'leftOccupied' => $leftOccupied,
        'rightOccupied' => $rightOccupied
    ]);
}





 
}
