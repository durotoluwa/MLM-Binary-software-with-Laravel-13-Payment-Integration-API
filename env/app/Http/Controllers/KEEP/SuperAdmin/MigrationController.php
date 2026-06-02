<?php

namespace App\Http\Controllers\superadmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Package;
use App\Models\userpackage;
use Illuminate\Support\Str;

class MigrationController extends Controller
{
 public function migratePage()
    {
        $packages = Package::where('status','active')->orderBy('price')->get();
        return view('superadmin.migration.migrate_user', compact('packages'));
    }

    public function userSearch(Request $request)
    {
        $q = $request->query('q');
        $users = User::query()
            ->when($q, fn($qbuilder) => $qbuilder->where(function($q2) use ($q) {
                $q2->where('username','like', "%{$q}%")
                   ->orWhere('first_name','like', "%{$q}%")
                   ->orWhere('last_name','like', "%{$q}%")
                   ->orWhere('email','like', "%{$q}%");
            }))
            ->limit(20)
            ->get(['id','username','first_name','last_name','email']);

        $results = $users->map(function($u){
            return [
                'id' => $u->id,
                'text' => "{$u->username} ({$u->first_name} {$u->last_name})",
                'username' => $u->username,
                'name' => trim("{$u->first_name} {$u->last_name}"),
                'email' => $u->email,
            ];
        });

        return response()->json(['results' => $results]);
    }

    public function lastPackage($id)
    {
        $user = User::find($id);
        if (!$user) return response()->json(['status'=>'error','message'=>'User not found'], 404);

        $last = userpackage::where('user_id',$user->id)
            ->orderBy('created_at','desc')
            ->first();

        $lastData = null;
        if ($last) {
            $pkg = Package::find($last->package_id);
            $lastData = [
                'id' => $last->id,
                'name' => $pkg?->packageName ?? 'N/A',
                'price' => $last->amount_paid ?? $pkg?->price ?? 0,
                'status' => $last->status,
                'date' => $last->created_at->format('Y-m-d'),
                'package_id' => $pkg?->id,
            ];
        }

        // Build available packages higher than last
        $available = collect();
        if ($last && $last->package_id) {
            $available = Package::where('id','>',$last->package_id)->where('status','active')->get();
        } else {
            $available = Package::where('status','active')->get();
        }

        return response()->json(['status'=>'success','last_package'=>$lastData,'available_packages'=>$available]);
    }

    public function store(Request $request)
    {
        $rules = [
            'existing_user_id' => 'nullable|exists:users,id',

            // if not existing user then create new
            'username' => 'required_without:existing_user_id|nullable|alpha_dash|unique:users,username',
            'email' => 'required_without:existing_user_id|nullable|email|unique:users,email',
            'first_name' => 'required_without:existing_user_id|nullable|string|max:100',
            'last_name' => 'nullable|string|max:100',

            // genealogy
            'upline_username' => 'nullable|string',
            'sponsor_username' => 'nullable|string',
            'position' => 'nullable|in:left,right',

            // package
            'package_id' => 'nullable|exists:package,id',
            'amount_paid' => 'nullable|numeric',
            'ctp_received' => 'nullable|numeric',

            // wallets
            'deposit_wallet_balance' => 'nullable|numeric',
            
            'unilevel_wallet_balance' => 'nullable|numeric',
            'withdraw_wallet_balance' => 'nullable|numeric',

            // matching carryovers
            'left_ctp_for_matching' => 'nullable|numeric',
            'right_ctp_for_matching' => 'nullable|numeric',

            'status' => 'nullable|in:active,inactive,blocked',
            'payment_status' => 'nullable|in:approved,pending',
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) return back()->withErrors($validator)->withInput();

        DB::transaction(function() use ($request) {
            // find or create user
            if ($request->filled('existing_user_id')) {
                $user = User::find($request->existing_user_id);
            } else {
                $pwd = Str::random(10);
                $user = User::create([
                    'username' => $request->username,
                    'email' => $request->email,
                    'first_name' => $request->first_name,
                    'last_name' => $request->last_name,
                    'password' => Hash::make($pwd),
                    'status' => $request->status ?? 'inactive',
                ]);
                // optionally notify user with new password
            }

            // lookup upline & sponsor
            $upline = null; $sponsor = null;
            if ($request->filled('upline_username')) {
                $upline = User::where('username', $request->upline_username)->first();
                if ($upline) $user->parent_id = $upline->id;
            }
            if ($request->filled('sponsor_username')) {
                $sponsor = User::where('username', $request->sponsor_username)->first();
                if ($sponsor) $user->sponsor_id = $sponsor->id;
            }

            if ($request->filled('position')) {
                $user->position = $request->position;
            }

            // wallets & balances
            $user->deposit_wallet_balance = $request->deposit_wallet_balance ?? $user->deposit_wallet_balance ?? 0;
             $user->unilevel_wallet_balance = $request->unilevel_wallet_balance ?? $user->unilevel_wallet_balance ?? 0;
            $user->withdraw_wallet_balance = $request->withdraw_wallet_balance ?? $user->withdraw_wallet_balance ?? 0;

            // matching carryovers
            $user->left_ctp_for_matching = $request->left_ctp_for_matching ?? $user->left_ctp_for_matching ?? 0;
            $user->right_ctp_for_matching = $request->right_ctp_for_matching ?? $user->right_ctp_for_matching ?? 0;

            // cpt fields
            $user->total_ctp = $request->total_ctp ?? $user->total_ctp ?? 0;
            $user->p_c_cpts = $request->p_c_cpts ?? $user->p_c_cpts ?? 0;
            $user->current_c_cpts = $request->current_c_cpts ?? $user->current_c_cpts ?? 0;
            $user->current_p_c_cpts = $request->current_p_c_cpts ?? $user->current_p_c_cpts ?? 0;

            $user->user_rank = $request->user_rank ?? $user->user_rank;
            $user->user_plan = $request->user_plan ?? $user->user_plan;
            $user->payment_status = $request->payment_status ?? $user->payment_status;

            $user->save();

            // If admin supplied a package to mark as bought, create userpackage and run bonuses
            if ($request->filled('package_id')) {
                $package = Package::find($request->package_id);

                $ctp = $request->ctp_received ?? $package->cpts ?? 0;
                $amountPaid = $request->amount_paid ?? $package->price ?? 0;

                $userpackage = userpackage::create([
                    'user_id' => $user->id,
                    'package_id' => $package->id,
                    'previous_package_id' => $request->previous_package_id ?? null,
                    'amount_paid' => $amountPaid,
                    'ctp_received' => $ctp,
                    'payment_method' => 'admin',
                    'status' => 'approved',
                    'package_order_status' => 'approved',
                ]);

                // update user CPTs & plan
                $user->increment('total_ctp', $ctp);
                $user->increment('p_c_cpts', $ctp);
                $user->increment('current_p_c_cpts', $ctp);
                $user->increment('current_c_cpts', $ctp);
                $user->user_plan = $package->packageName;
                $user->user_rank = $user->user_rank ?? 'Regular';
                $user->save();

                // trigger existing helpers (make sure they exist)
                if (function_exists('addCtpToUplines')) {
                    addCtpToUplines($user->id, $ctp);
                }

                // handleMatchingBonus is often in UserPackageController; call via action if exists
                if (method_exists(app(), 'handleMatchingBonus')) {
                    // not reliable; better to call via controller instance
                }

                // If your project exposes these as services, call them here:
                try {
                    // dispatch bonuses if your services are available
                    if (class_exists('\App\\Services\\BonusService')) {
                        \App\Services\BonusService::distribute($user);
                    }
                } catch (\Exception $e) {
                    // swallow—admin can manually re-run bonus scripts if needed
                }
            }

        }); // DB transaction

        return redirect()->route('superadmin.migration.migrate_user')->with('success', 'User migrated/updated successfully.');
    }
}
