<?php

namespace App\Http\Controllers\superadmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Transaction;
use App\Models\Bonus;
use App\Models\User;
use App\Models\wallettopup;
use App\Models\userpackage;
use App\Models\Package;
use App\Models\Withdrawal;
use App\Models\product_order;
use App\Models\package_product_orders;



use App\Helpers;
use Mail;
 

class TransactionController extends Controller
{
   public function pendingPayments()
    {
        if (!auth()->user()->can('view pending registration payments')) {
            abort(403, 'Unauthorized action.');
        }

        $transactions = Transaction::where('type', 'registration')
            ->where('status', 'pending')
            
            ->with('user')
            ->latest()
            ->get();

        return view('superadmin.transaction.pendingRegistrationPayment', compact('transactions'));
    }


  

    

 private static function generateTransactionPin(): string
{
    $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    return substr(str_shuffle(str_repeat($characters, 6)), 0, 6);
}

protected static function getAvailablePosition($parentId)
{
    $left = User::where('parent_id', $parentId)->where('position', 'left')->exists();
    $right = User::where('parent_id', $parentId)->where('position', 'right')->exists();

    if (!$left) return 'left';
    if (!$right) return 'right';
    return null;
}




protected function findBinaryPlacement(User $upline): array
{
    $queue = [$upline];

    while (!empty($queue)) {
        $current = array_shift($queue);

        // Check for left
        $left = User::where('parent_id', $current->id)->where('position', 'left')->first();
        if (!$left) {
            return ['parent_id' => $current->id, 'position' => 'left'];
        } else {
            $queue[] = $left;
        }

        // Check for right
        $right = User::where('parent_id', $current->id)->where('position', 'right')->first();
        if (!$right) {
            return ['parent_id' => $current->id, 'position' => 'right'];
        } else {
            $queue[] = $right;
        }
    }

    // If somehow no position is found
    throw new \Exception('No available position found in binary tree.');
}


public function approvePayment($id)
{
    if (!auth()->user()->can('approve registration payments')) {
        abort(403, 'Unauthorized action.');
    }

    $transaction = Transaction::findOrFail($id);
    $user = $transaction->user;

    //  Assign binary position after approval if not already assigned
    if (!$user->parent_id || !$user->position) {
        $uplineUser = User::where('username', $user->upline_username)->first();

        if ($uplineUser) {
            $placement = $this->findBinaryPlacement($uplineUser);
            $user->update([
                'parent_id' => $placement['parent_id'],
                'position'  => $placement['position'],
            ]);
        }
    }

    //  Approve transaction
    $transaction->update(['status' => 'approved']);

    //  Update user status + promote to Starter if first payment
    $user->update([
        'payment_status'   => 'approved',
        'status'   => 'inactive',
        'transaction_pin'  => self::generateTransactionPin(),
        'user_rank'        => 'starter', // set Starter only if null
    ]);

    // Trigger registration bonus
    \App\Services\BonusService::distribute($user);

    // Notify user: Payment confirmed
    $data = ['user' => $user];

    Mail::send(['html' => 'emails.registration_approved'], ['data' => $data], function($message) use ($user) {
        $message->to($user->email);
        $message->subject("Your Registration Payment is Approved");
    });

    //  Notify uplines: Registration bonus earned (up to 4 levels)
    $upline = $user->upline;
    $levels = [
        1 => '1st',
        2 => '2nd',
        3 => '3rd',
        4 => '4th',
    ];

    foreach ($levels as $level => $label) {
        if (!$upline) break;

        $bonusAmount = setting("starter_bonus_{$label}", 0); // already used in BonusService

        if ($bonusAmount > 0 && !empty($upline->email)) {
            Mail::send(['html' => 'emails.bonus_notification'], [
                'data' => [
                    'upline'   => $upline,
                    'downline' => $user,
                    'amount'   => $bonusAmount,
                    'level'    => $label,
                ]
            ], function($message) use ($upline, $label) {
                $message->to($upline->email);
                $message->subject("You've Earned a Registration Bonus ({$label} Level)");
            });
        }

        $upline = $upline->upline;
    }

    return redirect()->back()->with('success', 'Payment approved, user promoted to Starter, binary position assigned, and registration bonus paid.');
}




 



 public function arpprovePayments()
    {
        if (!auth()->user()->can('view approve registration payments')) {
            abort(403, 'Unauthorized action.');
        }

        $transactions = Transaction::where('status', 'approved')
            ->with('user')
            ->latest()
            ->get();

        return view('superadmin.transaction.registrationPayment', compact('transactions'));
    }


    



public function regBonuspage()
{
    if (!auth()->user()->can('view bonuses')) {
        abort(403, 'Unauthorized action.');
    }

    
      $bonuses = Bonus::where('type', 'registration')->latest()->get();
    return view('superadmin.bonuses.regBonus', compact('bonuses'));
}

public function markAsPaid($id)
{
    if (!auth()->user()->can('mark bonuses as paid')) {
        abort(403, 'Unauthorized action.');
    }

    $bonus = Bonus::findOrFail($id);
    $bonus->update([
        'is_paid' => true,
        'paid_at' => now(),
    ]);

    return redirect()->back()->with('success', 'Bonus marked as paid.');
}



public function macthingBonuspage()
{
    if (!auth()->user()->can('view matching bonuses')) {
        abort(403, 'Unauthorized action.');
    }

   $bonuses = Bonus::where('type', 'matching')
                        ->latest()
                        ->with('user')
                        ->get();
    return view('superadmin.bonuses.macthingBonus', compact('bonuses'));
}


 public function approveMatching($id)
    {
        $bonus = Bonus::findOrFail($id);

        if ($bonus->is_approved) {
            return back()->with('error', 'Bonus already approved.');
        }

        $user = User::find($bonus->user_id);
        $user->withdraw_wallet_balance += $bonus->amount;
        $user->save();

        $bonus->update([
            'is_approved' => true,
            'approved_at' => now(),
            'approved_by' => Auth::id(),
        ]);

        return back()->with('success', 'Matching bonus approved and credited.');
    }



public function approvepackageOrder()
{
    if (!auth()->user()->can('view approve package order')) {
        abort(403, 'Unauthorized action.');
    }

$approvePackages = UserPackage::where('status', 'approved')
    ->with(['user', 'package', 'packageProductOrders.product'])
    ->get();




    return view('superadmin.package.approvepackageOrder', compact('approvePackages'));
}



public function pendingpackageOrder()
    {
        if (!auth()->user()->can('view pending package order')) {
        abort(403, 'Unauthorized action.');
    }
        $pendingPackages = userpackage::where('status', 'pending')->with(['user', 'package'])->get();

        return view('superadmin.package.pendingpackageOrder', compact('pendingPackages'));
    }



 




public function approveorddrpackage($id)
{
    $userPackage = UserPackage::findOrFail($id);

    if ($userPackage->status === 'approved') {
        return back()->with('error', 'This package is already approved.');
    }

    $user    = User::findOrFail($userPackage->user_id);
    $package = Package::findOrFail($userPackage->package_id);

    $previousPackageId = $userPackage->previous_package_id;

    $amountToPay = $package->price;
    $ctpToAdd    = $package->cpts;

    // --- UPGRADE LOGIC ---
    if ($previousPackageId) {
        $previous = Package::find($previousPackageId);
        if ($previous) {
            $amountToPay -= $previous->price;
            $ctpToAdd    -= $previous->cpts;
        }
    }

    // APC total cost = (APC × qty)
    $amountToPay += ($package->apc * $package->bottle);

    DB::transaction(function () use ($user, $package, $userPackage, $amountToPay, $ctpToAdd, $previousPackageId) {

        // --- UPDATE USER PACKAGE STATUS ---
        $userPackage->update([
            'status'        => 'approved',
            'approved_at'   => now(),
            'approved_by'   => Auth::id(),
            'amount_paid'   => $amountToPay,
            'package_order_status' => 'approved',
        ]);

        // ------------------------------
        //  ACTIVATE USER AFTER APPROVAL
        // ------------------------------
        $user->status = 'active';
        $user->save();
        // ------------------------------

        // --- PRODUCT ORDER REPLICATION ---
        $orderedProducts = DB::table('package_product_orders')
            ->where('package_order_id', $userPackage->id)
            ->get();

        foreach ($orderedProducts as $row) {
            DB::table('package_product_orders')
                ->where('id', $row->id)
                ->update([
                    'updated_at' => now()
                ]);
        }

        // --- UPDATE USER ACCOUNT ---
        $user->total_ctp        += $ctpToAdd;
        $user->p_c_cpts         += $ctpToAdd;
        $user->current_p_c_cpts += $ctpToAdd;
        $user->current_c_cpts   += $ctpToAdd;

        $user->user_plan = $package->packageName;
        if (empty($user->user_rank)) {
            $user->user_rank = 'Regular';
        }

        $user->save();

        // === MATCHING BONUS & UPLINES ===
        if (function_exists('addCtpToUplines')) {
            addCtpToUplines($user->id, $ctpToAdd);
        }

        (new \App\Http\Controllers\User\UserPackageController)
            ->handleMatchingBonus($user, $ctpToAdd);

        // === REFERRAL BONUS ===
        (new \App\Http\Controllers\User\UserPackageController)
            ->payReferralBonus($user, $package->price, $previousPackageId ? true : false, $previousPackageId);
    });

    return back()->with('success', 'Package approved successfully, user activated and all bonuses processed.');
}



 

     public function pendingwallettoupPayments()
    {
        if (!auth()->user()->can('view pending wallet topup')) {
            abort(403, 'Unauthorized action.');
        }

        $transactions = wallettopup::where('status', 'pending')
            ->with('user')
            ->latest()
            ->get();

        return view('superadmin.transaction.pendingwallettopup', compact('transactions'));
    }

public function approveTopup($id)
{
    $topup = wallettopup::findOrFail($id);

    if ($topup->status !== 'pending') {
        return back()->with('error', 'Topup already processed.');
    }

    // Approve topup and credit user
    $topup->update(['status' => 'approved']);

    $user = $topup->user;
    $user->deposit_wallet_balance += $topup->amount;
    $user->save();

    //  Send email to user
    \Mail::send('emails.wallet_topup_approved', ['user' => $user, 'topup' => $topup], function ($message) use ($user) {
        $message->to($user->email);
        $message->subject('Wallet Top-up Approved');
    });

    return back()->with('success', 'Topup approved and wallet credited.');
}



   public function pendingWithdraw()
    {
        if (!auth()->user()->can('view pending withdrawals')) {
            abort(403, 'Unauthorized action.');
        }

        $transactions = Withdrawal::where('status', 'pending')
            ->with('user')
            ->latest()
            ->get();

        return view('superadmin.transaction.pendingwithdraw', compact('transactions'));
    }


    public function approveWithdrawal($id)
{
    $withdrawal = Withdrawal::findOrFail($id);

    if ($withdrawal->status !== 'pending') {
        return back()->with('error', 'Already processed.');
    }

    $user = $withdrawal->user;

    if ($user->withdraw_wallet_balance < $withdrawal->amount) {
        return back()->with('error', 'User has insufficient balance.');
    }

    // Deduct balance
    $user->withdraw_wallet_balance -= $withdrawal->amount;
    $user->save();

    // Update status
    $withdrawal->update(['status' => 'approved']);

    // Notify user
    Mail::send('emails.withdrawal_approved', ['user' => $user, 'withdrawal' => $withdrawal], function ($message) use ($user) {
        $message->to($user->email);
        $message->subject("Withdrawal Approved");
    });

    return back()->with('success', 'Withdrawal approved and wallet updated.');
}




public function pendingproductOrder()
    {
        if (!auth()->user()->can('view pending product order')) {
        abort(403, 'Unauthorized action.');
    }
        $pendingproduct = product_order::where('status', 'pending')->with(['user', 'product'])->get();

        return view('superadmin.product.pendingproductOrder', compact('pendingproduct'));
    }

    



    
public function aproveproductOrder()
    {
        if (!auth()->user()->can('view approve product order')) {
        abort(403, 'Unauthorized action.');
    }
        $pendingproduct = product_order::where('status', 'approved')->with(['user', 'product'])->get();

        return view('superadmin.product.aproveproductOrder', compact('pendingproduct'));
    }



    public function approveBankOrder($id)
{
    $order = product_order::findOrFail($id);

    if ($order->status !== 'pending') {
        return back()->with('error', 'Order already processed.');
    }

    DB::beginTransaction();
    try {
        $user = $order->user;
        $ctpAmount = $order->ctp;

        // Mark order as approved
        $order->status = 'approved';
        $order->save();

        // CTP Logic
        $user->increment('total_ctp', $ctpAmount);

        // Add CTP to uplines (up to 30 levels)
        $uplineId = $user->parent_id;
        $level = 1;
        $maxLevels = 30;

        while ($uplineId && $level <= $maxLevels) {
            $upline = User::find($uplineId);
            if (!$upline) break;

            $upline->increment('total_ctp', $ctpAmount);
            $uplineId = $upline->parent_id;
            $level++;
        }

        DB::commit();
        return back()->with('success', 'Order approved and CTP distributed.');
    } catch (\Exception $e) {
        DB::rollBack();
        return back()->with('error', 'Approval failed: ' . $e->getMessage());
    }
}



public function approve($id)
{
    $order = ProductOrder::findOrFail($id);

    // Ensure order is pending and not already approved
    if ($order->status !== 'pending') {
        return back()->with('error', 'This order is not pending.');
    }

    $user = $order->user;
    $ctpAmount = $order->cpts; // CTP stored directly on product_orders table

    DB::beginTransaction();
    try {
        // Mark order as approved
        $order->status = 'approved';
        $order->save();

        // 1 Add CTP to the buyer
        $user->increment('total_ctp', $ctpAmount);

        // Distribute CTP to 30 uplines (Binary/Rank Tracking)
        $uplineId = $user->parent_id;
        $level = 1;
        while ($uplineId && $level <= 30) {
            $upline = User::find($uplineId);
            if (!$upline) break;

            $upline->increment('total_ctp', $ctpAmount);
            $uplineId = $upline->parent_id;
            $level++;
        }

        //  UNILEVEL INDIRECT BONUS DISTRIBUTION (via sponsor_id chain)
        $bonusRates = [
            1 => 0.05, // 5%
            2 => 0.04, // 4%
            3 => 0.03, // 3%
            4 => 0.02, // 2%
            5 => 0.01, // 1%
            6 => 0.01, // 1%
            7 => 0.01, // 1%
            8 => 0.01  // 1%
        ];

        $sponsorId = $user->sponsor_id;
        $level = 1;

        while ($sponsorId && $level <= 8) {
            $sponsor = User::find($sponsorId);
            if (!$sponsor) break;

            $rate = $bonusRates[$level] ?? 0;
            if ($rate > 0) {
                $bonusAmount = $order->amount * $rate;

                // Add to sponsor's unilevel_wallet_balance
                $sponsor->increment('unilevel_wallet_balance', $bonusAmount);

                // Save to bonus table
                Bonus::create([
                    'user_id'     => $sponsor->id,
                    'amount'      => $bonusAmount,
                    'type'        => 'unilevel',
                    'description' => "Level {$level} unilevel bonus from reorder by {$user->name} (Order #{$order->order_no})",
                    'is_paid'     => 1
                ]);
            }

            $sponsorId = $sponsor->sponsor_id;
            $level++;
        }

        DB::commit();
        return back()->with('success', 'Order approved, CTP and Unilevel bonuses distributed successfully.');

    } catch (\Exception $e) {
        DB::rollBack();
        return back()->with('error', 'Approval failed: ' . $e->getMessage());
    }
}



    public function walletDeposits()
    {
        $transactions = wallettopup::with('user')

            ->latest()
            ->orderBy('id', 'desc')
            ->get();

        return view('superadmin.history.wallet-deposit', compact('transactions'));
    }

        public function registrationPayments()
    {
        $transactions = Transaction::with('user')
            ->where('type', 'registration')
            ->orderBy('id', 'desc')
            ->get();

        return view('superadmin.history.registration-history', compact('transactions'));
    }

    public function  withdrawals()
    {
        $transactions = Withdrawal::with('user')
            ->orderBy('id', 'desc')
            ->get();

        return view('superadmin.history.withdrawal-history', compact('transactions'));
    }



}
