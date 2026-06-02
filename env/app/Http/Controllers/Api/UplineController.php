<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;

class UplineController extends Controller
{
public function checkUpline(Request $request)
{
    $username = $request->input('username');

    $user = User::where('username', $username)->first();

    if (!$user) {
        return response()->json(['exists' => false]);
    }

    // Example checks for left/right occupied, adjust to your schema
    $leftOccupied = User::where('upline_username', $username)->where('position', 'left')->exists();
    $rightOccupied = User::where('upline_username', $username)->where('position', 'right')->exists();

    return response()->json([
        'exists' => true,
        'first_name' => $user->first_name,
        'last_name' => $user->last_name,
        'left_occupied' => $leftOccupied,
        'right_occupied' => $rightOccupied,
    ]);
}



    public function checkUplineUsername(Request $request)
{
    $username = $request->input('username');

    try {
        $uplineUser = User::where('username', $username)->first();

        if (!$uplineUser) {
            return response()->json([
                'exists' => false,
                'message' => 'Upline username does not exist.'
            ], 404);
        }

        // Check if left and right positions are occupied
        $leftOccupied = User::where('upline_username', $username)->where('position', 'left')->exists();
        $rightOccupied = User::where('upline_username', $username)->where('position', 'right')->exists();
        $isFull = $leftOccupied && $rightOccupied;

        return response()->json([
            'exists' => true,
            'is_full' => $isFull,
            'first_name' => $uplineUser->first_name,
            'last_name' => $uplineUser->last_name,
        ]);
    } catch (\Exception $e) {
        // Log error if needed
        return response()->json([
            'error' => 'Error checking upline username.',
            'message' => $e->getMessage(),
        ], 500);
    }
}

}
