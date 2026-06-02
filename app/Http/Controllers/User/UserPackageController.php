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
use App\Models\incentives;
use App\Models\incentive_settings;






 

class userpackageController extends Controller
{


 

public function selectProducts($package_id)
{
    $package = Package::findOrFail($package_id);
    $products = Product::all();  
    return view('user.package-products', compact('package', 'products'));
}

 





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
        ->latest('id')
        ->first();

    $finalPrice = $package->price;

    // Default values (no previous package)
    $effectiveBottle = $package->bottle;
    $effectiveCpt    = $package->cpts;

    if ($previous) {
        $previousPackage = Package::find($previous->package_id);
        if ($previousPackage) {
            // Deduct previous package price
            $finalPrice = max($package->price - $previousPackage->price, 0);

            // Deduct previous bottle and CPT
            $effectiveBottle = max($package->bottle - ($previous->previous_bottle ?? 0), 0);
            $effectiveCpt    = max($package->cpts - ($previous->previous_package_cpt ?? 0), 0);
        }
    }

    // APC total based on effective bottles
    $apcTotal = ($package->apc ?? 0) * $effectiveBottle;

    $products = Product::all();

    return view('user.purchase-package', compact(
        'package',
        'user',
        'finalPrice',
        'products',
        'apcTotal',
        'effectiveBottle',
        'effectiveCpt'
    ));
}




  



public function purchase(Request $request)
{
    $user = Auth::user();
    
$transactionId = $user->id . '-' . now()->timestamp;
    // ================= VALIDATION =================
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

    // ================= PACKAGE =================
    $newPackage = Package::find($request->package_id);
    if (!$newPackage) {
        return back()->with('error', 'Package not found.');
    }

    // ================= PREVIOUS PACKAGE =================
    $previousPackage = userpackage::where('user_id', $user->id)
        ->where('status', 'approved')
        ->latest()
        ->first();

    $previous = null;
    $isUpgrade = false;
    $previousPackageId = null;

    if ($previousPackage) {
        $previous = Package::find($previousPackage->package_id);

        if ($newPackage->id <= $previousPackage->package_id) {
            return back()->with('error', 'You can only upgrade to a higher package.');
        }

        $isUpgrade = true;
        $previousPackageId = $previousPackage->package_id;
    }

    // ================= EFFECTIVE VALUES =================
    $effectiveBottle = $newPackage->bottle;
    $effectiveCpt    = $newPackage->cpts;

    if ($previousPackage) {
        $effectiveBottle = max($newPackage->bottle - ($previousPackage->previous_bottle ?? 0), 0);
        $effectiveCpt    = max($newPackage->cpts - ($previousPackage->previous_package_cpt ?? 0), 0);
    }

    // ================= PRODUCT VALIDATION =================
    $validator->after(function ($validator) use ($request, $effectiveBottle, $effectiveCpt) {

        $selected = array_filter($request->product ?? [], fn($r) => !empty($r['qty']) && $r['qty'] > 0);

        if (count($selected) === 0) {
            $validator->errors()->add('product', 'Select valid products.');
            return;
        }

        $totalQty = 0;
        $totalCpts = 0;

        foreach ($selected as $row) {
            $product = Product::find($row['id']);
            if (!$product) continue;

            $qty = (int)$row['qty'];
            $totalQty += $qty;
            $totalCpts += $product->cpts * $qty;
        }

        if ($totalQty != $effectiveBottle) {
            $validator->errors()->add('product', "Must select {$effectiveBottle} bottles.");
        }

        if (round($totalCpts,2) != round($effectiveCpt,2)) {
            $validator->errors()->add('product', "CPT must equal {$effectiveCpt}.");
        }
    });

    if ($validator->fails()) {
        return back()->withErrors($validator)->withInput();
    }

    // ================= AMOUNT CALCULATION =================
    $packageAmount = $newPackage->price;

    if ($isUpgrade && $previous) {
        $packageAmount = max($newPackage->price - $previous->price, 0); //  FIXED
    }

    // TOTAL PAYMENT (includes APC)
    $amountToPay = $packageAmount + ($newPackage->apc * $newPackage->bottle);

    $ctpToAdd = $effectiveCpt;

    // ================= WALLET =================
    if ($request->payment_method === 'wallet') {

        if ($request->transaction_pin !== $user->transaction_pin) {
            return back()->withErrors(['transaction_pin'=>'Invalid PIN']);
        }

        if ($user->deposit_wallet_balance < $amountToPay) {
            return back()->with('error','Insufficient balance');
        }

        DB::transaction(function () use ($user,$newPackage,$amountToPay,$ctpToAdd,$request,$isUpgrade,$previousPackageId,$packageAmount,$transactionId) {

            // Deduct
            $user->deposit_wallet_balance -= $amountToPay;

            // ADD CPT (not overwrite)
            $user->total_ctp        += $ctpToAdd;
            $user->p_c_cpts         += $ctpToAdd;
            $user->current_p_c_cpts += $ctpToAdd;
            $user->current_c_cpts   += $ctpToAdd;

            $user->status = 'active';
            $user->user_plan = $newPackage->packageName;
            $user->save();

            // Bonuses
         $this->addCTPToUplines($user, $ctpToAdd, $isUpgrade, $previousPackageId, $transactionId);
$this->handleMatchingBonus($user, $ctpToAdd, 1, $isUpgrade, $previousPackageId, $transactionId);
$this->payReferralBonus($user, $packageAmount, $isUpgrade, $previousPackageId, $transactionId);


 


            // Order
            $order = userpackage::create([
                'user_id'=>$user->id,
                'package_id'=>$newPackage->id,
                'previous_package_id'=>$previousPackageId,
                'previous_package_cpt'=>$newPackage->cpts,
                'previous_bottle'=>$newPackage->bottle,
                'transaction_id' => $transactionId,
                'amount_paid'=>$amountToPay,
                'payment_method'=>'wallet',
                'status'=>'approved',
                'package_order_status'=>'approved',
            ]);

            foreach ($request->product as $row) {
                $qty = (int)($row['qty'] ?? 0);
                if ($qty <= 0) continue;

                DB::table('package_product_orders')->insert([
                    'user_id'=>$user->id,
                    'package_id'=>$newPackage->id,
                    'product_id'=>$row['id'],
                    'package_order_id'=>$order->id,
                    'qty'=>$qty,
                    'created_at'=>now(),
                    'updated_at'=>now(),
                ]);
            }
        });

        return redirect()->route('user.package')->with('success','Wallet purchase successful');
    }

    // ================= BANK =================
    if ($request->payment_method === 'bank') {

        if (!$request->hasFile('payment_proof')) {
            return back()->with('error','Upload proof');
        }

        $file = $request->file('payment_proof');
        $name = time().'_'.$file->getClientOriginalName();
        $file->move(public_path('payment_proofs'),$name);

        $order = userpackage::create([
            'user_id'=>$user->id,
            'package_id'=>$newPackage->id,
            'previous_package_id'=>$previousPackageId,
            'previous_package_cpt'=>$newPackage->cpts,
            'previous_bottle'=>$newPackage->bottle,
            'transaction_id' => $transactionId,
            'amount_paid'=>$amountToPay,
            'payment_method'=>'bank',
            'acctName'=>$request->acctName,
            'bankName'=>$request->bankName,
            'payment_proof'=>'payment_proofs/'.$name,
            'status'=>'pending',
            'package_order_status'=>'pending',
        ]);

        foreach ($request->product as $row) {
            $qty = (int)($row['qty'] ?? 0);
            if ($qty <= 0) continue;

            DB::table('package_product_orders')->insert([
                'user_id'=>$user->id,
                'package_id'=>$newPackage->id,
                'product_id'=>$row['id'],
                'package_order_id'=>$order->id,
                'qty'=>$qty,
                'created_at'=>now(),
                'updated_at'=>now(),
            ]);
        }

        return redirect()->route('user.package')->with('success','Awaiting approval');
    }

   // === ONLINE PAYMENT — PAYSTACK ===
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

        //  Verify Paystack payment
        $verifyResponse = Http::withOptions(['verify' => false])
            ->withHeaders([
                'Authorization' => 'Bearer ' . env('PAYSTACK_SECRET_KEY'),
                'Accept' => 'application/json',
            ])
            ->get("https://api.paystack.co/transaction/verify/{$reference}");

        $verifyData = $verifyResponse->json();

        if (!$verifyResponse->successful() || ($verifyData['data']['status'] ?? '') !== 'success') {
            \Log::error('Paystack verification failed', [
                'reference' => $reference,
                'response' => $verifyResponse->body()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Payment verification failed.',
            ], 400);
        }

        $amountPaid = ($verifyData['data']['amount'] ?? 0) / 100;

        //  CPT Calculation
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

            $previousPackageData = Package::find($previousPackageId);

            if ($previousPackageData) {
                $ctpToAdd = max($newPackage->cpts - $previousPackageData->cpts, 0);
            }
        }

        //  Update user CPTs
        $user->total_ctp += $ctpToAdd;
        $user->p_c_cpts += $ctpToAdd;
        $user->current_p_c_cpts += $ctpToAdd;
        $user->current_c_cpts += $ctpToAdd;

        DB::transaction(function () use ($user, $newPackage, $previousPackageId, $amountPaid, $ctpToAdd, $productSelection, $isUpgrade, $transactionId) {

            // Normalize rank
            $incentiveRanks = DB::table('incentive_settings')->pluck('rank')->toArray();
            $rankToSet = in_array($user->user_rank, $incentiveRanks) ? $user->user_rank : 'Regular';

            // Update user
            $user->update([
                'status' => 'active',
                'user_rank' => $rankToSet,
                'user_plan' => $newPackage->packageName,
            ]);

                 $this->addCTPToUplines($user, $ctpToAdd, $isUpgrade, $previousPackageId, $transactionId);
$this->handleMatchingBonus($user, $ctpToAdd, 1, $isUpgrade, $previousPackageId, $transactionId);

 
            // Incentive check
            if ($user->parent_id) {
                $parent = User::find($user->parent_id);
                if ($parent) {
                    $this->evaluateUserIncentives($parent);
                }
            }

            // Create package order
            $userPackage = userpackage::create([
                'user_id' => $user->id,
                'package_id' => $newPackage->id,
                'previous_package_id' => $previousPackageId,
                'previous_package_cpt' => $newPackage->cpts,
                'previous_bottle' => $newPackage->bottle,
                'transaction_id' => $transactionId,
                'amount_paid' => $amountPaid,
                'payment_method' => 'online',
                'status' => 'approved',
                'package_order_status' => 'approved',
            ]);

            // Save products
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

            //  FIXED: Referral bonus calculation (NO APC, correct upgrade diff)
            $packageAmount = $newPackage->price;

            if ($isUpgrade && $previousPackageId) {
                $previousPackageData = Package::find($previousPackageId);

                if ($previousPackageData) {
                    $packageAmount = max($newPackage->price - $previousPackageData->price, 0);
                }
            }

            // Referral bonus
$this->payReferralBonus($user, $packageAmount, $isUpgrade, $previousPackageId, $transactionId);        });

        return response()->json([
            'status' => 'success',
            'message' => 'Package purchased successfully!',
            'redirect_url' => route('user.dashboard'),
        ]);

    } catch (\Exception $e) {

        \Log::error('Online purchase error', [
            'exception' => $e
        ]);

        return response()->json([
            'status' => 'error',
            'message' => 'Unexpected error occurred. Please try again.',
            'error' => $e->getMessage(),
        ], 500);
    }
}

}

 

public function payReferralBonus($user, float $packageAmount, bool $isUpgrade = false, $previousPackageId = null, $transactionId = null): void
{
    //  Prevent invalid bonus
    if ($packageAmount <= 0) {
        return;
    }

    $sponsor = $user->sponsor_id ? User::find($user->sponsor_id) : null;

    $levels = [21, 2, 1, 1];
    $level = 0;


while ($sponsor && $level < 4) {

    // Upgrade: only level 1
    if ($isUpgrade && $level > 0) {
        break;
    }

   // Skip inactive or muted sponsors
if (
    !$isUpgrade &&
    $level > 0 &&
    (
        empty($sponsor->user_plan) ||
        strtolower($sponsor->user_plan) === 'standard' ||
        $sponsor->is_muted == 1
    )
) {
    $sponsor = $sponsor->sponsor_id ? User::find($sponsor->sponsor_id) : null;
    $level++;
    continue;
}


    // Skip sponsors whose status is pending
    if (strtolower($sponsor->status) === 'pending') {
        $sponsor = $sponsor->sponsor_id ? User::find($sponsor->sponsor_id) : null;
        $level++;
        continue;
    }

    $percentage = $levels[$level];

    if ($isUpgrade && $level === 0) {
        $bonusAmount = $packageAmount * 22 / 100;
        $type = 'upgrade';
        $description = "Upgrade bonus from user {$user->username}";
    } else {
        $bonusAmount = $packageAmount * ($percentage / 100);
        $type = 'referral';
        $description = "Referral bonus level " . ($level + 1);
    }

    // Extra safety
    if ($bonusAmount > 0) {
        $sponsor->increment('withdraw_wallet_balance', $bonusAmount);

        // Credit record for the sponsor (receiver)
        Bonus::create([
            'user_id'        => $sponsor->id,
            'amount'         => $bonusAmount,
            'source_user_id' => $user->id,
            'transaction_id' => $transactionId,
            'type'           => $type,
            'status'         => 'credit',
            'description'    => "{$description} to {$sponsor->username}",
        ]);

        // Debit record for the payer (buyer)
        Bonus::create([
            'user_id'        => $user->id,
            'amount'         => $bonusAmount,
            'source_user_id' => $sponsor->id,
            'transaction_id' => $transactionId,
            'type'           => $type,
            'status'         => 'debit',
            'description'    => "{$description} paid to {$sponsor->username}",
        ]);
    }

    $sponsor = $sponsor->sponsor_id ? User::find($sponsor->sponsor_id) : null;
    $level++;
}

}




public function handleMatchingBonus(User $user, $ctpGained, $generation = 1, $isUpgrade = false, $previousPackageId = null, $transactionId = null)
{
    // Stop condition
    if ($generation > 9 || !$user->sponsor_id) {
        return;
    }

    $upline = User::find($user->sponsor_id);
    if (!$upline) return;

    //  Ensure correct CPT is used for upgrade (safety)
    if ($isUpgrade && $previousPackageId) {
        $previous = \App\Models\Package::find($previousPackageId);
        if ($previous) {
            $ctpGained = max($ctpGained, 0); // already calculated, just safety
        }
    }

    // Skip inactive uplines
    if ($upline->status !== 'active' || $upline->is_muted == 1) {
        $this->handleMatchingBonus($upline, $ctpGained, $generation + 1, $isUpgrade, $previousPackageId);
        return;
    }

  

    // Determine side relative to THIS upline via binary tree
    $currentSide = $this->getSideRelativeToAncestor($upline, $user);

    if (!in_array($currentSide, ['left', 'right'])) {
        return; // Not inside this sponsor's binary subtree
    }

    //  Add ONLY effective CPT (already corrected before calling)
    if ($currentSide === 'left') {
        $upline->increment('left_ctp_for_matching', $ctpGained);
    } elseif ($currentSide === 'right') {
        $upline->increment('right_ctp_for_matching', $ctpGained);
    }

    $upline->refresh();

    // =========================
    // MATCHING BONUS LOGIC
    // =========================
    $left  = $upline->left_ctp_for_matching;
    $right = $upline->right_ctp_for_matching;

    $pairCPT = 16;

    if ($left >= $pairCPT && $right >= $pairCPT) {

        $possiblePairs = floor(min($left, $right) / $pairCPT);
        $usedCPT       = $possiblePairs * $pairCPT;

        // Deduct used CPT
        $upline->decrement('left_ctp_for_matching', $usedCPT);
        $upline->decrement('right_ctp_for_matching', $usedCPT);

        // Get active package
        $activePackage = \App\Models\userpackage::where('user_id', $upline->id)
            ->where('status', 'approved')
            ->latest('created_at')
            ->first();

        if ($activePackage) {
            $package = \App\Models\Package::find($activePackage->package_id);

            if ($package) {

                // Matching settings
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

                // Today's matching count
                $dailyMatchedCount = \App\Models\Bonus::where('user_id', $upline->id)
                    ->where('type', 'matching')
                    ->whereDate('created_at', now())
                    ->count();

                $remainingPairsAllowedToday = max(0, $matchLimitPairs - $dailyMatchedCount);

                $baseAmount = 24000;

                for ($i = 0; $i < $possiblePairs; $i++) {

                    $amount = 0;

                    //  Pay only within daily cap
                    if ($i < $remainingPairsAllowedToday) {
                        $amount = $baseAmount * ($matchPercentage / 100);
                        $upline->increment('withdraw_wallet_balance', $amount);
                    }

                     // Always generate a custom transaction ID directly

                
                    // Save bonus record
               // Credit record for the upline (receiver)
// Credit record for the upline (receiver)
\App\Models\Bonus::create([
    'user_id'        => $upline->id,       // receiver
    'amount'         => $amount,
    'source_user_id' => $user->id,         // payer
    'transaction_id' => $transactionId,
    'type'           => 'matching',
    'status'        => 'credit',                // credit
    'description'    => "Matching bonus for 1 pair — CPT deducted {$pairCPT} from {$user->username} to {$upline->username}",
    'is_approved'    => false,
]);

// Debit record for the payer (buyer)
\App\Models\Bonus::create([
    'user_id'        => $user->id,         // payer
    'amount'         => $amount,
    'source_user_id' => $upline->id,       // receiver
    'transaction_id' => $transactionId,
    'type'           => 'matching',
    'status'        => 'debit',                // debit
    'description'    => "Matching bonus for 1 pair — CPT deducted {$pairCPT} paid to {$upline->username}",
    'is_approved'    => false,
]);


                }
            }
        }
    }

    // =========================
    // MOVE UP THE TREE
    // =========================
    $this->handleMatchingBonus($upline, $ctpGained, $generation + 1, $isUpgrade, $previousPackageId, $transactionId);
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

    $leftLegTotal  = $user->downline_left_cpt ?? 0;
    $rightLegTotal = $user->downline_right_cpt ?? 0;

    $totalCtp = $leftLegTotal + $rightLegTotal;

    $weakerLeg = min($leftLegTotal, $rightLegTotal);

    foreach ($ranks as $r) {

        $requiredWeakerLeg =
            ($r->min_lesser_leg_percent / 100)
            * $r->required_ctp;

        // Total CPT check
        if ($totalCtp < $r->required_ctp) {
            continue;
        }

        // Weaker leg check
        if ($weakerLeg < $requiredWeakerLeg) {
            continue;
        }

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
