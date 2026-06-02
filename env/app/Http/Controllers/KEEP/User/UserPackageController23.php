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



 
 



public function handleMatchingBonus_copy(User $user, $ctpGained, $generation = 1, $side = null)
{
    \Log::info("==> handleMatchingBonus() for user ID: {$user->id}, gained CTP: {$ctpGained}, gen {$generation}, side {$side}");

    if ($generation > 10) {
        \Log::info("Reached 10th generation. Stopping.");
        return;
    }

    if (!$user->parent_id) {
        \Log::info("User has no upline. Stopping here.");
        return;
    }

    $upline = User::find($user->parent_id);
    if (!$upline) {
        \Log::info("Upline not found for parent_id: {$user->parent_id}");
        return;
    }

    // Skip if upline is inactive
    if ($upline->status !== 'active') {
        \Log::info("Upline ID {$upline->id} is inactive. Skipping bonus.");
        return;
    }

    // --- Determine which side this activity belongs to ---
    if ($generation === 1) {
        $side = strtolower($user->position);
    }

    if (!in_array($side, ['left', 'right'])) {
        \Log::info("Invalid side '{$side}' for user ID {$user->id}. Skipping.");
        return;
    }

    // --- Add CTP to correct side ---
    if ($side === 'left') {
        $upline->left_ctp_for_matching += $ctpGained;
        \Log::info("Added {$ctpGained} to LEFT CTP of upline ID {$upline->id}");
    } else {
        $upline->right_ctp_for_matching += $ctpGained;
        \Log::info("Added {$ctpGained} to RIGHT CTP of upline ID {$upline->id}");
    }

    // --- Active package check ---
    $activePackage = userpackage::where('user_id', $upline->id)
        ->where('status', 'approved')
        ->latest('created_at')
        ->first();

    if ($activePackage) {
        $package = Package::find($activePackage->package_id);

        if ($package) {
            $settings = getMatchingSettings(strtolower($package->packageName));
            $matchLimit = $settings['limit'] ?? 0;
            $matchPercentage = $settings['percentage'] ?? 0;

            $dailyMatched = Bonus::where('user_id', $upline->id)
                ->where('type', 'matching')
                ->whereDate('created_at', \Carbon\Carbon::today())
                ->count();

            $remainingMatches = $matchLimit - $dailyMatched;

            while (
                $remainingMatches > 0 &&
                $upline->left_ctp_for_matching >= 16 &&
                $upline->right_ctp_for_matching >= 16
            ) {
                $upline->left_ctp_for_matching -= 16;
                $upline->right_ctp_for_matching -= 16;

                $bonusAmount = 24000 * ($matchPercentage / 100);
                $upline->withdraw_wallet_balance += $bonusAmount;

                Bonus::create([
                    'user_id' => $upline->id,
                    'amount' => $bonusAmount,
                    'type' => 'matching',
                    'is_paid' => '1',
                    'description' => 'Matching bonus from left and right CTP activity',
                    'is_approved' => false,
                ]);

                \Log::info("Matching bonus of ₦{$bonusAmount} paid to upline ID {$upline->id}");
                $remainingMatches--;
            }

            if ($remainingMatches <= 0) {
                \Log::info("Match limit reached. Resetting remaining CTPs.");
                $upline->left_ctp_for_matching = 0;
                $upline->right_ctp_for_matching = 0;
            }
        }
    }

    $upline->save();

    // --- Recurse upwards with same SIDE info ---
    \Log::info("Recursively checking next upline...");
    $this->handleMatchingBonus($upline, $ctpGained, $generation + 1, $side);
}



public function handleMatchingBonus(User $user, $ctpGained, $generation = 1)
{
    \Log::info("==== [MATCHING START] ==== User: {$user->username} | Gained: {$ctpGained} CPT | Gen: {$generation}");

    if ($generation > 10) return; // stop after 10 generations
    if (!$user->parent_id) return; // no more uplines

    $upline = User::find($user->parent_id);
    if (!$upline) return;

    // Determine whether this user is LEFT or RIGHT child of upline
    $side = strtolower($user->position);
    if (!in_array($side, ['left', 'right'])) {
        \Log::warning("Invalid side '{$side}' for user {$user->username}");
        return;
    }

    // === 1️⃣ Add CPTs to the correct leg ===
    if ($side === 'left') {
        $upline->left_ctp_for_matching += $ctpGained;
    } else {
        $upline->right_ctp_for_matching += $ctpGained;
    }

    $upline->save();

    \Log::info("Upline {$upline->username}: LEFT={$upline->left_ctp_for_matching}, RIGHT={$upline->right_ctp_for_matching}");

    // === 2️⃣ Attempt Matching ===
    $left = $upline->left_ctp_for_matching;
    $right = $upline->right_ctp_for_matching;

    // Minimum CPT needed per pair (you can change 16)
    $pairCPT = 16;

    $pairs = floor(min($left, $right) / $pairCPT);

    if ($pairs > 0) {
        // Find active package to get settings
        $activePackage = \App\Models\userpackage::where('user_id', $upline->id)
            ->where('status', 'approved')
            ->latest('created_at')
            ->first();

        if ($activePackage) {
            $package = \App\Models\Package::find($activePackage->package_id);
            $settings = getMatchingSettings(strtolower($package->packageName));

            $matchPercentage = $settings['percentage'] ?? 0;
            $matchLimit = $settings['limit'] ?? 0;

            // Daily count
            $dailyMatched = \App\Models\Bonus::where('user_id', $upline->id)
                ->where('type', 'matching')
                ->whereDate('created_at', now())
                ->count();

            $availablePairs = max($matchLimit - $dailyMatched, 0);
            $pairs = min($pairs, $availablePairs);

            if ($pairs > 0) {
                $usedCPT = $pairs * $pairCPT;

                $upline->left_ctp_for_matching -= $usedCPT;
                $upline->right_ctp_for_matching -= $usedCPT;

                // Prevent negatives
                $upline->left_ctp_for_matching = max(0, $upline->left_ctp_for_matching);
                $upline->right_ctp_for_matching = max(0, $upline->right_ctp_for_matching);

                // Calculate bonus
                $bonusAmount = 24000 * ($matchPercentage / 100) * $pairs;
                $upline->withdraw_wallet_balance += $bonusAmount;
                $upline->save();

                \App\Models\Bonus::create([
                    'user_id' => $upline->id,
                    'amount' => $bonusAmount,
                    'type' => 'matching',
                    'is_paid' => 1,
                    'description' => "Matched {$pairs} pair(s) ({$usedCPT} CPT used)",
                    'is_approved' => false,
                ]);

                \Log::info("✅ Matching bonus: {$upline->username} matched {$pairs} pair(s), earned ₦{$bonusAmount}");
            }
        }
    } else {
        \Log::info("No match yet for {$upline->username} — Left: {$left}, Right: {$right}");
    }

    // === 3️⃣ Recurse upwards ===
    $this->handleMatchingBonus($upline, $ctpGained, $generation + 1);
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
    'product'       => 'required|array',
    'product.*.id'  => 'required|exists:product,id',
    'product.*.qty' => 'nullable|integer|min:0',
]);

// Stop here if validation fails
if ($validator->fails()) {
    return back()->withErrors($validator)->withInput();
}

//  Now it's safe to access the package
$newPackage = Package::find($request->package_id);
if (!$newPackage) {
    return back()->with('error', 'Selected package not found.');
}

$maxBottles = (int) $newPackage->bottle;
$maxCpts    = (float) $newPackage->cpts;


    // Validate product selection, bottles & CPTs
    $validator->after(function ($validator) use ($request, $maxBottles, $maxCpts) {
        $rows = $request->product ?? [];
        $selected = array_filter($rows, fn($r) => !empty($r['qty']) && (int)$r['qty'] > 0);

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

    // --- Get previous package (for upgrades)
    $previousPackage = userpackage::where('user_id', $user->id)
        ->where('status', 'approved')
        ->latest()
        ->first();

    if ($previousPackage && $newPackage->id <= $previousPackage->package_id) {
        return back()->with('error', 'You can only upgrade to a higher package.');
    }

    $amountToPay = $newPackage->price;
    $ctpToAdd    = $newPackage->cpts;

    $isUpgrade = false;
    $previousPackageId = null;

    if ($previousPackage) {
        $isUpgrade = true;
        $previousPackageId = $previousPackage->package_id;
        $previous = Package::find($previousPackageId);

        $amountToPay -= $previous->price;
        $ctpToAdd    -= $previous->cpts;
    }

    $amountToPay += ($newPackage->apc * $newPackage->bottle);

    // ====================================================
    // WALLET PAYMENT
    // ====================================================
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

            // Update CPT tracking
            $user->total_ctp           += $ctpToAdd;
            $user->p_c_cpts            += $ctpToAdd;
            $user->current_p_c_cpts    += $ctpToAdd;
            $user->current_c_cpts      += $ctpToAdd;
            $user->user_plan           = $newPackage->packageName;
            $user->user_rank           = $user->user_rank ?? 'Regular';
            $user->save();

            $userpackage = userpackage::create([
                'user_id'             => $user->id,
                'package_id'          => $newPackage->id,
                'previous_package_id' => $previousPackageId,
                'amount_paid'         => $amountToPay,
                'payment_method'      => 'wallet',
                'status'              => 'approved',
                'package_order_status'=> 'approved',
            ]);

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

            \addCtpToUplines($user->id, $ctpToAdd);
            $this->handleMatchingBonus($user, $ctpToAdd);
            $this->payReferralBonus($user, $newPackage->price, $isUpgrade, $previousPackageId);
        });

        return redirect()->route('user.package')->with('success', 'Package purchased successfully using wallet.');
    }

    // ====================================================
    // BANK PAYMENT
    // ====================================================
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
                'user_id'             => $user->id,
                'package_id'          => $newPackage->id,
                'previous_package_id' => $previousPackageId,
                'amount_paid'         => $request->amount,
                'payment_method'      => 'bank',
                'acctName'            => $request->acctName,
                'bankName'            => $request->bankName,
                'payment_proof'       => $paymentProofPath,
                'status'              => 'pending',
                'package_order_status'=> 'pending',
            ]);

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
        });

        return redirect()->route('user.package')->with('success', 'Package submitted for approval. Awaiting admin confirmation.');
    }

    // ====================================================
    // ONLINE PAYMENT — PAYSTACK
    // ====================================================
   // --- ONLINE PAYMENT --- //
if ($request->payment_method === 'online') {
    try {
        \Log::info('Incoming online purchase', $request->all());

        $request->validate([
            'paystack_reference' => 'required|string',
            'package_id' => 'required|exists:package,id', // or `packages` if plural
            'product' => 'required|array',
            'product.*.id' => 'required|exists:product,id',
            'product.*.qty' => 'nullable|integer|min:0',
        ]);

        $user = auth()->user();
        $reference = $request->paystack_reference;
        $packageId = $request->package_id;
        $productSelection = $request->product;

        // Fetch the package
        $newPackage = Package::find($packageId);
        if (!$newPackage) {
            \Log::error("Package not found for ID: {$packageId}");
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

        if (!$verifyResponse->successful()) {
            \Log::error('Paystack verification failed', ['response' => $verifyResponse->body()]);
            return response()->json([
                'status' => 'error',
                'message' => 'Unable to verify payment.',
                'response' => $verifyResponse->body(),
            ], 400);
        }

        $verify = $verifyResponse->json();
        if (($verify['data']['status'] ?? '') !== 'success') {
            \Log::error('Payment not successful', ['verify' => $verify]);
            return response()->json([
                'status' => 'error',
                'message' => 'Payment verification failed.',
                'response' => $verify,
            ], 400);
        }

        // --- Determine upgrade details
        $previousPackage = userpackage::where('user_id', $user->id)
            ->where('status', 'approved')
            ->latest()
            ->first();

        $isUpgrade = false;
        $previousPackageId = null;
        $ctpToAdd = $newPackage->cpts;
        $amountPaid = $verify['data']['amount'] / 100;

        if ($previousPackage && $newPackage->id > $previousPackage->package_id) {
            $isUpgrade = true;
            $previousPackageId = $previousPackage->package_id;
            $previous = Package::find($previousPackageId);
            $ctpToAdd -= $previous->cpts;
        }

        // --- Process transaction safely
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

            // MLM Logic
            \addCtpToUplines($user->id, $ctpToAdd);
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


}



















}
