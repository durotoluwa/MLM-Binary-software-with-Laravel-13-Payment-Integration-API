<?php

namespace App\Http\Controllers\user;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\product_order; 

class UserproductCOntroller extends Controller
{
 
    
    

public function userproductPending()
{
    $user = auth()->user();

    $pendingproduct = product_order::where('status', 'pending')
        ->where('user_id', $user->id)
        ->with(['user', 'product', 'items']) // load items too
        ->orderBy('created_at', 'desc')
        ->get();

    return view('user.pendingproductorder', compact('pendingproduct'));
}


}