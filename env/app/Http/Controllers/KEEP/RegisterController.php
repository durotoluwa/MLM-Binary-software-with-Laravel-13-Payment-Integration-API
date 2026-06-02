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

    return view("auth.register.step{$step}", [
        'data' => $registrationData,
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
            // Custom validation logic for step 2
            $request->validate([
                'upline_username' => ['required', 'exists:users,username'],
                // sponsor_username validation via closure below
                'sponsor_username' => [
                    function ($attribute, $value, $fail) use ($request) {
                        $uplineUsername = $request->input('upline_username');
                        $uplineUser = User::where('username', $uplineUsername)->first();

                        if (!$uplineUser) {
                            $fail('Upline username does not exist.');
                            return;
                        }

                        // Check if left and right positions are occupied under this upline
                        $leftOccupied = User::where('parent_id', $uplineUser->id)->where('position', 'left')->exists();
                        $rightOccupied = User::where('parent_id', $uplineUser->id)->where('position', 'right')->exists();
                        $isFull = $leftOccupied && $rightOccupied;

                        if ($isFull) {
                            // Sponsor username is required if upline fully occupied
                            if (empty($value)) {
                                $fail('Sponsor username is required because the upline user is fully occupied.');
                                return;
                            }

                            // Validate sponsor_username exists
                            if (!User::where('username', $value)->exists()) {
                                $fail('Sponsor username does not exist.');
                            }
                        }
                    }
                ],
            ]);

            // After validation, assign the validated fields:
            $validated = $request->only('upline_username', 'sponsor_username');
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
    $validated = $request->validate([
        'bank_code' => 'required|string',
        'bank_name' => 'required|string',
        'account_no' => 'required|string',
        'account_name' => 'required|string',
    ]);
    break;


    case 5:
    $validated = $request->validate([
        'username' => 'required|string|unique:users,username',
        'password' => 'required|string|confirmed',
    ]);

    $finalData = array_merge($registration, $validated);

    // Get upline user by username
    $uplineUser = User::where('username', $finalData['upline_username'])->first();
    if (!$uplineUser) {
        return back()->withErrors(['upline_username' => 'Invalid upline username'])->withInput();
    }

       $sponsorId = null;

    if (!empty($finalData['sponsor_username'])) {
        $sponsorUser = User::where('username', $finalData['sponsor_username'])->first();
        if (!$sponsorUser) {
            return back()->withErrors(['sponsor_username' => 'Invalid sponsor username'])->withInput();
        }
        $sponsorId = $sponsorUser->id;
    } else {
        // If no sponsor_username provided, fallback to upline user as sponsor
        $sponsorId = $uplineUser->id;
    }


    // Determine position and parent_id
    if ($sponsorId) {
        // Sponsor registration, position can be null or you can decide a default
        $parentId = $sponsorId;  // Usually sponsor_id as parent for sponsor registrations
        $position = null;        // Sponsors don’t have binary positions
    } else {
        // User registering under upline, so find available position (left or right)
        $leftOccupied = User::where('parent_id', $uplineUser->id)->where('position', 'left')->exists();
        $rightOccupied = User::where('parent_id', $uplineUser->id)->where('position', 'right')->exists();

        if ($leftOccupied && $rightOccupied) {
            return back()->withErrors(['upline_username' => 'Selected upline already has both legs occupied.'])->withInput();
        }

        $parentId = $uplineUser->id;
        $position = $leftOccupied ? 'right' : 'left';
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
         'status' => 'pending',
        'password' => Hash::make($finalData['password']),
        'kin_name' => $finalData['kin_name'],
        'kin_phone' => $finalData['kin_phone'],
        'kin_email' => $finalData['kin_email'],
        'kin_address' => $finalData['kin_address'],
        'bank_name' => $finalData['bank_name'],
        'account_no' => $finalData['account_no'],
        'account_name' => $finalData['account_name'],
        'bank_code' => $finalData['bank_code'],
        
    'upline_username' => $finalData['upline_username'],
    'sponsor_username' => $finalData['sponsor_username'] ?? null,
    ]);

    $user->assignRole('user');


$data = [
    'user' => $user,
    'first_name' => $finalData['first_name'],
    'last_name' => $finalData['last_name'],
    'upline' => $uplineUser ?? null,
    'sponsor' => $sponsorUser ?? null,
];

// Send email to registered user
Mail::send(['html' => 'emails.welcome_user'], ['data' => $data], function($message) use ($user) {
    $message->to($user->email);
    $message->subject("Welcome to DLT Health Plus, {$user->first_name}!");
});

// Send email to admin
Mail::send(['html' => 'emails.notify_admin'], ['data' => $data], function($message) {
    $message->to('devophostsolutions@gmail.com'); 
    $message->subject("New User Registration Alert");
});

// Notify upline if exists
if (!empty($data['upline'])) {
    Mail::send(['html' => 'emails.notify_upline'], ['data' => $data], function($message) use ($data) {
        $message->to($data['upline']->email);
        $message->subject("Your Downline Has Registered");
    });
}

// Notify sponsor if exists
if (!empty($data['sponsor'])) {
    Mail::send(['html' => 'emails.notify_sponsor'], ['data' => $data], function($message) use ($data) {
        $message->to($data['sponsor']->email);
        $message->subject("Your Referral Has Registered");
    });
}

// Login using web guard explicitly
Auth::guard('web')->login($user);

// Forget session
Session::forget('registration');

// Confirm if logged in (debugging)
if (!Auth::check()) {
    abort(500, 'Login failed'); // Optional debug check
}

// Redirect
if ($user->hasRole('superadmin')) {
    return redirect()->route('superadmin.dashboard');
} elseif ($user->hasRole('admin')) {
    return redirect()->route('admin.dashboard');
} elseif ($user->hasRole('accounting')) {
    return redirect()->route('accounting.dashboard');
} else {
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

