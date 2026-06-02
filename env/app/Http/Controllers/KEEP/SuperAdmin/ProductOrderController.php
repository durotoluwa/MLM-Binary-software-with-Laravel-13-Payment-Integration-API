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






/**
 * Unilevel Bonus Distribution
 */
private function processUnilevelBonus234($buyerId, $orderAmount)
{
    $percentages = [5, 4, 3, 2, 1, 1, 1, 1];
    $currentUser = User::find($buyerId);
    $level = 0;

    \Log::info("Processing unilevel bonus for buyer {$buyerId} ({$currentUser->name})");

    while ($currentUser->sponsor_id && $level < count($percentages)) {
        $sponsor = User::find($currentUser->sponsor_id);
        if (!$sponsor) break;

        $bonusAmount = ($percentages[$level] / 100) * $orderAmount;

        \Log::info("Level " . ($level+1) . ": Buyer {$currentUser->id} -> Sponsor {$sponsor->id} ({$sponsor->name}), Bonus: {$bonusAmount}");

        if ($bonusAmount > 0) {
            $sponsor->increment('unilevel_wallet_balance', $bonusAmount);

            \App\Models\Bonus::create([
                'user_id' => $sponsor->id,
                'amount' => $bonusAmount,
                'type' => 'unilevel',
                'description' => "Level " . ($level + 1) . " bonus from purchase by {$currentUser->name} (User ID: {$currentUser->id})",
                'is_paid' => 1,
            ]);
        }

        $currentUser = $sponsor;
        $level++;
    }
}




private function processUnilevelBonus($buyerId, $orderAmount)
{
    // 20% to buyer, then 8 more generations: [5,4,3,2,1,1,1,1]
    $percentages = [20, 5, 4, 3, 2, 1, 1, 1, 1];

    $buyer = User::find($buyerId);

    if (!$buyer) {
        \Log::error("Unilevel bonus: Buyer not found: {$buyerId}");
        return;
    }

    \Log::info("Processing UNILEVEL bonus for Buyer {$buyer->id} ({$buyer->username})");

    // -------------------------------
    //  Level 1 — Buyer gets 20%
    // -------------------------------
    $buyerBonus = ($percentages[0] / 100) * $orderAmount;

    $buyer->increment('unilevel_wallet_balance', $buyerBonus);

    \App\Models\Bonus::create([
        'user_id' => $buyer->id,
        'amount' => $buyerBonus,
        'type' => 'unilevel',
        'description' => "Level 1 -  20% unilevel bonus",
        'is_paid' => 1,
    ]);

    \Log::info("Buyer gets 20% = {$buyerBonus}");

    // -------------------------------
    //  Levels 2 to 9 — 8 Uplines
    // -------------------------------
    $currentUser = $buyer;
    $level = 1; // Next level (index 1 = 5%)

    while ($currentUser->sponsor_id && $level < count($percentages)) {

        $sponsor = User::find($currentUser->sponsor_id);
        if (!$sponsor) {
            \Log::warning("Unilevel stopped: missing sponsor at level {$level}");
            break;
        }

        $percentage = $percentages[$level];
        $bonusAmount = ($percentage / 100) * $orderAmount;

        \Log::info("Level " . ($level + 1) . ": Sponsor {$sponsor->id} gets {$percentage}% = {$bonusAmount}");

        if ($bonusAmount > 0) {
            $sponsor->increment('unilevel_wallet_balance', $bonusAmount);

            \App\Models\Bonus::create([
                'user_id' => $sponsor->id,
                'amount' => $bonusAmount,
                'type' => 'unilevel',
                'description' => "Level " . ($level + 1) . " unilevel bonus from buyer {$buyer->username}",
                'is_paid' => 1,
            ]);
        }

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
    $request->validate([
        'payment_method' => 'required|in:wallet,bank,online',
        'bank_name' => 'required_if:payment_method,bank',
        'account_name' => 'required_if:payment_method,bank',
        'amount' => 'required_if:payment_method,bank|numeric',
        'proof' => 'required_if:payment_method,bank|image|mimes:jpeg,png,jpg|max:2048',
        'transaction_pin' => 'required_if:payment_method,wallet',
    ]);

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

    // ----------------------
    // WALLET
    // ----------------------
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

            $orderNo = 'DLT' . strtoupper(uniqid());
            $productOrder = product_order::create([
                'user_id' => $user->id,
                'order_no' => $orderNo,
                'amount' => $totalAmount,
                'payment_method' => 'wallet',
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

                // Add CPT to buyer
                $user->increment('total_ctp', $item['cpts'] * $item['quantity']);

                // Distribute to sponsor chain (30 levels)
                $uplineId = $user->sponsor_id;
                $level = 1;
                while ($uplineId && $level <= 30) {
                    $upline = User::find($uplineId);
                    if (!$upline) break;
                    $upline->increment('total_ctp', $item['cpts'] * $item['quantity']);
                    $uplineId = $upline->sponsor_id;
                    $level++;
                }
            }

            // call unilevel bonus
            $this->processUnilevelBonus($user->id, $totalAmount);

            DB::commit();
            session()->forget('cart');
            return redirect()->route('user.order_product')->with('success', 'Order successful via wallet');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Checkout failed: ' . $e->getMessage());
        }
    }



    // ----------------------
    // BANK
    // ----------------------
    if ($request->payment_method === 'bank') {
        $orderNo = 'DLT' . strtoupper(uniqid());

        $proofPath = null;
        if ($request->hasFile('proof')) {
            $proofPath = $request->file('proof')->store('payment_proofs', 'public');
        }

        $productOrder = product_order::create([
            'user_id' => $user->id,
            'order_no' => $orderNo,
            'amount' => $totalAmount,
            'payment_method' => 'bank',
            'status' => 'pending',
            'ctp' => $ctpTotal,
            'bank_name' => $request->bank_name,
            'account_name' => $request->account_name,
            'proof' => $proofPath,
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
        }

        session()->forget('cart');
        return redirect()->route('user.order_product')->with('success', 'Bank payment submitted, awaiting admin approval.');
    }

    // ----------------------
    // ONLINE (Paystack inline) verification path
    // JS will POST JSON with paystack_reference + payment_method = online
    // ----------------------
    if ($request->payment_method === 'online') {
        $reference = $request->input('paystack_reference');
        if (empty($reference)) {
            return response()->json(['status' => 'error', 'message' => 'Missing Paystack reference.'], 422);
        }

        try {
            // verify at paystack
            $resp = Http::withOptions(['verify' => false]) // set verify=>true in production after CA fixed
                ->withToken(env('PAYSTACK_SECRET_KEY'))
                ->get("https://api.paystack.co/transaction/verify/{$reference}");

            if (!$resp->successful() || !isset($resp['data'])) {
                return response()->json(['status' => 'error', 'message' => 'Paystack verification failed.'], 422);
            }

            if (($resp['data']['status'] ?? '') !== 'success') {
                return response()->json(['status' => 'error', 'message' => 'Paystack reports payment not successful.'], 422);
            }

            // Verified: create approved product order and items (inside transaction)
            DB::beginTransaction();
            try {
                $orderNo = 'DLT' . strtoupper(uniqid());
                $productOrder = product_order::create([
                    'user_id' => $user->id,
                    'order_no' => $orderNo,
                    'amount' => $totalAmount,
                    'payment_method' => 'online',
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
                    $user->increment('total_ctp', $item['cpts'] * $item['quantity']);

                    // Distribute to sponsor chain (30 levels)
                    $uplineId = $user->sponsor_id;
                    $level = 1;
                    while ($uplineId && $level <= 30) {
                        $upline = User::find($uplineId);
                        if (!$upline) break;
                        $upline->increment('total_ctp', $item['cpts'] * $item['quantity']);
                        $uplineId = $upline->sponsor_id;
                        $level++;
                    }
                }

                // call unilevel bonus
                $this->processUnilevelBonus($user->id, $totalAmount);

                DB::commit();
                session()->forget('cart');

                  // Flash success message (for non-AJAX redirect)
        session()->flash('success', 'Payment verified successfully - your product has been purchased.');

                return response()->json([
                    'status' => 'success',
                    'message' => 'Payment verified successfully!',
                    'redirect_url' => route('user.order_product') // change to user.dashboard if desired
                ], 200);
            } catch (\Exception $e) {
                DB::rollBack();
                return response()->json(['status' => 'error', 'message' => 'Processing order failed: ' . $e->getMessage()], 500);
            }
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Paystack verification error: ' . $e->getMessage()], 500);
        }
    }

    // Fallback
    return back()->with('error', 'Invalid payment method.');
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
        $user->increment('total_ctp', $ctpToAdd);

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
