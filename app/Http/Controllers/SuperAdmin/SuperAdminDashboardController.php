<?php

namespace App\Http\Controllers\superadmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Spatie\Permission\PermissionRegistrar;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\URL;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use App\Models\User;
use App\Models\Transaction;
use App\Models\wallettopup;
use App\Models\Withdrawal;
use App\Models\userpackage;
use App\Models\product_order;
use App\Models\Package;
use App\Models\incentives;
use App\Models\payout;


use App\Http\Controllers\User\UserPackageController;
class SuperAdminDashboardController extends Controller
{

public function impersonate($id)
{
    $user = User::findOrFail($id);

    // Create a temporary token valid for 5 minutes
    $token = Str::random(60);
    $user->impersonation_token = hash('sha256', $token);
    $user->impersonation_token_expires_at = now()->addMinutes(5);
    $user->save();

    // Create signed URL
    $url = URL::temporarySignedRoute(
        'impersonate.login',
        now()->addMinutes(5),
        ['user' => $user->id, 'token' => $token]
    );

    //  Pass the URL to the view
    return view('superadmin.impersonate', [
        'impersonateUrl' => $url,
        'user' => $user
    ]);
}

public function loginAsUser(Request $request, $userId, $token)
{
    $user = User::findOrFail($userId);

    if (
        !$user->impersonation_token ||
        !hash_equals($user->impersonation_token, hash('sha256', $token)) ||
        now()->greaterThan($user->impersonation_token_expires_at)
    ) {
        abort(403, 'Invalid or expired token.');
    }

    // Clear token (single use)
    $user->impersonation_token = null;
    $user->impersonation_token_expires_at = null;
    $user->save();

    // Login as user
    Auth::login($user);

    return redirect()->route('user.dashboard')
        ->with('success', 'You are logged in as ' . $user->username);
}


public function stopImpersonate()
{
    if (Session::has('impersonate_user')) {
        $adminId = Session::get('impersonate_user');
        Session::forget('impersonate_user');

        // Log back in as superadmin
        Auth::loginUsingId($adminId);

        //  Refresh Spatie roles & permissions cache
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        Auth::user()->load('roles', 'permissions');

        return redirect()->route('superadmin.dashboard')
            ->with('success', 'You are back as Superadmin.');
    }

    return redirect()->route('superadmin.dashboard')
        ->with('error', 'Not impersonating anyone.');
}


 



public function checkUser(Request $request)
{
    $username = $request->input('username');
    $user = User::where('username', $username)->first();

    if (!$user) {
        return response()->json(['exists' => false]);
    }

    // Check legs if this is an upline
    $leftOccupied  = User::where('parent_id', $user->id)->where('position','left')->exists();
    $rightOccupied = User::where('parent_id', $user->id)->where('position','right')->exists();

    return response()->json([
        'exists' => true,
        'status' => $user->status,
        'user'   => [
            'first_name' => $user->first_name,
            'last_name'  => $user->last_name,
        ],
        'legs_full' => $leftOccupied && $rightOccupied,
    ]);
}



public function index()
{
    $user = Auth::user();

    if (!$user) {
        abort(403, 'Unauthorized access.');
    }

    // Counts
    $memberCount = User::where('status', 'active')->count();
    $inactivememberCount = User::where('status', 'inactive')->count();
        $pendingmemberCount = User::where('status', 'pending')->count();
    $mutedmemberCount = User::where('is_muted', '1')->count();
    $pendingregCount = Transaction::where('status', 'pending')->where('type', 'registration')->count();
    $wallettopupCount = wallettopup::where('status', 'pending')->count();
    $withdrawalCount = payout::where('status', 'pending')->count();
    $packageCount = userpackage::where('status', 'pending')->count();
 $productCount = product_order::where('status', 'pending')->count();
    // Monthly approved transaction totals
    $year = now()->year;

    $transactions = userpackage::selectRaw('MONTH(created_at) as month, SUM(amount_paid) as total')
        ->where('status', 'approved')
        ->whereYear('created_at', $year)
        ->groupByRaw('MONTH(created_at)')
        ->pluck('total', 'month');

            $transactionsproduct = product_order::selectRaw('MONTH(created_at) as month, SUM(amount) as total')
        ->where('status', 'approved')
        ->whereYear('created_at', $year)
        ->groupByRaw('MONTH(created_at)')
        ->pluck('total', 'month');

    $monthlyTotals = [];
    foreach (range(1, 12) as $month) {
        $monthlyTotals[] = $transactions->get($month, 0);
    }

       $monthlyproductTotals = [];
    foreach (range(1, 12) as $month) {
        $monthlyproductTotals[] = $transactionsproduct->get($month, 0);
    }

    return view('superadmin.dashboard', compact(
        'user',
        'memberCount',
        'inactivememberCount',
        'mutedmemberCount',
        'pendingmemberCount',
        'pendingregCount',
        'wallettopupCount',
        'withdrawalCount',
        'packageCount',
        'monthlyTotals',
        'monthlyproductTotals',
        'year',
        'productCount'
    ));
}



public function monthlyApprovedTransactions()
{
    $year = Carbon::now()->year;

    // Get approved transactions grouped by month
    $transactions = Transaction::selectRaw('MONTH(created_at) as month, SUM(amount) as total')
        ->where('status', 'approved')
        ->whereYear('created_at', $year)
        ->groupByRaw('MONTH(created_at)')
        ->pluck('total', 'month');

    // Prepare all months with zero defaults
    $monthlyData = [];
    foreach (range(1, 12) as $month) {
        $monthlyData[] = $transactions->get($month, 0);
    }

    return view('admin.charts.transactions', [
        'monthlyTotals' => $monthlyData,
        'year' => $year,
    ]);
}



public function addUsers()
{
    
    return view('superadmin.member.addMembers');
}

public function editUsersprofile($id)
{
    $user = User::findOrFail($id);
    return view('superadmin.member.edituserprofile',compact('user'));
}



public function updateuserprofile(Request $request, $id)
{
    $user = User::findOrFail($id);

    // Validate input
    $validated = $request->validate([
        
        'username' => 'nullable|string|max:255',
        'first_name' => 'nullable|string|max:255',
        'last_name' => 'nullable|string|max:255',
        'phone' => 'nullable|string|max:20',
        'email' => 'nullable|email|max:255',
        'address' => 'nullable|string|max:255',
        'state' => 'nullable|string|max:100',
        'country' => 'nullable|string|max:100',
        'bank_name' => 'nullable|string|max:100',
        'account_no' => 'nullable|string|max:20',
        'account_name' => 'nullable|string|max:100',
        'password' => 'nullable|string|min:6|confirmed',
    ]);

    // Update user fields
        $user->first_name = $validated['first_name'] ?? $user->first_name;
    $user->first_name = $validated['first_name'] ?? $user->first_name;
    $user->last_name = $validated['last_name'] ?? $user->last_name;
    $user->phone = $validated['phone'] ?? $user->phone;
    $user->email = $validated['email'] ?? $user->email;
    $user->address = $validated['address'] ?? $user->address;
    $user->state = $validated['state'] ?? $user->state;
 $user->city = $validated['city'] ?? $user->city;
    $user->country = $validated['country'] ?? $user->country;
    $user->bank_name = $validated['bank_name'] ?? $user->bank_name;
    $user->account_no = $validated['account_no'] ?? $user->account_no;
    $user->account_name = $validated['account_name'] ?? $user->account_name;

    // Update password if provided
    if (!empty($validated['password'])) {
        $user->password = Hash::make($validated['password']);
    }

    $user->save();

    return redirect()->back()->with('success', 'Profile updated successfully.');

}



public function userList()
{

         $users = User::latest()->get();

    foreach ($users as $user) {
        $token = Str::random(60);
        $user->impersonation_token = hash('sha256', $token);
        $user->impersonation_token_expires_at = now()->addMinutes(5);
        $user->save();

        // Attach a temporary signed URL to each user
        $user->impersonateUrl = URL::temporarySignedRoute(
            'impersonate.login',
            now()->addMinutes(5),
            ['user' => $user->id, 'token' => $token]
        );
    }

    return view('superadmin.member.allMembers', compact('users'));
}


public function impersonateLogin(Request $request, User $user, $token)
{
    if (!hash_equals($user->impersonation_token, hash('sha256', $token))) {
        abort(403, 'Invalid impersonation token.');
    }

    if (now()->greaterThan($user->impersonation_token_expires_at)) {
        abort(403, 'Impersonation token expired.');
    }

    // Clear token
    $user->impersonation_token = null;
    $user->impersonation_token_expires_at = null;
    $user->save();

    // Instead of default guard, use impersonate guard
    Auth::guard('impersonate')->login($user);

    return redirect()->route('user.dashboard')->with('success', 'You are now logged in as ' . $user->username);
}



public function toggleMute(Request $request, $id)
{
    $user = \App\Models\User::findOrFail($id);
    $user->is_muted = $request->input('is_muted') ? 1 : 0;
    $user->save();

    return redirect()->back()->with('success', 'Mute status updated successfully.');
}



public function activeuserList()
{
    if (!auth()->user()->can('view active members')) {
        abort(403, 'Unauthorized action.');
    }

   $users = User::
             where('status', 'active')
             ->orderBy('created_at', 'desc')
             ->get();

    return view('superadmin.member.activeMembers', compact('users'));
}

public function inactiveuserList()
{
    if (!auth()->user()->can('view inactive members')) {
        abort(403, 'Unauthorized action.');
    }

   $users = User::
            where('status', 'inactive')
             ->orderBy('created_at', 'desc')
             ->get();
    return view('superadmin.member.inactiveMembers', compact('users'));
}

public function pendinguserList()
{
    if (!auth()->user()->can('view pending members')) {
        abort(403, 'Unauthorized action.');
    }

   $users = User::
             where('status', 'pending')
             ->orderBy('created_at', 'desc')
             ->get();
    return view('superadmin.member.pendingMembers', compact('users'));
}


public function mutedUsersList()
{
    if (!auth()->user()->can('view muted members')) {
        abort(403, 'Unauthorized action.');
    }

    $users = User::where('is_muted', true)->latest()->get();
    return view('superadmin.member.mutedMembers', compact('users'));
}




public function take($id)
{
    $user = \App\Models\User::findOrFail($id);

    // Save the original admin ID in session
    session(['impersonator_id' => auth()->id()]);

    // Login as the target user
    auth()->login($user);

    return redirect('/user/dashboard')->with('success', "You are now impersonating {$user->name}");
}



public function leave()
{
    if (!$this->manager->isImpersonating()) {
        abort(403);
    }

    $this->manager->leave();

    return redirect()->route('superadmin.dashboard')
        ->with('success', 'You are back as SuperAdmin.');
}





public function editSponsor($id)
{
    $user = User::findOrFail($id);
    return view('superadmin.member.edit-sponsor', compact('user'));
}

public function updateSponsor(Request $request, $id)
{
    $request->validate([
        'sponsor_id' => 'required|string|exists:users,username',
        'parent_id' => 'required|string|exists:users,username',
        'position' => 'nullable|in:left,right',
    ]);

    $user = User::findOrFail($id);

    // Prevent self-reference
    if ($request->sponsor_id === $user->username || $request->parent_id === $user->username) {
        return back()->with('error', 'A user cannot be their own sponsor or upline.');
    }

    // Get sponsor and upline records
    $sponsor = User::where('username', $request->sponsor_id)->first();
    $upline = User::where('username', $request->parent_id)->first();

    if (!$sponsor || !$upline) {
        return back()->with('error', 'Sponsor or upline not found.');
    }

    // Update user’s genealogy data
    $user->update([
        'sponsor_id' => $sponsor->id,
        'parent_id' => $upline->id,
        'position' => $request->position,
    ]);

    return redirect()->route('superadmin.member.allMembers')
                     ->with('success', 'Sponsor and upline updated successfully.');
}


public function validateUsername(Request $request)
{
    $username = $request->query('username');

    $user = \App\Models\User::where('username', $username)->first();

    if ($user) {
        return response()->json([
            'exists' => true,
            'id' => $user->id,
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
        ]);
    }

    return response()->json(['exists' => false]);
}


 public function buyPackage()
{
    $users = \App\Models\User::role('user')->get();
    $packages = \App\Models\Package::all();

    return view('superadmin.package.buy_package', compact('users', 'packages'));
}




public function getUserPackageInfo($id)
{
    try {
        $user = User::findOrFail($id);

        // Define package hierarchy
        $packageOrder = ['standard', 'basic', 'classic', 'premium', 'executive', 'vip'];

        // Get last user package
        $lastUserPackage = userpackage::with('package')
            ->where('user_id', $user->id)
            ->latest('id')
            ->first();

        $lastPackage = null;
        $lastPackageRank = -1;

        if ($lastUserPackage && $lastUserPackage->package) {
            $lastPackage = [
                'name' => $lastUserPackage->package->packageName,
                'price' => $lastUserPackage->package->price,
                'status' => ucfirst($lastUserPackage->status),
                'date' => $lastUserPackage->created_at->format('d M, Y'),
            ];

            $lastPackageRank = array_search(
                strtolower($lastUserPackage->package->packageName),
                $packageOrder
            );
        }

        // Get only higher packages
        $availablePackages = Package::whereIn('packageName', array_slice($packageOrder, $lastPackageRank + 1))
            ->orderByRaw("FIELD(packageName, '" . implode("','", $packageOrder) . "')")
            ->get(['id', 'packageName', 'price']);

        return response()->json([
            'status' => 'success',
            'last_package' => $lastPackage,
            'available_packages' => $availablePackages,
        ]);

    } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
        return response()->json([
            'status' => 'error',
            'message' => 'User not found.',
        ], 404);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => 'Error fetching package info',
            'error' => $e->getMessage(),
        ], 500);
    }
}


public function buyPackageStore(Request $request)
{
    $request->validate([
        'user_id' => 'required|exists:users,id',
        'package_id' => 'required|exists:package,id',
    ]);

    try {
        $user = User::findOrFail($request->user_id);
        $package = Package::findOrFail($request->package_id);

        // Instantiate UserPackageController to use its bonus functions
        $userPackageController = new UserPackageController();

        DB::transaction(function () use ($user, $package, $userPackageController) {
            $ctpToAdd = $package->cpts ?? 0;

            // Update user’s package and CPT totals
            $user->total_ctp += $ctpToAdd;
            $user->p_c_cpts += $ctpToAdd;
            $user->current_p_c_cpts += $ctpToAdd;
            $user->current_c_cpts += $ctpToAdd;
            $user->user_plan = $package->packageName;
            $user->status = 'active';
            if (empty($user->user_rank)) {
                $user->user_rank = 'Regular';
            }
            $user->save();

            // Get previous package (if any)
            $previousPackage = userpackage::where('user_id', $user->id)
                ->latest('id')
                ->value('package_id');

            // Record this purchase
            $userPackage = userpackage::create([
                'user_id'             => $user->id,
                'package_id'          => $package->id,
                'previous_package_id' => $previousPackage,
                'amount_paid'         => $package->price,
                'payment_method'      => 'admin_manual',
                'status'              => 'approved',
                'package_order_status'=> 'approved',
                'approved_by'         => auth()->id(),
                'is_approved'         => true,
                'approved_at'         => now(),
            ]);

            //  MLM bonus triggers
            \addCtpToUplines($user->id, $ctpToAdd);
            $userPackageController->handleMatchingBonus($user, $ctpToAdd);
            $userPackageController->payReferralBonus($user, $package->price, false, $previousPackage);
        });

        return redirect()
            ->back()
            ->with('success', "Package '{$package->packageName}' successfully assigned to {$user->username}.");

    } catch (\Exception $e) {
        return redirect()
            ->back()
            ->with('error', 'An unexpected error occurred: ' . $e->getMessage());
    }
}

 public function adminorderPage()
{
 
 
 return view('superadmin.package.order_product');
}


public function incentivesHistory()
{
    $incentives = DB::table('incentives')
        ->join('users', 'incentives.user_id', '=', 'users.id')
        ->select(
            'incentives.id',
            'users.username as username',
            'users.first_name as first_name',
            'users.last_name as last_name',
            'incentives.rank',
            'incentives.status',
            'incentives.achieved_at',
            'incentives.created_at'
        )
        ->orderBy('incentives.achieved_at', 'desc')
        ->paginate(20); // optional pagination

    return view('superadmin.incentive_settings.incentive_list', compact('incentives'));
}


}
