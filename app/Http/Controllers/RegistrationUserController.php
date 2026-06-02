<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Package;
use App\Models\Setting;
use App\Models\Bonus;
use App\Models\wallettopup;
use App\Models\userpackage;
use App\Models\Withdrawal;
use App\Models\Transaction;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Mail;

class RegistrationUserController extends Controller
{
  



public function showUserStep($step, $referrer = null)
{
    $registrationData = Session::get('registration', []);

    // Step 1: Handle referrer
    if ($step == 1 && $referrer && empty($registrationData['upline_ref'])) {
        $registrationData['upline_ref'] = $referrer;
        Session::put('registration', $registrationData);
    }

    // Step 2: Assign upline_username
    if ($step == 2 && empty($registrationData['upline_username']) && !empty($registrationData['upline_ref'])) {
        $registrationData['upline_username'] = $registrationData['upline_ref'];
        Session::put('registration', $registrationData);
    }

    // Step 4: Fetch bank list from Paystack
      $banks = [];
    if ($step == 4) {
       // $response = Http::withToken(env('PAYSTACK_SECRET_KEY'))
           // ->get('https://api.paystack.co/bank', ['country' => 'nigeria']);

            $response = Http::withOptions(['verify' => false])
    ->withToken(env('PAYSTACK_SECRET_KEY'))
    ->get('https://api.paystack.co/bank', ['country' => 'nigeria']);


        $banks = $response->json()['data'] ?? [];
    }

    return view("user.downline.step{$step}", [
        'data' => $registrationData,
        'banks' => $banks,
    ]);
}

 


public function postUserStep(Request $request, $step)
{
    $registration = Session::get('registration', []);

    switch ($step) {

        /* ================= STEP 1 ================= */
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

            Session::put('registration', array_merge($registration, $validated));
            return redirect()->route('user.downline.step', 2);


        /* ================= STEP 2 ================= */
      case 2:
    $validated = $request->validate([
        'upline_username'  => 'required|exists:users,username',
        'sponsor_username' => 'nullable|string',
    ]);

  //  Check upline status
$uplineUser = User::where('username', $validated['upline_username'])->first();
if ($uplineUser && !in_array($uplineUser->status, ['active', 'pending'])) {
    return back()->withErrors([
        'upline_username' => 'Username found but is inactive. Only active or pending members can be an upline.',
    ])->withInput();
}

//  Check sponsor status (only if provided)
if (!empty($validated['sponsor_username'])) {
    $sponsorUser = User::where('username', $validated['sponsor_username'])->first();
    if ($sponsorUser && (!in_array($sponsorUser->status, ['active', 'pending']) || $sponsorUser->is_muted == 1)) {
        return back()->withErrors([
            'sponsor_username' => 'Username found but is inactive or muted. Only active or pending members can sponsor a user.',
        ])->withInput();
    }
}



    Session::put('registration', array_merge($registration, $validated));
    return redirect()->route('user.downline.step', 3);



        /* ================= STEP 3 ================= */
        case 3:
            $validated = $request->validate([
                'kin_name'    => 'nullable|string',
                'kin_phone'   => 'nullable|string',
                'kin_email'   => 'nullable|email',
                'kin_address' => 'nullable|string',
            ]);

            Session::put('registration', array_merge($registration, $validated));
            return redirect()->route('user.downline.step', 4);


        /* ================= STEP 4 ================= */
        case 4:
            $validated = $request->validate([
                'bank_code'    => 'required|string',
                'bank_name'    => 'required|string',
                'account_no'   => 'required|string',
                'account_name' => 'required|string',
            ]);

            Session::put('registration', array_merge($registration, $validated));
            return redirect()->route('user.downline.step', 5);


        /* ================= STEP 5 ================= */
    case 5:
    $validated = $request->validate([
        'username' => 'required|string|unique:users,username',
        'password' => 'required|string|confirmed',
    ]);

    $finalData = array_merge($registration, $validated);

    // If sponsor_username is empty, use upline_username
    $sponsorUsername = !empty($finalData['sponsor_username'])
        ? $finalData['sponsor_username']
        : $finalData['upline_username'];

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
    'status'           => 'inactive',
    'payment_status'   => 'pending',
    'password'         => Hash::make($finalData['password']),
    'kin_name'         => $finalData['kin_name'] ?? null,
    'kin_phone'        => $finalData['kin_phone'] ?? null,
    'kin_email'        => $finalData['kin_email'] ?? null,
    'kin_address'      => $finalData['kin_address'] ?? null,
    'bank_name'        => $finalData['bank_name'],
    'account_no'       => $finalData['account_no'],
    'account_name'     => $finalData['account_name'],
    'bank_code'        => $finalData['bank_code'],
    'upline_username'  => $finalData['upline_username'],
    'sponsor_username' => !empty($finalData['sponsor_username'])
                            ? $finalData['sponsor_username']
                            : $finalData['upline_username'],
    'userreg_id'       => Auth::id(), // numeric ID of the creator
]);


    $user->assignRole('user');

    Session::forget('registration');

    return redirect()->route('user.userreg_paymentpage')
        ->with('success', 'Registration saved. Please complete payment.');



    }
}

protected function confirmPayment(User $user, string $paymentReference = null)
{
    // Update user status and generate transaction pin
    $user->update([
        'payment_status'   => 'approved',
        'status'           => 'pending',
        'transaction_pin'  => self::generateTransactionPin(),
        'user_rank'        => 'starter',
        'payment_reference'=> $paymentReference,
    ]);

    // --- Binary Placement Logic ---
    $uplineUser  = User::where('username', $user->upline_username)->first();
    $sponsorUser = User::where('username', $user->sponsor_username)->first();

    if (!$uplineUser || !$sponsorUser) {
        return; // fail-safe: no placement if upline/sponsor missing
    }

    //  Placement must be based on uplineUser
    $leftOccupied  = User::where('parent_id', $uplineUser->id)->where('position','left')->exists();
    $rightOccupied = User::where('parent_id', $uplineUser->id)->where('position','right')->exists();

    if ($leftOccupied && $rightOccupied) {
        // Auto spillover: find next available leg starting from uplineUser
        $placementParent = $this->findAvailableSponsor($uplineUser);
    } else {
        // Manual placement: respect chosen upline
        $placementParent = $uplineUser;
    }

    //  Recalculate position under placementParent
    $leftOccupiedPlacement  = User::where('parent_id', $placementParent->id)->where('position','left')->exists();
    $rightOccupiedPlacement = User::where('parent_id', $placementParent->id)->where('position','right')->exists();

    $position = !$leftOccupiedPlacement ? 'left' : (!$rightOccupiedPlacement ? 'right' : null);

    $user->update([
        'parent_id' => $placementParent->id,   //  placement under upline
        'sponsor_id'=> $sponsorUser->id,       //  referral sponsor
        'position'  => $position,
    ]);

    // --- Bonuses ---
    \App\Services\BonusService::distribute($user);
}



 


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




protected function updateBinaryBonus(User $upline)
{
    if (!$upline) return;

    $leftCount  = User::where('upline_username', $upline->username)->where('position', 'left')->count();
    $rightCount = User::where('upline_username', $upline->username)->where('position', 'right')->count();

    if ($leftCount > 0 && $rightCount > 0) {
        $binaryBonus = setting('binary_bonus', 500);
        $upline->increment('withdraw_wallet_balance', $binaryBonus);
    }
}




 

public function showPaymentPage()
{
    $authId = Auth::id();

    // Fetch only pending users created by the current authenticated user
    $pendingUsers = User::where('status', 'inactive')
        ->where('payment_status', 'pending')
        ->where('userreg_id', $authId) // only show users registered by this sponsor/upline
        ->latest()
        ->get();

    return view('user.userreg_paymentpage', compact('pendingUsers'));
}




public function payPendingUser($id)
{
    // Fetch the pending user record
    $user = User::where('id', $id)
        ->where('status', 'inactive')
        ->where('payment_status', 'pending')
        ->firstOrFail();

    // Show the payment choice view
    return view('user.userreg_pay', compact('user'));
}



private static function generateTransactionPin(): string
{
    $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    return substr(str_shuffle(str_repeat($characters, 6)), 0, 6);
}


public function payWithWallet(Request $request, $id)
{
    $request->validate([
        'transaction_pin' => 'required',
    ]);

    $authUser = Auth::user();
    $amountToPay = setting('registration_fee', 5000);

    if ($request->transaction_pin !== $authUser->transaction_pin) {
        return back()->withErrors(['transaction_pin' => 'Invalid transaction PIN.']);
    }

    if ($authUser->deposit_wallet_balance < $amountToPay) {
        return back()->with('error', 'Insufficient wallet balance.');
    }

    $authUser->decrement('deposit_wallet_balance', $amountToPay);

    $user = User::findOrFail($id);

    $user->update([
        'payment_status'  => 'approved',
        'status'          => 'pending', // activate user
        'transaction_pin' => self::generateTransactionPin(),
        'user_rank'       => 'starter',
    ]);

    // Trigger MLM bonuses and binary placement
    $this->confirmPayment($user);

    return redirect()->route('user.dashboard')->with('success', 'Payment successful via wallet.');
}

public function verifyuserPaystackPayment(Request $request, $id)
{
    $reference = $request->query('reference');

    if (!$reference) {
        return redirect()->route('user.paymentPage')->with('error', 'No payment reference found.');
    }

    $response = Http::withOptions([
        'verify' => storage_path('cacert.pem'),
    ])->withToken(env('PAYSTACK_SECRET_KEY'))
      ->get("https://api.paystack.co/transaction/verify/{$reference}");

    if ($response->successful() && $response['data']['status'] === 'success') {
        //  Fetch the user being registered, not the logged-in user
        $user = User::findOrFail($id);

        // Create transaction record
        $transaction = Transaction::create([
            'user_id'        => $user->id,
            'type'           => 'registration',
            'method'         => 'paystack',
            'status'         => 'approved',
            'transaction_no' => $reference,
        ]);

        // Activate user (same as wallet flow)
        $user->update([
            'payment_status'  => 'approved',
            'status'          => 'pending', // activate user
            'transaction_pin' => self::generateTransactionPin(),
            'user_rank'       => 'starter',
        ]);

        // Trigger MLM bonuses and binary placement
        $this->confirmPayment($user, $reference);

        // Notify user
        $data = ['user' => $user];
        Mail::send(['html' => 'emails.registration_approved'], ['data' => $data], function($message) use ($user) {
            $message->to($user->email);
            $message->subject("Your Registration Payment is Approved");
        });

        // Redirect back to user dashboard
        return redirect()->route('user.dashboard')->with('success', 'Payment successful via Paystack.');
    }

    return redirect()->route('user.paymentPage')->with('error', 'Payment verification failed.');
}















 


}
