<?php

namespace App\Http\Controllers\user;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class memberReorderController extends Controller
{
  public function showmemberpackage()
    {
        return view('user.member_reorder');
    }


    public function searchMemberreorder(Request $request)
{
    $validated = $request->validate([
        'member' => 'required|string|exists:users,username',
    ]);

    return redirect()->route('member.reorder.select', ['username' => $validated['member']]);
}
}
