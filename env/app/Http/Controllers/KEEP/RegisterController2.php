<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class RegisterController extends Controller
{

 


    public function showRegistrationForm()
{
    return view('auth.register');
}


public function checkUpline(Request $request)
{
    $username = $request->input('username');
    $user = User::where('username', $username)->first();

    if (!$user) {
        return response()->json(['exists' => false], 200);
    }

    $leftOccupied = User::where('upline_username', $username)->where('position', 'left')->exists();
    $rightOccupied = User::where('upline_username', $username)->where('position', 'right')->exists();

    return response()->json([
        'exists' => true,
        'user' => [
            'username' => $user->username,
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
        ],
        'leftOccupied' => $leftOccupied,
        'rightOccupied' => $rightOccupied,
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

    // If it's Step 1 and there's a referrer, store it temporarily
    if ($step == 1 && $referrer && empty($registrationData['upline_ref'])) {
        // Save the referrer in session as a temporary value
        $registrationData['upline_ref'] = $referrer;
        Session::put('registration', $registrationData);
    }

    // If it's Step 2 and the session contains upline_ref, assign it to upline_username
    if ($step == 2 && empty($registrationData['upline_username']) && !empty($registrationData['upline_ref'])) {
        $registrationData['upline_username'] = $registrationData['upline_ref'];
        Session::put('registration', $registrationData);
    }

    return view("auth.register.step{$step}", ['data' => $registrationData]);
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
                'kin_name' => 'required|string',
                'kin_phone' => 'required|string',
                'kin_email' => 'required|email',
                'kin_address' => 'required|string',
            ]);
            break;

        case 4:
            $validated = $request->validate([
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
        'username' => $finalData['username'],
        'parent_id' => $parentId,
        'sponsor_id' => $sponsorId,
        'position' => $position,
        'password' => Hash::make($finalData['password']),
        'kin_name' => $finalData['kin_name'],
        'kin_phone' => $finalData['kin_phone'],
        'kin_email' => $finalData['kin_email'],
        'kin_address' => $finalData['kin_address'],
        'bank_name' => $finalData['bank_name'],
        'account_no' => $finalData['account_no'],
        'account_name' => $finalData['account_name'],
    ]);

    $user->assignRole('user');
   
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
    return redirect()->route('user.dashboard');
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

