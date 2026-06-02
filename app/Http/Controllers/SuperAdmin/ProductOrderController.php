<?php

namespace App\Http\Controllers\superadmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use App\Models\product_order;
use App\Models\product_order_item;
use App\Models\Product;
use App\Models\User;
use App\Models\Bonus;
use App\Models\userpackage;
use Yabacon\Paystack;
use App\Models\incentives;
use App\Models\incentive_settings;



class ProductOrderController extends Controller
{


public function addToCart(Request $request, $id)
{
    $product = Product::findOrFail($id);

    $cart = session()->get('cart', []);

    $quantity = max((int) $request->quantity, 1); // Ensure at least 1

    if (isset($cart[$id])) {
        $cart[$id]['quantity'] += $quantity;
        $cart[$id]['total'] = ($cart[$id]['price'] + $cart[$id]['apc']) * $cart[$id]['quantity'];
    } else {
        $cart[$product->id] = [
            'product_id' => $product->id,
            'name' => $product->productName,
            'price' => $product->price,
            'apc' => $product->apc,
            'cpts' => $product->cpts, 
            'image' => $product->product_image,
            'quantity' => $quantity,
            'total' => ($product->price + $product->apc) * $quantity,
        ];
    }

    session()->put('cart', $cart);

    return redirect()->back()->with('success', 'Product added to cart!');
}







    public function viewCart()
    {
        $cart = session()->get('cart', []);
        return view('cart.view', compact('cart'));
    }

    public function updateCart(Request $request)
    {
        $request->validate([
            'quantities' => 'required|array',
        ]);

        $cart = session()->get('cart', []);
        foreach ($request->quantities as $id => $qty) {
            if (isset($cart[$id])) {
                $cart[$id]['quantity'] = max(1, (int) $qty);
            }
        }

        session()->put('cart', $cart);
        return redirect()->back()->with('success', 'Cart updated');
    }

    public function removeFromCart($id)
    {
        $cart = session()->get('cart', []);
        unset($cart[$id]);
        session()->put('cart', $cart);
        return redirect()->back()->with('success', 'Item removed from cart');
    }

 

public function checkoutPage()
{
    $cart = session('cart', []);
    $user = auth()->user();

    $total = 0;
    $totalCtp = 0;

    foreach ($cart as $item) {
        $apc = $item['apc'] ?? 0;
        $priceWithApc = $item['price'] + $apc;
        $quantity = $item['quantity'];

        $total += $priceWithApc * $quantity;
        $totalCtp += ($item['cpts'] ?? 0) * $quantity;
    }

    return view('user.checkout', compact('cart', 'total', 'totalCtp'));
}


public function admincheckoutPage()
{
    $cart = session('cart', []);

    $total = 0;
    $totalCtp = 0;

    foreach ($cart as $item) {
        $apc = $item['apc'] ?? 0;
        $priceWithApc = $item['price'] + $apc;
        $quantity = $item['quantity'];

        $total += $priceWithApc * $quantity;
        $totalCtp += ($item['cpts'] ?? 0) * $quantity;
    }

      $users = \App\Models\User::role('user')->get();

    return view('superadmin.admincheckout', compact('cart', 'total', 'totalCtp', 'users'));
}



public function remove($id)
{
    $cart = session('cart', []);
    if (isset($cart[$id])) {
        unset($cart[$id]);
        session()->put('cart', $cart);
    }

    return response()->json(['status' => 'success']);
}

public function clear()
{
    session()->forget('cart');
    return response()->json(['status' => 'cleared']);
}


private function processUnilevelBonus($buyerId, $orderAmount)
{
    /*
    |--------------------------------------------------------------------------
    | ENSURE NUMERIC VALUE
    |--------------------------------------------------------------------------
    */

    $orderAmount = (float) $orderAmount;

    /*
    |--------------------------------------------------------------------------
    | BONUS PERCENTAGES
    |--------------------------------------------------------------------------
    |
    | Buyer = 20%
    | Sponsors = 5%, 4%, 3%, 2%, 1%, 1%, 1%, 1%
    |
    */

    $percentages = [20, 5, 4, 3, 2, 1, 1, 1, 1];

    $buyer = User::find($buyerId);

    if (!$buyer) {

        \Log::error("Unilevel bonus: Buyer not found: {$buyerId}");

        return;
    }

    \Log::info(
        "Processing UNILEVEL bonus for Buyer {$buyer->id} ({$buyer->username})"
    );

    /*
    |--------------------------------------------------------------------------
    | LEVEL 1 — BUYER GETS 20%
    |--------------------------------------------------------------------------
    */

    $buyerBonus = ((float) $percentages[0] / 100) * $orderAmount;

    $buyer->increment(
        'unilevel_wallet_balance',
        (float) $buyerBonus
    );

    /*
    |--------------------------------------------------------------------------
    | CREDIT RECORD FOR BUYER
    |--------------------------------------------------------------------------
    */

    \App\Models\Bonus::create([
        'user_id'        => $buyer->id,
        'source_user_id' => $buyer->id,
        'amount'         => (float) $buyerBonus,
        'type'           => 'unilevel',
        'description'    => "Level 1 - 20% unilevel bonus credited to {$buyer->username}",
        'status'         => 'credit',
    ]);

    \Log::info("Buyer gets 20% = {$buyerBonus}");

    /*
    |--------------------------------------------------------------------------
    | LEVELS 2 TO 9 — SPONSOR CHAIN
    |--------------------------------------------------------------------------
    */

    $currentUser = $buyer;

    $level = 1;

    while (
        $currentUser->sponsor_id &&
        $level < count($percentages)
    ) {

        $sponsor = User::find($currentUser->sponsor_id);

        if (!$sponsor) {

            \Log::warning(
                "Unilevel stopped: missing sponsor at level {$level}"
            );

            break;
        }

        /*
        |--------------------------------------------------------------------------
        | SKIP INACTIVE / MUTED SPONSORS
        |--------------------------------------------------------------------------
        */

        if (
            $sponsor->status !== 'active' ||
            $sponsor->is_muted == 1
        ) {

            $currentUser = $sponsor;

            $level++;

            continue;
        }

        /*
        |--------------------------------------------------------------------------
        | CALCULATE BONUS
        |--------------------------------------------------------------------------
        */

        $percentage = (float) $percentages[$level];

        $bonusAmount = ($percentage / 100) * $orderAmount;

        \Log::info(
            "Level " . ($level + 1) .
            ": Sponsor {$sponsor->id} gets {$percentage}% = {$bonusAmount}"
        );

        if ($bonusAmount > 0) {

            /*
            |--------------------------------------------------------------------------
            | CREDIT SPONSOR WALLET
            |--------------------------------------------------------------------------
            */

            $sponsor->increment(
                'unilevel_wallet_balance',
                (float) $bonusAmount
            );

            /*
            |--------------------------------------------------------------------------
            | CREDIT RECORD FOR SPONSOR
            |--------------------------------------------------------------------------
            */

            \App\Models\Bonus::create([
                'user_id'        => $sponsor->id,
                'source_user_id' => $buyer->id,
                'amount'         => (float) $bonusAmount,
                'type'           => 'unilevel',
                'description'    => "Level " . ($level + 1) .
                    " unilevel bonus from {$buyer->username} to {$sponsor->username}",
                'status'         => 'credit',
            ]);

            /*
            |--------------------------------------------------------------------------
            | DEBIT RECORD FOR BUYER
            |--------------------------------------------------------------------------
            */

            \App\Models\Bonus::create([
                'user_id'        => $buyer->id,
                'source_user_id' => $sponsor->id,
                'amount'         => (float) $bonusAmount,
                'type'           => 'unilevel',
                'description'    => "Level " . ($level + 1) .
                    " unilevel bonus paid to {$sponsor->username}",
                'status'         => 'debit',
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | MOVE UP SPONSOR TREE
        |--------------------------------------------------------------------------
        */

        $currentUser = $sponsor;

        $level++;
    }

    \Log::info("UNILEVEL bonus completed up to 9 generations.");
}






/**
 * Checkout Function
 */

public function checkout(Request $request)
{
    try {

        $user = auth()->user();

        $request->validate([
            'order_for'          => 'required|in:self,member',
            'member_username'    => 'nullable|required_if:order_for,member|exists:users,username',
            'payment_method'     => 'required|in:wallet,bank,online',
            'bank_name'          => 'required_if:payment_method,bank',
            'account_name'       => 'required_if:payment_method,bank',
            'proof'              => 'required_if:payment_method,bank|image|mimes:jpeg,png,jpg|max:2048',
            'transaction_pin'    => 'required_if:payment_method,wallet',
            'paystack_reference' => 'required_if:payment_method,online',
        ]);

        $cart = session('cart');

        if (!$cart || count($cart) === 0) {
            return back()->with('error', 'Cart is empty.');
        }

        /*
        |--------------------------------------------------------------------------
        | Determine Beneficiary
        |--------------------------------------------------------------------------
        */
        $beneficiary = $user;

        if ($request->order_for === 'member') {
            $beneficiary = User::where('username', $request->member_username)->first();

            if (!$beneficiary) {
                return back()->with('error', 'Invalid member username.');
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Calculate Totals
        |--------------------------------------------------------------------------
        */
        $totalAmount = 0;
        $singletotalAmount = 0;
        $ctpTotal = 0;

        foreach ($cart as &$item) {

            $item['quantity'] = (int) ($item['quantity'] ?? 1);
            $item['price']    = (float) ($item['price'] ?? 0);
            $item['apc']      = (float) ($item['apc'] ?? 0);
            $item['cpts']     = (float) ($item['cpts'] ?? 0);

            $item['apc_total']   = $item['apc'] * $item['quantity'];
            $item['total']       = ($item['price'] * $item['quantity']) + $item['apc_total'];
            $item['singletotal'] = $item['price'] * $item['quantity'];

            $totalAmount       += $item['total'];
            $singletotalAmount += $item['singletotal'];
            $ctpTotal          += $item['cpts'] * $item['quantity'];
        }

        /*
        |--------------------------------------------------------------------------
        | WALLET PAYMENT
        |--------------------------------------------------------------------------
        */
        if ($request->payment_method === 'wallet') {

            if ($request->transaction_pin !== $user->transaction_pin) {
                return back()->with('error', 'Invalid Transaction PIN.');
            }

            if ($user->deposit_wallet_balance < $totalAmount) {
                return back()->with('error', 'Insufficient wallet balance.');
            }

            DB::beginTransaction();

            try {

                $user->decrement('deposit_wallet_balance', $totalAmount);
                // Log wallet payment as a debit in Bonus table
\App\Models\Bonus::create([
    'user_id'        => $user->id,              // payer (the one whose wallet was used)
    'amount'         => $totalAmount,           // total amount paid with wallet
    'source_user_id' => $beneficiary->id,       // beneficiary (the member receiving the product)
    'type'           => 'wallet_payment',       // custom type for wallet payments
    'status'         => 'debit',                // always debit for payer
    'description'    => "Wallet payment for product purchased for {$beneficiary->username}",
]);


                $this->createOrder(
                    $beneficiary,
                    $user,
                    $cart,
                    $totalAmount,
                    $ctpTotal,
                    $singletotalAmount,
                    'wallet',
                    'approved'
                );

                DB::commit();
                session()->forget('cart');

                return redirect()->route('user.order_product')
                    ->with('success', 'Order successful via wallet.');

            } catch (\Exception $e) {

                DB::rollBack();

                return back()->with('error', $e->getMessage());
            }
        }

        /*
        |--------------------------------------------------------------------------
        | BANK PAYMENT
        |--------------------------------------------------------------------------
        */
        if ($request->payment_method === 'bank') {

            $proofPath = null;

            if ($request->hasFile('proof')) {
                $proofPath = $request->file('proof')
                    ->store('payment_proofs', 'public');
            }

            $this->createOrder(
                $beneficiary,
                $user,
                $cart,
                $totalAmount,
                $ctpTotal,
                $singletotalAmount,
                'bank',
                'pending',
                $request->bank_name,
                $request->account_name,
                $proofPath
            );

            session()->forget('cart');

            return redirect()->route('user.order_product')
                ->with('success', 'Bank payment submitted. Awaiting approval.');
        }

        /*
        |--------------------------------------------------------------------------
        | ONLINE PAYMENT (PAYSTACK)
        |--------------------------------------------------------------------------
        */
        if ($request->payment_method === 'online') {

            $reference = $request->paystack_reference;

            $resp = Http::withOptions(['verify' => false])
                ->withHeaders([
                    'Authorization' => 'Bearer ' . env('PAYSTACK_SECRET_KEY'),
                    'Accept'        => 'application/json',
                ])
                ->get("https://api.paystack.co/transaction/verify/{$reference}");

            if (
                !$resp->successful() ||
                ($resp['data']['status'] ?? '') !== 'success'
            ) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Payment verification failed.',
                ], 422);
            }

            $paidAmount = $resp['data']['amount'] / 100;

            if ((float) $paidAmount != (float) $totalAmount) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Payment amount mismatch.',
                ], 422);
            }

            DB::beginTransaction();

            try {

                $this->createOrder(
                    $beneficiary,
                    $user,
                    $cart,
                    $totalAmount,
                    $ctpTotal,
                    $singletotalAmount,
                    'online',
                    'approved'
                );

                DB::commit();
                session()->forget('cart');

                return response()->json([
                    'status'       => 'success',
                    'message'      => 'Order successful via Paystack.',
                    'redirect_url' => route('user.order_product'),
                ]);

            } catch (\Exception $e) {

                DB::rollBack();

                return response()->json([
                    'status'  => 'error',
                    'message' => $e->getMessage(),
                ], 500);
            }
        }

        return back()->with('error', 'Invalid payment method selected.');

    } catch (\Illuminate\Validation\ValidationException $e) {

        return back()
            ->withErrors($e->validator)
            ->withInput();

    } catch (\Exception $e) {

        return back()->with('error', $e->getMessage());
    }
}

private function createOrder(
    $beneficiary,
    $payer,
    $cart,
    $totalAmount,
    $ctpTotal,
    $singletotalAmount,
    $paymentMethod,
    $status,
    $bankName = null,
    $accountName = null,
    $proofPath = null
) {

    $orderNo = 'DLT' . strtoupper(uniqid());

    /*
    |--------------------------------------------------------------------------
    | CREATE ORDER
    |--------------------------------------------------------------------------
    */

    $order = product_order::create([
        'user_id'        => $beneficiary->id,
        'ordered_by'     => $payer->id,
        'order_no'       => $orderNo,
        'amount'         => (float) $totalAmount,
        'payment_method' => $paymentMethod,
        'status'         => $status,
        'ctp'            => (float) $ctpTotal,
        'bank_name'      => $bankName,
        'account_name'   => $accountName,
        'proof'          => $proofPath,
    ]);

    /*
    |--------------------------------------------------------------------------
    | CREATE ORDER ITEMS
    |--------------------------------------------------------------------------
    */

    foreach ($cart as $item) {

        \App\Models\product_order_item::create([

            'product_order_id' => $order->id,
            'product_id'       => $item['product_id'],
            'quantity'         => (int) $item['quantity'],
            'price'            => (float) $item['price'],
            'product_name'     => $item['name'],
            'apc'              => (float) $item['apc'],
            'ctp'              => (float) $item['cpts'],
            'apc_total'        => (float) $item['apc_total'],
            'total'            => (float) $item['total'],
        ]);

        /*
        |--------------------------------------------------------------------------
        | CPT GAINED
        |--------------------------------------------------------------------------
        */

        $ctpGained =
            (float) $item['cpts']
            * (int) $item['quantity'];

        /*
        |--------------------------------------------------------------------------
        | ADD CPT USING CENTRALIZED SYSTEM
        |--------------------------------------------------------------------------
        |
        | This automatically handles:
        |
        | - personal CPT
        | - left/right binary tree CPT
        | - uplines
        | - incentive checks
        |
        */

        $this->addCTP($beneficiary, $ctpGained);
    }

    /*
    |--------------------------------------------------------------------------
    | PROCESS UNILEVEL BONUS
    |--------------------------------------------------------------------------
    */

    $this->processUnilevelBonus(
        $beneficiary->id,
        (float) $singletotalAmount
    );

    /*
    |--------------------------------------------------------------------------
    | FINAL INCENTIVE CHECK
    |--------------------------------------------------------------------------
    */

    $this->evaluateUserIncentives($beneficiary);
}
 



public function addCTPToUplines(
    User $buyer,
    int $ctpGained,
    bool $isUpgrade = false,
    $previousPackageId = null
): void {

    if ($isUpgrade && $previousPackageId) {

        $previous = Package::find($previousPackageId);

        if ($previous) {
            $ctpGained = max($ctpGained, 0);
        }
    }

    $currentUplineId = $buyer->parent_id;

    while ($currentUplineId) {

        $upline = User::find($currentUplineId);

        if (!$upline) {
            break;
        }

        // Skip inactive/muted
        if ($upline->status !== 'active' || $upline->is_muted == 1) {

            $currentUplineId = $upline->parent_id;
            continue;
        }

        /*
        |--------------------------------------------------------------------------
        | TOTAL TEAM CPT
        |--------------------------------------------------------------------------
        */

        $upline->increment('downline_cpt', $ctpGained);
        $upline->increment('total_ctp', $ctpGained);

        /*
        |--------------------------------------------------------------------------
        | DETERMINE LEFT/RIGHT TREE
        |--------------------------------------------------------------------------
        */

        $side = $this->getSideRelativeToAncestor($upline, $buyer);

        if ($side === 'left') {

            $upline->increment('downline_left_cpt', $ctpGained);

        } elseif ($side === 'right') {

            $upline->increment('downline_right_cpt', $ctpGained);
        }

        /*
        |--------------------------------------------------------------------------
        | CHECK INCENTIVES
        |--------------------------------------------------------------------------
        */

        $this->evaluateUserIncentives($upline);

        /*
        |--------------------------------------------------------------------------
        | MOVE UP
        |--------------------------------------------------------------------------
        */

        $currentUplineId = $upline->parent_id;
    }
}
 



public function evaluateUserIncentives(User $user): void
{
    $ranks = DB::table('incentive_settings')
        ->orderBy('required_ctp', 'desc')
        ->get();

    /*
    |--------------------------------------------------------------------------
    | USE STORED LEFT / RIGHT TREE TOTALS
    |--------------------------------------------------------------------------
    */

    $leftLegTotal  = $user->downline_left_cpt ?? 0;
    $rightLegTotal = $user->downline_right_cpt ?? 0;

    /*
    |--------------------------------------------------------------------------
    | TOTAL TEAM CPT
    |--------------------------------------------------------------------------
    */

    $totalCtp = $leftLegTotal + $rightLegTotal;

    /*
    |--------------------------------------------------------------------------
    | WEAKER LEG
    |--------------------------------------------------------------------------
    */

    $weakerLeg = min($leftLegTotal, $rightLegTotal);

    foreach ($ranks as $r) {

        /*
        |--------------------------------------------------------------------------
        | REQUIRED WEAKER LEG
        |--------------------------------------------------------------------------
        |
        | Example:
        | required_ctp = 1100
        | min_lesser_leg_percent = 40
        |
        | 40% of 1100 = 440
        |
        */

        $requiredWeakerLeg =
            ($r->min_lesser_leg_percent / 100)
            * $r->required_ctp;

        /*
        |--------------------------------------------------------------------------
        | CHECK TOTAL CPT
        |--------------------------------------------------------------------------
        */

        if ($totalCtp < $r->required_ctp) {
            continue;
        }

        /*
        |--------------------------------------------------------------------------
        | CHECK WEAKER LEG
        |--------------------------------------------------------------------------
        */

        if ($weakerLeg < $requiredWeakerLeg) {
            continue;
        }

        /*
        |--------------------------------------------------------------------------
        | QUALIFIED
        |--------------------------------------------------------------------------
        */

        $user->user_rank = $r->rank;

        $user->save();

        Incentives::updateOrCreate(
            [
                'user_id' => $user->id,
                'rank'    => $r->rank,
            ],
            [
                'status'      => 'achieved',
                'achieved_at' => now(),
            ]
        );

        /*
        |--------------------------------------------------------------------------
        | STOP AFTER HIGHEST QUALIFIED RANK
        |--------------------------------------------------------------------------
        */

        break;
    }
}





public function addCTP(User $user, int $ctpToAdd): void
{
    // Personal CPT
    $user->increment('total_ctp', $ctpToAdd);
    $user->increment('p_c_cpts', $ctpToAdd);
    $user->increment('current_p_c_cpts', $ctpToAdd);
    $user->increment('current_c_cpts', $ctpToAdd);

    // Update uplines
    $this->addCTPToUplines($user, $ctpToAdd);

    // Evaluate self
    $this->evaluateUserIncentives($user);
}



private function getSideRelativeToAncestor($ancestor, $descendant)
{
    $current = $descendant;

    while ($current && $current->parent_id) {

        if ($current->parent_id == $ancestor->id) {
            return strtolower($current->position); // left or right
        }

        $current = User::find($current->parent_id);
    }

    return null;
}










public function admincheckoutapprove(Request $request)
{

      $cart = session('cart');
    if (!$cart || count($cart) === 0) {
        // For AJAX online path return JSON error; for normal return redirect back.
        if ($request->expectsJson()) {
            return response()->json(['status' => 'error', 'message' => 'Cart is empty.'], 422);
        }
        return back()->with('error', 'Cart is empty.');
    }

    $user = auth()->user();

       // Calculate totals & CTPs
    $totalAmount = 0;
    $ctpTotal = 0;
    foreach ($cart as &$item) {
        $item['quantity'] = isset($item['quantity']) ? (int)$item['quantity'] : 1;
        $item['apc'] = isset($item['apc']) ? (float)$item['apc'] : 0;
        $item['price'] = isset($item['price']) ? (float)$item['price'] : 0;
        $item['cpts'] = isset($item['cpts']) ? (float)$item['cpts'] : 0;

        $item['apc_total'] = $item['apc'] * $item['quantity'];
        $item['total'] = ($item['price'] * $item['quantity']) + $item['apc_total'];

        $totalAmount += $item['total'];
        $ctpTotal += $item['cpts'] * $item['quantity'];
    }
   
    if ($request->has('is_admin_order') && auth()->user()->hasRole('superadmin')) {
    $targetUser = User::findOrFail($request->input('user_id'));

    DB::beginTransaction();
    try {
        $orderNo = 'DLT' . strtoupper(uniqid());
        $productOrder = \App\Models\product_order::create([
            'user_id' => $targetUser->id,
            'order_no' => $orderNo,
            'amount' => $totalAmount,
            'payment_method' => 'admin_manual',
            'status' => 'approved',
            'ctp' => $ctpTotal,
        ]);

        foreach ($cart as $item) {
            \App\Models\product_order_item::create([
                'product_order_id' => $productOrder->id,
                'product_id' => $item['product_id'],
                'quantity' => $item['quantity'],
                'price' => $item['price'],
                'product_name' => $item['name'],
                'apc' => $item['apc'],
                'ctp' => $item['cpts'],
                'apc_total' => $item['apc_total'],
                'total' => $item['total'],
            ]);

            // Add CTP to buyer
            $targetUser->increment('total_ctp', $item['cpts'] * $item['quantity']);

            // Distribute to sponsor chain (30 levels)
            $uplineId = $targetUser->sponsor_id;
            $level = 1;
            while ($uplineId && $level <= 30) {
                $upline = \App\Models\User::find($uplineId);
                if (!$upline) break;
                $upline->increment('total_ctp', $item['cpts'] * $item['quantity']);
                $uplineId = $upline->sponsor_id;
                $level++;
            }
        }

        // Call unilevel bonus for the user
        $this->processUnilevelBonus($targetUser->id, $totalAmount);

        DB::commit();
        session()->forget('cart');

        return redirect()
            ->route('superadmin.package.order_product')
            ->with('success', 'Order successfully placed for ' . $targetUser->username . ' (no payment).');
    } catch (\Exception $e) {
        DB::rollBack();
        return back()->with('error', 'Admin order failed: ' . $e->getMessage());
    }
}
}




public function paystackCallback(Request $request)
{
    $orderNo = $request->get('order_no');
    $reference = $request->get('reference');

    if (!$reference) {
        return redirect()->route('user.order_product')->with('error', 'Missing Paystack reference.');
    }

    $verify = Http::withOptions(['verify' => false])
        ->withToken(env('PAYSTACK_SECRET_KEY'))
        ->get("https://api.paystack.co/transaction/verify/{$reference}");

    if (!$verify->successful() || $verify['data']['status'] !== 'success') {
        return redirect()->route('user.order_product')->with('error', 'Payment verification failed.');
    }

    $order = product_order::where('order_no', $orderNo)->first();
    if (!$order) {
        return redirect()->route('user.order_product')->with('error', 'Order not found.');
    }

    DB::transaction(function () use ($order, $verify) {
        $order->update(['status' => 'approved']);

        // Update user CPT & distribute
        $user = $order->user;
        $ctpToAdd = $order->ctp;
        $beneficiary->increment('total_ctp', $ctpToAdd);

        // Unilevel bonus
        app(\App\Http\Controllers\YourControllerName::class)
            ->processUnilevelBonus($user->id, $order->amount);
    });

    session()->forget('cart');

    return redirect()->route('user.dashboard')->with('success', 'Payment successful and order approved.');
}





public function checkoutCallback(Request $request)
{
    $orderNo = $request->order_no;
    $user = auth()->user();
    $paystack = new \Yabacon\Paystack(env('PAYSTACK_SECRET_KEY'));

    try {
        $tranx = $paystack->transaction->verify(['reference' => $orderNo]);
    } catch (\Yabacon\Paystack\Exception\ApiException $e) {
        return redirect()->route('user.dashboard')->with('error', 'Payment verification failed.');
    }

    if ($tranx->data->status === 'success') {
        $productOrder = product_order::where('order_no', $orderNo)->first();
        if ($productOrder && $productOrder->status !== 'approved') {
            $productOrder->update(['status' => 'approved']);
            session()->forget('cart');

            return redirect()->route('user.dashboard')->with('success', 'Payment successful! Your order has been confirmed.');
        }
    }

    return redirect()->route('user.dashboard')->with('error', 'Payment verification failed or canceled.');
}




}
