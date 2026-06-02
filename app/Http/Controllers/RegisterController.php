<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Mail;

class RegisterController extends Controller
{

 


    public function showRegistrationForm()
{
    return view('auth.register');
}

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

 



public function checkSponsor (Request $request)
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







    public function showStep1()
{
    // Redirect to step 1 page (same as step 1 view)
 
     return view("register.step{$step}"); 
}

public function showStep($step, $referrer = null)
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

    // Step 4: Fetch bank list and create recipient
    $banks = [];
    if ($step == 4) {
        // Fetch bank list
        $response = Http::withOptions(['verify' => false])
            ->withToken(env('PAYSTACK_SECRET_KEY'))
            ->get('https://api.paystack.co/bank', ['country' => 'nigeria']);

        $banks = $response->json()['data'] ?? [];

        // If account details are already in session, create recipient
        if (!empty($registrationData['account_name']) &&
            !empty($registrationData['account_no']) &&
            !empty($registrationData['bank_code'])) {

            $recipientResponse = Http::withOptions(['verify' => false])
                ->withToken(env('PAYSTACK_SECRET_KEY'))
                ->post('https://api.paystack.co/transferrecipient', [
                    'type'           => 'nuban',
                    'name'           => $registrationData['account_name'],
                    'account_number' => $registrationData['account_no'],
                    'bank_code'      => $registrationData['bank_code'],
                    'currency'       => 'NGN',
                ]);

            if ($recipientResponse->successful()) {
                $registrationData['recipient_code'] =
                    $recipientResponse->json()['data']['recipient_code'] ?? null;
                Session::put('registration', $registrationData);
            }
        }
    }

    return view("auth.register.step{$step}", [
        'data'  => $registrationData,
        'banks' => $banks,
    ]);
}



public function verifyAccount(Request $request)
{
    $request->validate([
        'account_no' => 'required|string',
        'bank_code' => 'required|string',
    ]);

    //$response = Http::withToken(env('PAYSTACK_SECRET_KEY'))
       // ->get('https://api.paystack.co/bank/resolve', [
       //     'account_number' => $request->account_no,
      //      'bank_code' => $request->bank_code,
      //  ]);
$response = Http::withOptions(['verify' => false])
    ->withToken(env('PAYSTACK_SECRET_KEY'))
    ->get('https://api.paystack.co/bank/resolve', [
        'account_number' => $request->account_no,
        'bank_code' => $request->bank_code,
    ]);



    if ($response->successful() && isset($response['data']['account_name'])) {
        return response()->json([
            'success' => true,
            'account_name' => $response['data']['account_name'],
        ]);
    }

    return response()->json([
        'success' => false,
        'message' => 'Account verification failed.',
    ], 422);
}

  









public function postStep(Request $request, $step)
{
    $registration = Session::get('registration', []);
    $validated = [];

    switch ($step) {

        case 1:
            $validated = $request->validate([
                'first_name' => 'required|string',
                'last_name' => 'required|string',
                'email' => 'required|email|unique:users,email',
                'phone' => 'required|string|unique:users,phone',
                'state' => 'required|string',
                'city' => 'required|string',
                'country' => 'required|string',
                'address' => 'required|string',
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

               // Fail only if upline is explicitly inactive or muted
if (strtolower(trim($uplineUser->status)) === 'inactive' || $uplineUser->is_muted == 1) {
    $fail('Username found but is inactive or muted. An inactive or muted member cannot be an upline.');
    return;
}


                $leftOccupied  = User::where('parent_id', $uplineUser->id)->where('position','left')->exists();
                $rightOccupied = User::where('parent_id', $uplineUser->id)->where('position','right')->exists();
                $isFull = $leftOccupied && $rightOccupied;

                if ($isFull) {
                    if (empty($value)) {
                        $fail('Sponsor username is required because the upline user is fully occupied.');
                        return;
                    }

                    $sponsorUser = User::where('username', $value)->first();
                    if (!$sponsorUser) {
                        $fail('Sponsor username does not exist.');
                        return;
                    }

                    //  Fail only if sponsor is explicitly inactive
                    if (strtolower(trim($sponsorUser->status)) === 'inactive') {
                        $fail('Username found but is inactive. An inactive member cannot sponsor a user.');
                        return;
                    }
                }
            }
        ],
    ]);

    $validated = $request->only('upline_username','sponsor_username');
    break;




        case 3:

            $validated = $request->validate([
                'kin_name' => 'nullable|string',
                'kin_phone' => 'nullable|string',
                'kin_email' => 'nullable|email',
                'kin_address' => 'nullable|string',
            ]);

            break;


case 4:
    // Get current registration data
    $registration = Session::get('registration', []);

    // Validate input
    $request->validate([
        'bank_code'    => 'required|string',
        'account_no'   => 'required|string',
        'account_name' => 'required|string',
    ]);

    // Call Paystack to create transfer recipient
    $recipientResponse = Http::withOptions(['verify' => false])
        ->withToken(env('PAYSTACK_SECRET_KEY'))
        ->post('https://api.paystack.co/transferrecipient', [
            'type'           => 'nuban',
            'name'           => $request->account_name,
            'account_number' => $request->account_no,
            'bank_code'      => $request->bank_code,
            'currency'       => 'NGN',
        ]);

    if ($recipientResponse->successful()) {
        $recipientCode = $recipientResponse->json()['data']['recipient_code'];

        $validated = [
            'bank_name'              => $request->bank_name,
            'bank_code'              => $request->bank_code,
            'account_no'             => $request->account_no,
            'account_name'           => $request->account_name,
            'paystack_recipient_code'=> $recipientCode,
        ];

        Session::put('registration', array_merge($registration, $validated));
    } else {
        return back()->withErrors('Unable to create Paystack recipient. Please try again.');
    }

    return redirect()->route('register.step', 5);


            break;



        case 5:

            $validated = $request->validate([
                'username' => 'required|string|unique:users,username',
                'password' => 'required|string|confirmed',
            ]);

            $finalData = array_merge($registration, $validated);

            $uplineUser = User::where('username', $finalData['upline_username'])->first();

            if (!$uplineUser) {
                return back()->withErrors([
                    'upline_username' => 'Invalid upline username'
                ])->withInput();
            }


            $sponsorUser = null;
            $sponsorId = null;

            if (!empty($finalData['sponsor_username'])) {

                $sponsorUser = User::where('username', $finalData['sponsor_username'])->first();

                if (!$sponsorUser) {
                    return back()->withErrors([
                        'sponsor_username' => 'Invalid sponsor username'
                    ])->withInput();
                }

                $sponsorId = $sponsorUser->id;

            } else {

                $sponsorUser = $uplineUser;
                $sponsorId = $uplineUser->id;
            }



            /*
            |--------------------------------------------------------------------------
            | UPDATED BINARY AUTO PLACEMENT LOGIC
            |--------------------------------------------------------------------------
            | This will automatically find the next available position
            | while keeping the sponsor intact
            */

            $placementRoot = $uplineUser;

            if ($sponsorUser) {
                $placementRoot = $sponsorUser;
            }

            $queue = [$placementRoot];
/*
|--------------------------------------------------------------------------
| AUTO + MANUAL PLACEMENT LOGIC (FIXED)
|--------------------------------------------------------------------------
*/

$parentId = null;
$position = null;

//  Detect MANUAL placement (upline != sponsor)
$isManualPlacement = !empty($finalData['sponsor_username']) 
    && $finalData['upline_username'] !== $finalData['sponsor_username'];

if ($isManualPlacement) {

    // 🔥 MANUAL PLACEMENT (STRICT)
    $leftChild = User::where('parent_id', $uplineUser->id)
        ->where('position', 'left')
        ->exists();

    $rightChild = User::where('parent_id', $uplineUser->id)
        ->where('position', 'right')
        ->exists();

    if (!$leftChild) {
        $parentId = $uplineUser->id;
        $position = 'left';
    } elseif (!$rightChild) {
        $parentId = $uplineUser->id;
        $position = 'right';
    } else {
        return back()->withErrors([
            'upline_username' => 'Selected upline already has both legs occupied.'
        ])->withInput();
    }

} else {

    //   AUTO PLACEMENT (BFS from sponsor or upline)
    $placementRoot = $sponsorUser ?? $uplineUser;

    $queue = [$placementRoot];

    while (!empty($queue)) {

        $current = array_shift($queue);

        $leftChild = User::where('parent_id', $current->id)
            ->where('position', 'left')
            ->first();

        $rightChild = User::where('parent_id', $current->id)
            ->where('position', 'right')
            ->first();

        if (!$leftChild) {
            $parentId = $current->id;
            $position = 'left';
            break;
        }

        if (!$rightChild) {
            $parentId = $current->id;
            $position = 'right';
            break;
        }

        $queue[] = $leftChild;
        $queue[] = $rightChild;
    }
}

// Final safety check
if (!$parentId || !$position) {
    return back()->withErrors([
        'upline_username' => 'No available position found in binary tree.'
    ])->withInput();
}


            $user = User::create([

                'first_name' => $finalData['first_name'],
                'last_name' => $finalData['last_name'],
                'email' => $finalData['email'],
                'phone' => $finalData['phone'],
                'state' => $finalData['state'],
                'city' => $finalData['city'],
                'address' => $finalData['address'],
                'country' => $finalData['country'],

                'username' => $finalData['username'],

                'parent_id' => $parentId,
                'sponsor_id' => $sponsorId,
                'position' => $position,

                'status' => 'inactive',

                'password' => Hash::make($finalData['password']),

                'kin_name' => $finalData['kin_name'],
                'kin_phone' => $finalData['kin_phone'],
                'kin_email' => $finalData['kin_email'],
                'kin_address' => $finalData['kin_address'],

               'bank_name' => $finalData['bank_name'],
    'account_no' => $finalData['account_no'],
    'account_name' => $finalData['account_name'],
    'bank_code' => $finalData['bank_code'],
    'paystack_recipient_code' => $finalData['paystack_recipient_code'],
'upline_username' => $finalData['upline_username'],
'sponsor_username' => $finalData['sponsor_username'] ?? null,

            ]);


            $user->assignRole('user');



            $data = [

                'user' => $user,
                'first_name' => $finalData['first_name'],
                'last_name' => $finalData['last_name'],
                'upline' => $uplineUser,
                'sponsor' => $sponsorUser,
            ];



            Mail::send(['html' => 'emails.welcome_user'], ['data' => $data], function ($message) use ($user) {

                $message->to($user->email);
                $message->subject("Welcome to DLT Health Plus, {$user->first_name}!");

            });



            Mail::send(['html' => 'emails.notify_admin'], ['data' => $data], function ($message) {

                $message->to('devophostsolutions@gmail.com');
                $message->subject("New User Registration Alert");

            });



            if (!empty($data['upline'])) {

                Mail::send(['html' => 'emails.notify_upline'], ['data' => $data], function ($message) use ($data) {

                    $message->to($data['upline']->email);
                    $message->subject("Your Downline Has Registered");

                });
            }



            if (!empty($data['sponsor'])) {

                Mail::send(['html' => 'emails.notify_sponsor'], ['data' => $data], function ($message) use ($data) {

                    $message->to($data['sponsor']->email);
                    $message->subject("Your Referral Has Registered");

                });
            }



            Auth::guard('web')->login($user);

            Session::forget('registration');



            if ($user->hasRole('superadmin')) {
                return redirect()->route('superadmin.dashboard');
            }

            elseif ($user->hasRole('admin')) {
                return redirect()->route('admin.dashboard');
            }

            elseif ($user->hasRole('accounting')) {
                return redirect()->route('accounting.dashboard');
            }

            else {
                return redirect()->route('user.paymentPage');
            }
    }


    Session::put('registration', array_merge($registration, $validated));

    return redirect()->route('register.step', $step + 1);
}









    public function success()
    {
        return view('auth.register.success');
    }
}

