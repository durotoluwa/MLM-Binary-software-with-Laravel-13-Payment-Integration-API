<?php

namespace App\Http\Controllers;

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




 public function showAdminStep($step, $referrer = null)
{
    $registrationData = Session::get('registration', []);
    $banks = [];

    if ($step == 1 && $referrer && empty($registrationData['upline_ref'])) {
        $registrationData['upline_ref'] = $referrer;
        Session::put('registration', $registrationData);
    }

    if ($step == 2 && empty($registrationData['upline_username']) && !empty($registrationData['upline_ref'])) {
        $registrationData['upline_username'] = $registrationData['upline_ref'];
        Session::put('registration', $registrationData);
    }

    if ($step == 4) {
        $response = Http::withOptions(['verify' => false])
            ->withToken(env('PAYSTACK_SECRET_KEY'))
            ->get('https://api.paystack.co/bank', ['country' => 'nigeria']);
        $banks = $response->json()['data'] ?? [];
    }

    return view("superadmin.registeruser.step{$step}", [
        'data'  => $registrationData,
        'banks' => $banks,
    ]);
}



 public function postAdminStep(Request $request, $step)
{
    $registration = Session::get('registration', []);
    $validated = [];

    switch ($step) {
        case 1:
            $validated = $request->validate([
                'first_name' => 'required|string',
                'last_name'  => 'required|string',
                'email'      => 'required|email|unique:users,email',
                'phone'      => 'required|string|unique:users,phone',
                'state'      => 'required|string',
                'city'       => 'required|string',
                'country'    => 'required|string',
                'address'    => 'required|string',
            ]);
            break;

        case 2:
            $request->validate([
                'upline_username' => ['required', 'exists:users,username'],
                'sponsor_username' => [
                    function ($attribute, $value, $fail) use ($request) {
                        $uplineUser = User::where('username', $request->upline_username)->first();
                        if (!$uplineUser) {
                            $fail('Upline username does not exist.');
                            return;
                        }

                        $leftOccupied  = User::where('parent_id', $uplineUser->id)->where('position','left')->exists();
                        $rightOccupied = User::where('parent_id', $uplineUser->id)->where('position','right')->exists();
                        $isFull = $leftOccupied && $rightOccupied;

                        if ($isFull && empty($value)) {
                            $fail('Sponsor username is required because the upline user is fully occupied.');
                        }

                        if (!empty($value) && !User::where('username', $value)->exists()) {
                            $fail('Sponsor username does not exist.');
                        }
                    }
                ],
            ]);
            $validated = $request->only('upline_username','sponsor_username');
            break;

        case 3:
            $validated = $request->validate([
                'kin_name'    => 'nullable|string',
                'kin_phone'   => 'nullable|string',
                'kin_email'   => 'nullable|email',
                'kin_address' => 'nullable|string',
            ]);
            break;

        case 4:
            $validated = $request->validate([
                'bank_code'    => 'required|string',
                'bank_name'    => 'required|string',
                'account_no'   => 'required|string',
                'account_name' => 'required|string',
            ]);
            break;
      case 5:
            $validated = $request->validate([
                'username' => 'required|string|unique:users,username',
                'password' => 'required|string|confirmed',
            ]);

            $finalData = array_merge($registration, $validated);

           $uplineUser = User::where('username', $finalData['upline_username'])->first();
if (!$uplineUser) {
    return back()->withErrors(['upline_username' => 'Invalid upline username'])->withInput();
}

$sponsorUser = !empty($finalData['sponsor_username'])
    ? User::where('username', $finalData['sponsor_username'])->first()
    : $uplineUser;

if (!$sponsorUser) {
    return back()->withErrors(['sponsor_username' => 'Invalid sponsor username'])->withInput();
}

// ✅ Placement must be based on uplineUser, not sponsorUser
$leftOccupied  = User::where('parent_id', $uplineUser->id)->where('position','left')->exists();
$rightOccupied = User::where('parent_id', $uplineUser->id)->where('position','right')->exists();

if ($leftOccupied && $rightOccupied) {
    // Auto spillover: find next available leg starting from uplineUser
    $placementParent = $this->findAvailableSponsor($uplineUser);
} else {
    // Manual placement: respect chosen upline
    $placementParent = $uplineUser;
}

// ✅ Position is recalculated under placementParent
$leftOccupiedPlacement  = User::where('parent_id', $placementParent->id)->where('position','left')->exists();
$rightOccupiedPlacement = User::where('parent_id', $placementParent->id)->where('position','right')->exists();

$position = !$leftOccupiedPlacement ? 'left' : (!$rightOccupiedPlacement ? 'right' : null);

// Create user with correct parent and sponsor
$user = User::create([
    'first_name'       => $finalData['first_name'],
    'last_name'        => $finalData['last_name'],
    'email'            => $finalData['email'],
    'phone'            => $finalData['phone'],
    'state'            => $finalData['state'],
    'city'             => $finalData['city'],
    'address'          => $finalData['address'],
    'country'          => $finalData['country'],
    'username'         => $finalData['username'],
    'parent_id'        => $placementParent->id,   // ✅ placement under upline
    'sponsor_id'       => $sponsorUser->id,       // ✅ referral sponsor
    'position'         => $position,
    'user_rank'        => 'starter',
    'status'           => 'active',
    'payment_status'   => 'approved',
    'transaction_pin'  => self::generateTransactionPin(),
    'password'         => Hash::make($finalData['password']),
    'kin_name'         => $finalData['kin_name'],
    'kin_phone'        => $finalData['kin_phone'],
    'kin_email'        => $finalData['kin_email'],
    'kin_address'      => $finalData['kin_address'],
    'bank_name'        => $finalData['bank_name'],
    'account_no'       => $finalData['account_no'],
    'account_name'     => $finalData['account_name'],
    'bank_code'        => $finalData['bank_code'],
    'upline_username'  => $placementParent->username,
    'sponsor_username' => $sponsorUser->username,
]);
 
            $user->assignRole('user');

            // ✅ Trigger bonuses immediately
            \App\Services\BonusService::distribute($user);

            Session::forget('registration');

            return redirect()->route('superadmin.member.allMembers')
                ->with('success', 'User registered successfully by admin. Placement, payment approval, and bonuses triggered.');
    }

    Session::put('registration', array_merge($registration, $validated));
    return redirect()->route('superadmin.registermember.step', $step + 1);

}


 
 




/**
 * Recursively find the next available sponsor in the binary tree.
 */
protected function findAvailableSponsor(User $user)
{
    $leftChild = User::where('parent_id', $user->id)->where('position','left')->first();
    if (!$leftChild) {
        return $user; // left leg available
    }

    $rightChild = User::where('parent_id', $user->id)->where('position','right')->first();
    if (!$rightChild) {
        return $user; // right leg available
    }

    $nextSponsor = $this->findAvailableSponsor($leftChild);
    if ($nextSponsor) {
        return $nextSponsor;
    }

    return $this->findAvailableSponsor($rightChild);
}


private static function generateTransactionPin(): string
{
    $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    return substr(str_shuffle(str_repeat($characters, 6)), 0, 6);
}



}
