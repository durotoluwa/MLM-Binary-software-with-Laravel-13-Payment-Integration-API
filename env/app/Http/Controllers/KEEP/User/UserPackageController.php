<?php

namespace App\Http\Controllers\user;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use App\Models\product_order;
use App\Models\product_order_item;
use App\Helpers\MlmBonusHelper;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use App\Models\userpackage;
use App\Models\User;
use App\Models\Package;
use Carbon\Carbon;
use App\Models\Bonus; 
use App\Models\Product; 
use App\Models\package_product_orders;
use Yabacon\Paystack; 




 

class userpackageController extends Controller
{



public function showPurchaseForm($id)
{
    $package = Package::findOrFail($id);
    $user = Auth::user();

    // Check if user already purchased this package
    $existing = userpackage::where('user_id', $user->id)
        ->where('package_id', $id)
        ->first();

    if ($existing) {
        return redirect()->back()->with('error', 'You have already purchased this package.');
    }

    // Get the user's last purchased package (previous upgrade)
    $previous = userpackage::where('user_id', $user->id)
        ->latest('id') // or created_at if you track purchases
        ->first();

    $finalPrice = $package->price;

    if ($previous) {
        $previousPackage = Package::find($previous->package_id);
        if ($previousPackage) {
            // Deduct previous package price
            $finalPrice = max($package->price - $previousPackage->price, 0);
        }
    }
  $products = Product::all(); 
    return view('user.purchase-package', compact('package', 'user', 'finalPrice', 'products'));
}



public function selectProducts($package_id)
{
    $package = Package::findOrFail($package_id);
    $products = Product::all();  
    return view('user.package-products', compact('package', 'products'));
}

 






public function purchase(Request $request)
{
    $user = Auth::user();

    $validator = Validator::make($request->all(), [
        'package_id' => 'required|exists:package,id',
        'payment_method' => 'required|in:wallet,bank,online',
        'transaction_pin' => 'required_if:payment_method,wallet',
        'payment_proof' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        'acctName' => 'nullable|string',
        'bankName' => 'nullable|string',
        'amount' => 'nullable|numeric',
        'product' => 'required|array',
        'product.*.id' => 'required|exists:product,id',
        'product.*.qty' => 'nullable|integer|min:0',
    ]);

    if ($validator->fails()) {
        return back()->withErrors($validator)->withInput();
    }

    $newPackage = Package::find($request->package_id);
    if (!$newPackage) {
        return back()->with('error', 'Selected package not found.');
    }

    $maxBottles = (int) $newPackage->bottle;
    $maxCpts = (float) $newPackage->cpts;

    $validator->after(function ($validator) use ($request, $maxBottles, $maxCpts) {
        $rows = $request->product ?? [];
        $selected = array_filter($rows, fn($r) => !empty($r['qty']) && (int)$r['qty'] > 0);

        if (count($selected) === 0) {
            $validator->errors()->add('product', 'Please select products with valid quantities.');
            return;
        }

        $totalQty = 0;
        $totalCpts = 0.0;

        foreach ($selected as $index => $row) {
            $qty = (int) $row['qty'];
            $product = Product::find($row['id']);
            if (!$product) {
                $validator->errors()->add("product.$index.id", 'Invalid product selected.');
                continue;
            }

            $totalQty += $qty;
            $totalCpts += ((float) $product->cpts) * $qty;
        }

        if ($totalQty !== $maxBottles) {
            $validator->errors()->add('product', "You must select exactly {$maxBottles} bottles; you selected {$totalQty}.");
        }

        if ($totalCpts !== $maxCpts) {
            $validator->errors()->add('product', "Total CPTs must be exactly {$maxCpts}; you selected {$totalCpts}.");
        }
    });

    if ($validator->fails()) {
        return back()->withErrors($validator)->withInput();
    }

    $previousPackage = userpackage::where('user_id', $user->id)
        ->where('status', 'approved')
        ->latest()
        ->first();

    if ($previousPackage && $newPackage->id <= $previousPackage->package_id) {
        return back()->with('error', 'You can only upgrade to a higher package.');
    }

    $amountToPay = $newPackage->price;
    $ctpToAdd = $newPackage->cpts;
    $isUpgrade = false;
    $previousPackageId = null;

    if ($previousPackage) {
        $isUpgrade = true;
        $previousPackageId = $previousPackage->package_id;
        $previous = Package::find($previousPackageId);
        $amountToPay -= $previous->price;
        $ctpToAdd -= $previous->cpts;
    }

    $amountToPay += ($newPackage->apc * $newPackage->bottle);

    // === WALLET PAYMENT ===

  
    if ($request->payment_method === 'wallet') {

    if ($request->transaction_pin !== $user->transaction_pin) {
        return back()->withErrors(['transaction_pin' => 'Invalid transaction PIN.'])->withInput();
    }

    if ($user->deposit_wallet_balance < $amountToPay) {
        return back()->with('error', 'Insufficient wallet balance.');
    }

    DB::transaction(function () use ($user, $newPackage, $previousPackageId, $amountToPay, $ctpToAdd, $request, $isUpgrade) {

        // Deduct wallet
        $user->deposit_wallet_balance -= $amountToPay;

        // Add CPT
        $user->total_ctp        += $ctpToAdd;
        $user->p_c_cpts         += $ctpToAdd;
        $user->current_p_c_cpts += $ctpToAdd;
        $user->current_c_cpts   += $ctpToAdd;

        // Update plan
        $user->user_plan = $newPackage->packageName;
        $user->user_rank = $user->user_rank ?? 'Regular';

        // ------------------------------------
        //   Activate user after payment
        // ------------------------------------
        $user->status = 'active';

        $user->save();

        // Create package order
        $userpackage = userpackage::create([
            'user_id'              => $user->id,
            'package_id'           => $newPackage->id,
            'previous_package_id'  => $previousPackageId,
            'amount_paid'          => $amountToPay,
            'payment_method'       => 'wallet',
            'status'               => 'approved',
            'package_order_status' => 'approved',
        ]);

        // Save ordered products
        foreach ($request->product as $row) {
            $qty = (int) ($row['qty'] ?? 0);
            if ($qty <= 0) continue;

            DB::table('package_product_orders')->insert([
                'user_id'          => $user->id,
                'package_id'       => $newPackage->id,
                'product_id'       => $row['id'],
                'package_order_id' => $userpackage->id,
                'qty'              => $qty,
                'created_at'       => now(),
                'updated_at'       => now(),
            ]);
        }

        // Add CPT to uplines + matching bonus
        $this->addCTPToUplines($user, $ctpToAdd, function ($uplineUser) {
            $this->handleMatchingBonus($uplineUser, $ctpToAdd);
        });

        $this->handleMatchingBonus($user, $ctpToAdd);

        // Referral bonus
        $this->payReferralBonus($user, $newPackage->price, $isUpgrade, $previousPackageId);
    });

    return redirect()->route('user.package')->with('success', 'Package purchased successfully using wallet.');
}


    // === BANK PAYMENT ===
    if ($request->payment_method === 'bank') {
        if (!$request->hasFile('payment_proof')) {
            return back()->with('error', 'Please upload proof of payment.');
        }

        $image = $request->file('payment_proof');
        $filename = time() . '_' . Str::uuid() . '.' . $image->getClientOriginalExtension();
        $image->move(public_path('payment_proofs'), $filename);
        $paymentProofPath = 'payment_proofs/' . $filename;

        DB::transaction(function () use ($request, $user, $newPackage, $previousPackageId, $amountToPay, $paymentProofPath) {
            $userpackage = userpackage::create([
                'user_id' => $user->id,
                'package_id' => $newPackage->id,
                'previous_package_id' => $previousPackageId,
                'amount_paid' => $request->amount,
                'payment_method' => 'bank',
                'acctName' => $request->acctName,
                'total_amount_package' => $request->total_amount_package,
                'bankName' => $request->bankName,
                'payment_proof' => $paymentProofPath,
                'status' => 'pending',
                'package_order_status' => 'pending',
            ]);

            foreach ($request->product as $row) {
                $qty = (int) ($row['qty'] ?? 0);
                if ($qty <= 0) continue;

                DB::table('package_product_orders')->insert([
                    'user_id' => $user->id,
                    'package_id' => $newPackage->id,
                    'product_id' => $row['id'],
                    'package_order_id' => $userpackage->id,
                    'qty' => $qty,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        });

        return redirect()->route('user.package')->with('success', 'Package submitted for approval. Awaiting admin confirmation.');
    }

    // ====================================================
    // ONLINE PAYMENT — PAYSTACK
    // ====================================================
   // --- ONLINE PAYMENT --- //
if ($request->payment_method === 'online') {
    try {
        $request->validate([
            'paystack_reference' => 'required|string',
            'package_id' => 'required|exists:package,id',
            'product' => 'required|array',
            'product.*.id' => 'required|exists:product,id',
            'product.*.qty' => 'nullable|integer|min:0',
        ]);

        $reference = $request->paystack_reference;
        $productSelection = $request->product;
        $newPackage = Package::find($request->package_id);

        if (!$newPackage) {
            \Log::error("Package not found for ID: {$request->package_id}");
            return response()->json([
                'status' => 'error',
                'message' => 'Selected package not found.',
            ], 404);
        }

        // Verify Paystack payment
        $verifyResponse = Http::withOptions(['verify' => false])
            ->withHeaders([
                'Authorization' => 'Bearer ' . env('PAYSTACK_SECRET_KEY'),
                'Accept' => 'application/json',
            ])
            ->get("https://api.paystack.co/transaction/verify/{$reference}");

        if (!$verifyResponse->successful() || ($verifyResponse['data']['status'] ?? '') !== 'success') {
            \Log::error('Paystack verification failed', ['response' => $verifyResponse->body()]);
            return response()->json([
                'status' => 'error',
                'message' => 'Payment verification failed.',
            ], 400);
        }

        $amountPaid = $verifyResponse['data']['amount'] / 100;
        $ctpToAdd = $newPackage->cpts;
        $previousPackage = userpackage::where('user_id', $user->id)
            ->where('status', 'approved')
            ->latest()
            ->first();

        $isUpgrade = false;
        $previousPackageId = null;

        if ($previousPackage && $newPackage->id > $previousPackage->package_id) {
            $isUpgrade = true;
            $previousPackageId = $previousPackage->package_id;
            $previous = Package::find($previousPackageId);
            $ctpToAdd -= $previous->cpts;
        }

        DB::transaction(function () use ($user, $newPackage, $previousPackageId, $amountPaid, $ctpToAdd, $productSelection, $isUpgrade) {
            $user->update([
                'total_ctp' => $user->total_ctp + $ctpToAdd,
                'p_c_cpts' => $user->p_c_cpts + $ctpToAdd,
                'current_p_c_cpts' => $user->current_p_c_cpts + $ctpToAdd,
                'current_c_cpts' => $user->current_c_cpts + $ctpToAdd,
                'user_plan' => $newPackage->packageName,
                'status' => 'active',
                'user_rank' => $user->user_rank ?? 'Regular',
            ]);

            $userPackage = userpackage::create([
                'user_id' => $user->id,
                'package_id' => $newPackage->id,
                'previous_package_id' => $previousPackageId,
                'amount_paid' => $amountPaid,
                'payment_method' => 'online',
                'status' => 'approved',
                'package_order_status' => 'approved',
            ]);

            foreach ($productSelection as $row) {
                $qty = (int) ($row['qty'] ?? 0);
                if ($qty <= 0) continue;

                DB::table('package_product_orders')->insert([
                    'user_id' => $user->id,
                    'package_id' => $newPackage->id,
                    'product_id' => $row['id'],
                    'package_order_id' => $userPackage->id,
                    'qty' => $qty,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

          // Distribute CPTs and trigger matching for uplines
$this->addCTPToUplines($user, $ctpToAdd, function ($uplineUser) {
    $this->handleMatchingBonus($uplineUser, $ctpToAdd);
});


            //  Trigger matching for buyer
            $this->handleMatchingBonus($user, $ctpToAdd);

            $this->payReferralBonus($user, $newPackage->price, $isUpgrade, $previousPackageId);
        });

        return response()->json([
            'status' => 'success',
            'message' => 'Package purchased successfully!',
            'redirect_url' => route('user.dashboard'),
        ]);

    } catch (\Exception $e) {
        \Log::error('Online purchase error', ['exception' => $e]);
        return response()->json([
            'status' => 'error',
            'message' => 'Unexpected error occurred. Please try again.',
            'error' => $e->getMessage(),
        ], 500);
    }
}
    return back()->with('error', 'Invalid payment method selected.');
}




















public function distributeCTPsToUplines($user, $cpt)
{
    $upline = User::find($user->parent_id);
    $position = $user->position;

    for ($level = 1; $upline && $level <= 10; $level++) {
        if ($position === 'left') {
            $upline->left_ctp_balance += $cpt;
        } elseif ($position === 'right') {
            $upline->right_ctp_balance += $cpt;
        }

        $upline->save();

        // Attempt matching
        $this->handleMatching($upline);

        // Move up
        $position = $upline->position;
        $upline = User::find($upline->parent_id);
    }
}


private function updateUplineLegCtp(User $user, int $ctp)
{
    if (!$user->parent_id || !$user->position) {
        return;
    }

    $upline = User::find($user->parent_id);
    if (!$upline) return;

    if ($user->position === 'left') {
        $upline->left_ctp_for_matching += $ctp;
    } elseif ($user->position === 'right') {
        $upline->right_ctp_for_matching += $ctp;
    }

    $upline->save();
}



 public function handleMatchingBonus1234(User $user, $ctpGained, $generation = 1, $side = null)
{
    \Log::info("==> handleMatchingBonus() START: user_id={$user->id}, ctpGained={$ctpGained}, gen={$generation}, side={$side}");

    if ($generation > 9) {
        \Log::info("Stopping recursion: reached generation > 9.");
        return;
    }

    if (empty($user->parent_id)) {
        \Log::info("Stopping: user {$user->id} has no parent_id.");
        return;
    }

    $upline = User::find($user->parent_id);
    if (!$upline) {
        \Log::warning("Upline not found for parent_id {$user->parent_id}");
        return;
    }

    // Determine side relative to current user
    if ($generation === 1) {
        $side = strtolower($user->position ?? '');
    } else {
        $side = strtolower($user->position ?? $side);
    }

    if (!in_array($side, ['left', 'right'])) {
        \Log::warning("Invalid side '{$side}' for user {$user->id}. Skipping this level but continuing up.");
        return $this->handleMatchingBonus($upline, $ctpGained, $generation + 1, $side);
    }

    // Add CPT
    if ($side === 'left') {
        $upline->left_ctp_for_matching = ($upline->left_ctp_for_matching ?? 0) + $ctpGained;
    } else {
        $upline->right_ctp_for_matching = ($upline->right_ctp_for_matching ?? 0) + $ctpGained;
    }
    $upline->save();

    // Active package check
    $activePackage = \App\Models\UserPackage::where('user_id', $upline->id)
        ->where('status', 'approved')
        ->latest('created_at')
        ->first();

    if (!$activePackage) {
        return $this->handleMatchingBonus($upline, $ctpGained, $generation + 1, $side);
    }

    $package = \App\Models\Package::find($activePackage->package_id);
    if (!$package) {
        return $this->handleMatchingBonus($upline, $ctpGained, $generation + 1, $side);
    }

    // Matching settings
    $map = [
        'standard'  => ['limit' => 20,  'percentage' => 7],
        'basic'     => ['limit' => 30, 'percentage' => 9],
        'classic'   => ['limit' => 40, 'percentage' => 12],
        'premium'   => ['limit' => 60, 'percentage' => 13],
        'executive' => ['limit' => 75, 'percentage' => 14],
        'vip'       => ['limit' => 100, 'percentage' => 15],
    ];
    $key = strtolower($package->packageName);
    $matchLimitPairs = $map[$key]['limit'] ?? 0;
    $matchPercentage = $map[$key]['percentage'] ?? 0;

    $left  = (float) ($upline->left_ctp_for_matching ?? 0);
    $right = (float) ($upline->right_ctp_for_matching ?? 0);
    $pairCPT = 16;

    if ($left < $pairCPT || $right < $pairCPT) {
        return $this->handleMatchingBonus($upline, $ctpGained, $generation + 1, $side);
    }

    $possiblePairs = (int) floor(min($left, $right) / $pairCPT);
    if ($possiblePairs <= 0) {
        return $this->handleMatchingBonus($upline, $ctpGained, $generation + 1, $side);
    }

    // Deduct CPT for all possible pairs
    $usedCPT = $possiblePairs * $pairCPT;
    $upline->left_ctp_for_matching  = max(0, $upline->left_ctp_for_matching  - $usedCPT);
    $upline->right_ctp_for_matching = max(0, $upline->right_ctp_for_matching - $usedCPT);
    $upline->save();

    // Daily limit check
    $dailyMatchedCount = \App\Models\Bonus::where('user_id', $upline->id)
        ->where('type', 'matching')
        ->whereDate('created_at', now())
        ->count();

    $remainingPairsAllowedToday = max(0, $matchLimitPairs - $dailyMatchedCount);

    $baseAmountPerPair = 24000;

    for ($i = 0; $i < $possiblePairs; $i++) {
        if ($i < $remainingPairsAllowedToday) {
            $amount = $baseAmountPerPair * ($matchPercentage / 100);
            $upline->withdraw_wallet_balance = ($upline->withdraw_wallet_balance ?? 0) + $amount;
            $upline->save();
        } else {
            $amount = 0; // reached daily limit, record bonus but no payout
        }

        \App\Models\Bonus::create([
            'user_id'     => $upline->id,
            'amount'      => $amount,
            'type'        => 'matching',
            'is_paid'     => 1,
            'description' => "Matching payout for 1 pair — CPT deducted {$pairCPT}",
            'is_approved' => false,
        ]);
    }

    $this->handleMatchingBonus($upline, $ctpGained, $generation + 1, $side);
}




public function handleMatchingBonuslastone(User $user, $ctpGained, $generation = 1, $side = null)
{
    \Log::info("==== [MATCHING START] ==== User: {$user->username} | Gained: {$ctpGained} CPT | Gen: {$generation}");

    // Stop at 9 generations
    if ($generation > 9 || !$user->parent_id) {
        return;
    }

    $upline = User::find($user->parent_id);
    if (!$upline) return;

    // Determine side for 1st generation
    if ($generation === 1) {
        $side = strtolower($user->position);
    }

    if (!in_array($side, ['left', 'right'])) {
        \Log::warning("Invalid side '{$side}' for user {$user->username}");
        return $this->handleMatchingBonus($upline, $ctpGained, $generation + 1, $side);
    }

    // Check sponsor structure (old function logic)
    $hasLeftSponsor = User::where('sponsor_id', $upline->id)->where('position', 'left')->exists();
    $hasRightSponsor = User::where('sponsor_id', $upline->id)->where('position', 'right')->exists();

    // Add CPT to only valid leg – old function logic
    if ($hasLeftSponsor && $hasRightSponsor) {
        if ($side === 'left')  $upline->left_ctp_for_matching += $ctpGained;
        if ($side === 'right') $upline->right_ctp_for_matching += $ctpGained;
    } elseif ($hasLeftSponsor && $side === 'left') {
        $upline->left_ctp_for_matching += $ctpGained;
    } elseif ($hasRightSponsor && $side === 'right') {
        $upline->right_ctp_for_matching += $ctpGained;
    } else {
        return $this->handleMatchingBonus($upline, $ctpGained, $generation + 1, $side);
    }

    $upline->save();

    $left  = $upline->left_ctp_for_matching;
    $right = $upline->right_ctp_for_matching;
    $pairCPT = 16;

    // Must have CPT to match
    if (!$hasLeftSponsor || !$hasRightSponsor || $left < $pairCPT || $right < $pairCPT) {
        return $this->handleMatchingBonus($upline, $ctpGained, $generation + 1, $side);
    }

    // Determine number of possible pairs
    $possiblePairs = floor(min($left, $right) / $pairCPT);

    // Fetch active package
    $activePackage = \App\Models\UserPackage::where('user_id', $upline->id)
        ->where('status', 'approved')
        ->latest('created_at')
        ->first();

    if (!$activePackage) {
        return $this->handleMatchingBonus($upline, $ctpGained, $generation + 1, $side);
    }

    $package = \App\Models\Package::find($activePackage->package_id);
    if (!$package) {
        return;
    }

    // Use LIMIT + PERCENTAGE from handleMatchingBonus1234()
    $map = [
        'standard'  => ['limit' => 20,  'percentage' => 7],
        'basic'     => ['limit' => 30,  'percentage' => 9],
        'classic'   => ['limit' => 40,  'percentage' => 12],
        'premium'   => ['limit' => 60,  'percentage' => 13],
        'executive' => ['limit' => 75,  'percentage' => 14],
        'vip'       => ['limit' => 100, 'percentage' => 15],
    ];

    $key = strtolower($package->packageName);
    $matchLimitPairs = $map[$key]['limit'] ?? 0;
    $matchPercentage = $map[$key]['percentage'] ?? 0;

    // Check how many pairs already paid today
    $dailyMatchedCount = \App\Models\Bonus::where('user_id', $upline->id)
        ->where('type', 'matching')
        ->whereDate('created_at', now())
        ->count();

    $remainingPairsAllowedToday = max(0, $matchLimitPairs - $dailyMatchedCount);

    // CPT MUST ALWAYS BE DEDUCTED
    $usedCPT = $possiblePairs * $pairCPT;

    $upline->left_ctp_for_matching  = max(0, $upline->left_ctp_for_matching  - $usedCPT);
    $upline->right_ctp_for_matching = max(0, $upline->right_ctp_for_matching - $usedCPT);
    $upline->save();

    $baseAmount = 24000;

    // Pay per pair
    for ($i = 0; $i < $possiblePairs; $i++) {

        if ($i < $remainingPairsAllowedToday) {
            // Pay bonus
            $amount = $baseAmount * ($matchPercentage / 100);

            $upline->withdraw_wallet_balance += $amount;
            $upline->save();
        } else {
            // Daily limit reached — no bonus paid
            $amount = 0;
        }

        // Record bonus for each pair
        \App\Models\Bonus::create([
            'user_id'     => $upline->id,
            'amount'      => $amount,
            'type'        => 'matching',
            'is_paid'     => 1,
            'description' => "Matching payout for 1 pair — CPT deducted {$pairCPT}",
            'is_approved' => false,
        ]);
    }

    // Continue recursion
    return $this->handleMatchingBonus($upline, $ctpGained, $generation + 1, $side);
}




public function handleMatchingBonus(User $user, $ctpGained, $generation = 1, $side = null)
{
    \Log::info("==== [MATCHING START] ==== User: {$user->username} | Gained: {$ctpGained} CPT | Gen: {$generation}");

    // Stop at 9 generations
    if ($generation > 9 || !$user->parent_id) {
        return;
    }

    // Parent/upline at this generation
    $upline = User::find($user->parent_id);
    if (!$upline) return;

    // Always derive placement side from the child relative to THIS upline
    // This ensures: 
    // - tester04 activity → tester02 left (because tester04.position == 'left')
    // - tester05 activity → tester02 right
    // - tester02 activity → tester01 left (because tester02.position == 'left')
    $currentSide = strtolower($user->position);

    if (!in_array($currentSide, ['left', 'right'])) {
        \Log::warning("Invalid side '{$currentSide}' for user {$user->username}");
        // Recurse upward anyway, preserving CPT flow (no add on invalid side)
        return $this->handleMatchingBonus($upline, $ctpGained, $generation + 1, $currentSide);
    }

    // Check sponsor structure (preserved)
    $hasLeftSponsor = User::where('sponsor_id', $upline->id)->where('position', 'left')->exists();
    $hasRightSponsor = User::where('sponsor_id', $upline->id)->where('position', 'right')->exists();

    // Add CPT strictly to the placement leg at this level,
    // but only if that leg has at least one sponsored user (preserved gate).
    // This naturally splits under the direct upline (tester02 receives left for tester04, right for tester05)
    // and flows to a single leg for higher uplines (tester01 receives only left because tester02.position == 'left').
    $added = false;
    if ($currentSide === 'left' && $hasLeftSponsor) {
        $upline->left_ctp_for_matching += $ctpGained;
        $added = true;
    } elseif ($currentSide === 'right' && $hasRightSponsor) {
        $upline->right_ctp_for_matching += $ctpGained;
        $added = true;
    }

    if (!$added) {
        \Log::info("🚫 No CPT added — {$upline->username} has no sponsored user on {$currentSide} leg.");
        // Continue upward with the upline as the child for the next level
        return $this->handleMatchingBonus($upline, $ctpGained, $generation + 1, $currentSide);
    }

    $upline->save();

    $left  = $upline->left_ctp_for_matching;
    $right = $upline->right_ctp_for_matching;
    $pairCPT = 16;

    // Must have CPT on both legs AND both legs sponsored to match (preserved)
    if (!$hasLeftSponsor || !$hasRightSponsor || $left < $pairCPT || $right < $pairCPT) {
        return $this->handleMatchingBonus($upline, $ctpGained, $generation + 1, $currentSide);
    }

    // Determine number of possible pairs
    $possiblePairs = floor(min($left, $right) / $pairCPT);

    // Fetch active package (preserved)
    $activePackage = \App\Models\UserPackage::where('user_id', $upline->id)
        ->where('status', 'approved')
        ->latest('created_at')
        ->first();

    if (!$activePackage) {
        return $this->handleMatchingBonus($upline, $ctpGained, $generation + 1, $currentSide);
    }

    $package = \App\Models\Package::find($activePackage->package_id);
    if (!$package) {
        return;
    }

    // Use LIMIT + PERCENTAGE (preserved map)
    $map = [
        'standard'  => ['limit' => 20,  'percentage' => 7],
        'basic'     => ['limit' => 30,  'percentage' => 9],
        'classic'   => ['limit' => 40,  'percentage' => 12],
        'premium'   => ['limit' => 60,  'percentage' => 13],
        'executive' => ['limit' => 75,  'percentage' => 14],
        'vip'       => ['limit' => 100, 'percentage' => 15],
    ];

    $key = strtolower($package->packageName);
    $matchLimitPairs = $map[$key]['limit'] ?? 0;
    $matchPercentage = $map[$key]['percentage'] ?? 0;

    // Check how many pairs already paid today (preserved)
    $dailyMatchedCount = \App\Models\Bonus::where('user_id', $upline->id)
        ->where('type', 'matching')
        ->whereDate('created_at', now())
        ->count();

    $remainingPairsAllowedToday = max(0, $matchLimitPairs - $dailyMatchedCount);

    // CPT MUST ALWAYS BE DEDUCTED for all computed pairs (preserved)
    $usedCPT = $possiblePairs * $pairCPT;

    $upline->left_ctp_for_matching  = max(0, $upline->left_ctp_for_matching  - $usedCPT);
    $upline->right_ctp_for_matching = max(0, $upline->right_ctp_for_matching - $usedCPT);
    $upline->save();

    $baseAmount = 24000;

    // Pay per pair with remaining daily cap (preserved)
    for ($i = 0; $i < $possiblePairs; $i++) {

        if ($i < $remainingPairsAllowedToday) {
            // Pay bonus
            $amount = $baseAmount * ($matchPercentage / 100);
            $upline->withdraw_wallet_balance += $amount;
            $upline->save();
        } else {
            // Daily limit reached — no bonus paid
            $amount = 0;
        }

        // Record bonus for each pair (preserved)
        \App\Models\Bonus::create([
            'user_id'     => $upline->id,
            'amount'      => $amount,
            'type'        => 'matching',
            'is_paid'     => 1,
            'description' => "Matching payout for 1 pair — CPT deducted {$pairCPT}",
            'is_approved' => false,
        ]);
    }

    // Continue recursion with upline as the child for the next level
    return $this->handleMatchingBonus($upline, $ctpGained, $generation + 1, null);
}










public function addCTPToUplines(User $user, int $ctpGained)
{
    $currentUpline = $user->parent_id;

    while ($currentUpline) {
        $upline = User::find($currentUpline);
        if (!$upline) break;

        $upline->increment('total_ctp', $ctpGained);
        $currentUpline = $upline->parent_id;
    }
}




public function distributeCtpAnd23TriggerMatching23(User $user, int $ctpGained)
{
    $currentUser = $user;
    $generation = 1;

    while ($currentUser->parent_id && $generation <= 10) {
        $upline = User::find($currentUser->parent_id);
        if (!$upline) break;

        $side = strtolower($currentUser->position); // 'left' or 'right'

        //  Add CPT to correct leg
        if ($side === 'left') {
            $upline->left_ctp_for_matching += $ctpGained;
        } elseif ($side === 'right') {
            $upline->right_ctp_for_matching += $ctpGained;
        }

        //  Add to total CPT
        $upline->total_ctp += $ctpGained;
        $upline->save();

        //  Trigger matching for this upline
        $this->handleMatchingBonus($upline, $ctpGained, $generation);

        $currentUser = $upline;
        $generation++;
    }

    //  Also trigger matching for the buyer
    $this->handleMatchingBonus($user, $ctpGained, 1);
}



 


public function addCTPTo20Uplines(User $user, int $ctpGained)
{
    $currentUplineId = $user->parent_id;
    $position = $user->position; // 'left' or 'right'
    $generation = 1;

    while ($currentUplineId && $generation <= 10) {
        $upline = User::find($currentUplineId);
        if (!$upline) break;

        //  Add to total CPT
        $upline->increment('total_ctp', $ctpGained);

        //  Add CPT to correct leg
        if ($position === 'left') {
            $upline->left_ctp_for_matching += $ctpGained;
        } elseif ($position === 'right') {
            $upline->right_ctp_for_matching += $ctpGained;
        }

        $upline->save();

        //  Trigger matching bonus
        $this->handleMatchingBonus($upline, $ctpGained, $generation);

        // Move up the tree
        $position = $upline->position; // get upline's position relative to their own parent
        $currentUplineId = $upline->parent_id;
        $generation++;
    }
}


public function payReferralBonus($user, float $amount, bool $isUpgrade = false, $previousPackageId = null): void
{
    $sponsor = $user->sponsor_id ? User::find($user->sponsor_id) : null;
    $levels = [21, 2, 1, 1]; // % per level
    $level = 0;

    \Log::info("==> Starting payReferralBonus for user ID {$user->id} | Amount: {$amount} | IsUpgrade: " . ($isUpgrade ? 'Yes' : 'No'));

    while ($sponsor && $level < 4) {
        $percentage = $levels[$level];

        // Skip non-direct upgrade
        if ($isUpgrade && $level > 0) {
            break;
        }

        // On registration: skip level 2-4 if sponsor has standard or no plan
        if (!$isUpgrade && $level > 0 && (empty($sponsor->user_plan) || strtolower($sponsor->user_plan) === 'standard')) {
            \Log::info("Skipping level " . ($level + 1) . " sponsor ID {$sponsor->id} due to plan: {$sponsor->user_plan}");
            $sponsor = $sponsor->sponsor_id ? User::find($sponsor->sponsor_id) : null;
            $level++;
            continue;
        }

        // Calculate bonus
        if ($isUpgrade && $level === 0) {
            $oldAmount = 0;
            if ($previousPackageId) {
                $old = Package::find($previousPackageId);
                $oldAmount = $old ? $old->price : 0;
            }
            $bonusAmount = ($amount - $oldAmount) * 22 / 100;
            $type = 'upgrade';
            $description = "Upgrade bonus from user {$user->username}";
        } else {
            $bonusAmount = $amount * ($percentage / 100);
            $type = 'referral';
            $description = "Referral bonus from level " . ($level + 1) . " user {$user->username}";
        }

        // Credit bonus
        $sponsor->withdraw_wallet_balance += $bonusAmount;
        $sponsor->save();

        // Save record
        Bonus::create([
            'user_id' => $sponsor->id,
            'amount' => $bonusAmount,
            'type' => $type,
            'is_paid' => '1',
            'description' => $description,
        ]);

        \Log::info("Paid {$type} bonus of ₦{$bonusAmount} to sponsor ID {$sponsor->id} (level " . ($level + 1) . ")");

        $sponsor = $sponsor->sponsor_id ? User::find($sponsor->sponsor_id) : null;
        $level++;
    }
}


public function saveSelectedProducts(Request $request, $package_id)
{
    $package    = Package::findOrFail($package_id);
    $user       = Auth::user();
    $maxBottles = (int) $package->bottle;
    $maxCpts    = (float) $package->cpts;

    $validator = Validator::make($request->all(), [
        'product'       => 'required|array',
        'product.*.id'  => 'required|exists:product,id',
        'product.*.qty' => 'nullable|integer|min:0',
    ]);



$validator->after(function ($validator) use ($request, $maxBottles, $maxCpts) {
    $rows = $request->product ?? [];
    $selected = array_filter($rows, fn ($r) => !empty($r['qty']) && (int)$r['qty'] > 0);

    if (count($selected) === 0) {
        $validator->errors()->add('product', 'Please select products with valid quantities.');
        return;
    }

    $totalQty  = 0;
    $totalCpts = 0.0;

    foreach ($selected as $index => $row) {
        $qty = (int) $row['qty'];
        $product = Product::find($row['id']);
        if (!$product) {
            $validator->errors()->add("product.$index.id", 'Invalid product selected.');
            continue;
        }

        $totalQty  += $qty;
        $totalCpts += ((float) $product->cpts) * $qty;
    }

    //  Enforce exact bottles (not less, not more)
    if ($totalQty !== $maxBottles) {
        $validator->errors()->add('product', "You must select exactly {$maxBottles} bottles; you selected {$totalQty}.");
    }

    //  Ensure CPTs do not exceed the package limit
    if ($totalCpts > $maxCpts) {
        $validator->errors()->add('product', "Total selected CPTs ({$totalCpts}) exceeds your package CPTs ({$maxCpts}).");
    }
});


    if ($validator->fails()) {
        return back()->withErrors($validator)->withInput();
    }

    DB::transaction(function () use ($request, $user, $package_id) {

        // Save items (use your existing table)
        foreach ($request->product as $row) {
            $qty = (int) ($row['qty'] ?? 0);
            if ($qty <= 0) continue;

            DB::table('package_product_orders')->insert([
                'user_id'    => $user->id,
                'package_id' => $package_id,
                'product_id' => $row['id'],
                'qty'        => $qty,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Flip the latest pending userpackage for this user+package to APPROVED (product selection done)
        $pendingUP = userpackage::where('user_id', $user->id)
            ->where('package_id', $package_id)
            ->where('package_order_status', 'pending')
            ->latest('id')
            ->first();

        if ($pendingUP) {
            $pendingUP->package_order_status = 'approved';
            $pendingUP->save();
        }
    });

    return redirect()->route('user.package')
        ->with('success', 'Products selected successfully. Your package order is now complete.');
}
















}
