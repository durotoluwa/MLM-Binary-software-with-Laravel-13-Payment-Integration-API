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

class memberPackageController extends Controller
{
   


public function showmemberpackage()
    {
        return view('user.memberpackage');
    }

    // MemberPackageController.php
public function searchMember(Request $request)
{
    $validated = $request->validate([
        'member' => 'required|string|exists:users,username',
    ]);

    $user = User::where('username', $validated['member'])->first();

    if (!$user) {
        return back()->with('error', 'Username not found ❌');
    }

 if ($user && !in_array($user->status, ['active', 'pending'])) {
    return back()->with('error', 'Username found, but user is not active or pending. You cannot buy a package for a user that is inactive ❌');
}


    // If user exists and is active, proceed
    return redirect()->route('member.package.select', ['username' => $validated['member']])
                     ->with('success', 'Username found ');
}




public function checkUsername(Request $request)
{
    $request->validate([
        'member' => 'required|string',
    ]);

    $user = User::where('username', $request->member)->first();

    if (!$user) {
        return response()->json([
            'exists' => false,
            'message' => 'Username not found ❌',
        ]);
    }

if ($user && !in_array($user->status, ['active', 'pending'])) {
    return response()->json([
        'exists' => false,
        'message' => 'Username found, but user is not active or pending. You cannot buy a package for a user that is inactive ❌',
    ]);
}


    return response()->json([
        'exists' => true,
        'message' => 'Username found ',
    ]);
}



public function packagePage($username)
{
    // Find the user by username
    $user = \App\Models\User::where('username', $username)->firstOrFail();

    // Define rank order
    $rank = ['standard','basic','classic','premium','executive','vip'];

    // Current plan index (or -1 if none)
    $currentIndex = -1;
    if ($user && $user->user_plan) {
        $normalized = strtolower(trim($user->user_plan));
        $idx = array_search($normalized, $rank);
        $currentIndex = ($idx === false) ? -1 : $idx;
    }

    // All packages sorted by rank
    $all = \App\Models\Package::all()
        ->sortBy(fn($p) => array_search(strtolower(trim($p->packageName)), $rank))
        ->values();

    // Find the user's last approved package and its price
    $lastPackagePrice = 0.0;
    $lastUserPkg = \App\Models\userpackage::where('user_id', $user->id)
        ->where('status', 'approved')
        ->latest('id')
        ->first();

    if ($lastUserPkg) {
        $prevPkg = \App\Models\Package::find($lastUserPkg->package_id);
        if ($prevPkg) {
            $lastPackagePrice = (float) $prevPkg->price;
        }
    }

    return view('user.memberpackage_list', compact('all','rank','currentIndex','lastPackagePrice','user'));
}



    
public function showPackageSelection()
    {
        $packages = Package::all();
        return view('user.package_selection', compact('packages'));
    }


    public function showMemberPurchaseForm($userId, $packageId)
    {
        $package = Package::findOrFail($packageId);
        $user    = User::findOrFail($userId); // target member
    
        // Check if this member already purchased this package
        $existing = userpackage::where('user_id', $user->id)
            ->where('package_id', $packageId)
            ->first();
    
        if ($existing) {
            return redirect()->back()->with('error', 'This member has already purchased this package.');
        }
    
        // Get the member's last purchased package (previous upgrade)
        $previous = userpackage::where('user_id', $user->id)
            ->latest('id')
            ->first();
    
        $lastPackagePrice = 0;
        $isUpgrade        = false;
        $bottleCount      = $package->bottle;
        $currentCpt       = $package->cpts;
        $currentApcTotal  = 0;
    
        if ($previous) {
            $isUpgrade = true;
    
            $previousPackage = Package::find($previous->package_id);
            if ($previousPackage) {
                $lastPackagePrice = $previousPackage->price ?? 0;
    
                // Deduct previous bottles
                $prevBottle  = $previous->previous_bottle ?? 0;
                $bottleCount = max($package->bottle - $prevBottle, 0);
    
                // Deduct previous CPT
                $prevCpt     = $previous->previous_package_cpt ?? 0;
                $currentCpt  = max($package->cpts - $prevCpt, 0);
    
                // Current APC
                $currentApcTotal = ($package->apc ?? 0) * $bottleCount;
            }
        }
    
        // Upgrade price = difference
        $upgradePrice = max(($package->price ?? 0) - $lastPackagePrice, 0);
    
        // Main APC (full bottles)
        $apcTotal = ($package->apc ?? 0) * ($package->bottle ?? 0);
    
        // Final price depending on upgrade mode
        $finalPrice = $isUpgrade
            ? $upgradePrice + $currentApcTotal
            : $upgradePrice + $apcTotal;
    
        $products = Product::all();
    
        return view('user.member-purchase-package', compact(
            'package',
            'user',
            'products',
            'isUpgrade',
            'upgradePrice',
            'lastPackagePrice',
            'bottleCount',
            'currentCpt',
            'currentApcTotal',
            'apcTotal',
            'finalPrice'
        ));
    }
    








public function memberPurchase(Request $request)
{
    // Logged-in buyer (who is initiating the purchase)
    $buyer = Auth::user();
  $transactionId = $buyer->id . '-' . now()->timestamp;

    // Target member (the one receiving the package)
    $targetUser = User::findOrFail($request->member_id);

    $validator = Validator::make($request->all(), [
        'member_id' => 'required|exists:users,id',   //  validate target member
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

    $previousPackage = userpackage::where('user_id', $targetUser->id)
        ->where('status', 'approved')
        ->latest()
        ->first();
    
    // Default values
    $requiredBottles = (int) $newPackage->bottle;
    $requiredCpts    = (float) $newPackage->cpts;
    
    // Adjust for upgrade
    if ($previousPackage) {
        $prevPkg = Package::find($previousPackage->package_id);
    
        if ($prevPkg) {
            $prevBottle = (int) ($previousPackage->previous_bottle ?? $prevPkg->bottle);
            $prevCpt    = (float) ($previousPackage->previous_package_cpt ?? $prevPkg->cpts);
    
            $requiredBottles = max((int)$newPackage->bottle - $prevBottle, 0);
            $requiredCpts    = max((float)$newPackage->cpts - $prevCpt, 0);
        }
    }
    
    // Optional (for UI/reference only)
    $maxBottles = (int) $newPackage->bottle;
    $maxCpts    = (float) $newPackage->cpts;
    
    
    // === VALIDATION ===
    $validator->after(function ($validator) use ($request, $requiredBottles, $requiredCpts) {
    
        $rows = $request->product ?? [];
    
        $selected = array_filter($rows, function ($r) {
            return !empty($r['qty']) && (int)$r['qty'] > 0;
        });
    
        // If upgrade and nothing is required, skip validation
        if ((int)$requiredBottles === 0 && (float)$requiredCpts === 0.0) {
            return;
        }
    
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
    
        // Normalize values
        $totalQty = (int) $totalQty;
        $requiredBottles = (int) $requiredBottles;
    
        // Bottle validation
        if ($totalQty !== $requiredBottles) {
            $validator->errors()->add(
                'product',
                "You must select exactly {$requiredBottles} bottles; you selected {$totalQty}."
            );
        }
    
        // CPT validation (with tolerance)
        if (abs($totalCpts - $requiredCpts) > 0.0001) {
            $validator->errors()->add(
                'product',
                "Total CPTs must be exactly {$requiredCpts}; you selected {$totalCpts}."
            );
        }
    });


    if ($validator->fails()) {
        return back()->withErrors($validator)->withInput();
    }
 


    $previousPackage = userpackage::where('user_id', $targetUser->id)
    ->where('status', 'approved')
    ->latest()
    ->first();

if ($previousPackage && $newPackage->id <= $previousPackage->package_id) {
    return back()->with('error', 'This member can only upgrade to a higher package.');
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
        //$ctpToAdd -= $previous->cpts;
    }

    $amountToPay += ($newPackage->apc * $newPackage->bottle);

    // === WALLET PAYMENT ===
if ($request->payment_method === 'wallet') {
    // Buyer is the logged-in user
    $buyer = Auth::user();

    // Target member is the one being purchased for
    $targetUser = User::findOrFail($request->member_id);

    // Validate buyer's transaction pin
    if ($request->transaction_pin !== $buyer->transaction_pin) {
        return back()->withErrors(['transaction_pin' => 'Invalid transaction PIN.'])->withInput();
    }

    // Validate buyer's wallet balance
    if ($buyer->deposit_wallet_balance < $amountToPay) {
        return back()->with('error', 'Insufficient wallet balance.');
    }

    DB::transaction(function () use ($buyer, $targetUser, $newPackage, $previousPackageId, $amountToPay, $ctpToAdd, $request, $isUpgrade, $transactionId) {

        // Deduct from buyer’s wallet
        $buyer->deposit_wallet_balance -= $amountToPay;
        $buyer->save();

        // Update target member’s CPTs
        $targetUser->total_ctp += $ctpToAdd;
        $targetUser->p_c_cpts += $ctpToAdd;
        $targetUser->current_p_c_cpts += $ctpToAdd;
        $targetUser->current_c_cpts += $ctpToAdd;

        // Normalize rank: reset to Regular if not in incentive ranks
        $incentiveRanks = DB::table('incentive_settings')->pluck('rank')->toArray();
        $rankToSet = in_array($targetUser->user_rank, $incentiveRanks) ? $targetUser->user_rank : 'Regular';

        // Update target member’s plan and status
        $targetUser->update([
            'status'    => 'active',
            'user_rank' => $rankToSet,
            'user_plan' => $newPackage->packageName,
        ]);

        // Trigger incentive check for direct upline of target member
        if ($targetUser->parent_id) {
            $parent = User::find($targetUser->parent_id);
            if ($parent) {
                $this->evaluateUserIncentives($parent);
            }
        }

        // Add CPT to uplines + matching bonus
        $this->addCTPToUplines($targetUser, $ctpToAdd, $isUpgrade, $previousPackageId, $transactionId);

        // Then trigger matching separately (your system design already supports this)
        $this->handleMatchingBonus($targetUser, $ctpToAdd, 1, $isUpgrade, $previousPackageId, $transactionId);

        // Trigger matching for target member
        //$this->handleMatchingBonus($targetUser, $ctpToAdd);

        // Create package order for target member
     $userPackage = userpackage::create([
    'user_id'             => $targetUser->id,
    'package_id'          => $newPackage->id,
    'previous_package_id' => $previousPackageId,
    'previous_package_cpt'=>$newPackage->cpts,
     'transaction_id' => $transactionId,
    'previous_bottle'=>$newPackage->bottle,
    'amount_paid'         => $amountToPay,
    'payment_method'      => 'wallet',
    'status'              => 'approved',
    'package_order_status'=> 'approved',
]);


        // Save ordered products
        foreach ($request->product as $row) {
            $qty = (int) ($row['qty'] ?? 0);
            if ($qty <= 0) continue;

            DB::table('package_product_orders')->insert([
                'user_id'          => $targetUser->id,
                'package_id'       => $newPackage->id,
                'product_id'       => $row['id'],
                'package_order_id' => $userPackage->id,
                'qty'              => $qty,
                'created_at'       => now(),
                'updated_at'       => now(),
            ]);
        }

        // Referral bonus for target member
        $packageAmount = $newPackage->price;

        if ($isUpgrade && isset($previous)) {
            $packageAmount -= $previous->price; // ONLY DIFFERENCE
        }
        
        $this->payReferralBonus($targetUser, $packageAmount, $isUpgrade, $previousPackageId, $transactionId);
    });

    return redirect()->route('user.dashboard')->with('success', 'Package purchased successfully using wallet.');
}


 

    // === ONLINE PAYMENT — PAYSTACK ===
if ($request->payment_method === 'online') {

    $buyer = Auth::user(); // logged-in user
    $targetUser = User::findOrFail($request->member_id); // member receiving package

    try {
        $request->validate([
            'paystack_reference' => 'required|string',
            'package_id' => 'required|exists:package,id',
            'member_id' => 'required|exists:users,id',
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

        $previousPackage = userpackage::where('user_id', $targetUser->id)
            ->where('status', 'approved')
            ->latest()
            ->first();

        $isUpgrade = false;
        $previousPackageId = null;

        if ($previousPackage && $newPackage->id > $previousPackage->package_id) {
            $isUpgrade = true;
            $previousPackageId = $previousPackage->package_id;
            $previous = Package::find($previousPackageId);
            // $ctpToAdd -= $previous->cpts;
        }

        $targetUser->total_ctp += $ctpToAdd;
        $targetUser->p_c_cpts += $ctpToAdd;
        $targetUser->current_p_c_cpts += $ctpToAdd;
        $targetUser->current_c_cpts += $ctpToAdd;

        DB::transaction(function () use ($targetUser, $newPackage, $previousPackageId, $amountPaid, $ctpToAdd, $productSelection, $isUpgrade, $transactionId) {

            // Normalize rank: reset to Regular if not in incentive ranks
            $incentiveRanks = DB::table('incentive_settings')->pluck('rank')->toArray();
            $rankToSet = in_array($targetUser->user_rank, $incentiveRanks) ? $targetUser->user_rank : 'Regular';

            // Update target user CPT and status
            $targetUser->update([
                'status'    => 'active',
                'user_rank' => $rankToSet,
                'user_plan' => $newPackage->packageName,
            ]);

            // Update uplines + evaluate parent incentives
            $this->addCTPToUplines($targetUser, $ctpToAdd, $isUpgrade, $previousPackageId, $transactionId);

            // Trigger matching bonus correctly
            $this->handleMatchingBonus($targetUser, $ctpToAdd, 1, $isUpgrade, $previousPackageId, $transactionId);

            if ($targetUser->parent_id) {
                $parent = User::find($targetUser->parent_id);
                if ($parent) {
                    $this->evaluateUserIncentives($parent);
                }
            }

            // Create package order
            $userPackage = userpackage::create([
                'user_id'             => $targetUser->id,
                'package_id'          => $newPackage->id,
                'previous_package_id' => $previousPackageId,
                'previous_package_cpt'=>$newPackage->cpts,
                'previous_bottle'=>$newPackage->bottle,
                 'transaction_id' => $transactionId,
                'amount_paid'         => $amountPaid,
                'payment_method'      => 'online',
                'status'              => 'approved',
                'package_order_status'=> 'approved',
            ]);

            // Save ordered products
            foreach ($productSelection as $row) {
                $qty = (int) ($row['qty'] ?? 0);
                if ($qty <= 0) continue;

                DB::table('package_product_orders')->insert([
                    'user_id'          => $targetUser->id,
                    'package_id'       => $newPackage->id,
                    'product_id'       => $row['id'],
                    'package_order_id' => $userPackage->id,
                    'qty'              => $qty,
                    'created_at'       => now(),
                    'updated_at'       => now(),
                ]);
            }

            // Trigger matching for target user
           // $this->handleMatchingBonus($targetUser, $ctpToAdd);

            // Referral bonus
            $packageAmount = $newPackage->price;

            if ($isUpgrade && isset($previous)) {
                $packageAmount -= $previous->price; // ONLY DIFFERENCE
            }
            
            $this->payReferralBonus($targetUser, $packageAmount, $isUpgrade, $previousPackageId, $transactionId);
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

 

public function payReferralBonus(User $user, float $packageAmount, bool $isUpgrade = false, $previousPackageId = null, $transactionId = null): void
{
    if ($packageAmount <= 0) {
        return;
    }

    $sponsor = $user->sponsor_id ? User::find($user->sponsor_id) : null;
    $levels  = [21, 2, 1, 1];
    $level   = 0;

    while ($sponsor && $level < 4) {

        // Upgrades: only level 1 sponsor gets bonus
        if ($isUpgrade && $level > 0) {
            break;
        }

        // Skip inactive sponsors for deeper levels
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


        //   Skip sponsors whose status is pending
        if (strtolower($sponsor->status) === 'pending') {
            $sponsor = $sponsor->sponsor_id ? User::find($sponsor->sponsor_id) : null;
            $level++;
            continue;
        }

        $percentage = $levels[$level];
        $bonusAmount = 0;
        $type = 'referral';
        $description = '';

        if ($isUpgrade && $level === 0) {
            // Pay only on upgrade difference
            $oldAmount = 0;
            if ($previousPackageId) {
                $old = Package::find($previousPackageId);
                $oldAmount = $old ? $old->price : 0;
            }
            $bonusAmount = ($packageAmount - $oldAmount) * 22 / 100;
            $type = 'upgrade';
            $description = "Upgrade bonus from user {$user->username}";
        } else {
            $bonusAmount = $packageAmount * ($percentage / 100);
            $description = "Referral bonus level " . ($level + 1);
        }

        if ($bonusAmount > 0) {
            $sponsor->increment('withdraw_wallet_balance', $bonusAmount);

            // Credit record for the sponsor (receiver)
            Bonus::create([
                'user_id'        => $sponsor->id,     // receiver
                'amount'         => $bonusAmount,
                'source_user_id' => $user->id,        // payer
                'transaction_id' => $transactionId,
                'type'           => $type,
                'status'         => 'credit',         // credit
                'description'    => "{$description} to {$sponsor->username}",
            ]);

            // Debit record for the payer (buyer)
            Bonus::create([
                'user_id'        => $user->id,        // payer
                'amount'         => $bonusAmount,
                'source_user_id' => $sponsor->id,     // receiver
                'transaction_id' => $transactionId,
                'type'           => $type,
                'status'         => 'debit',          // debit
                'description'    => "{$description} paid to {$sponsor->username}",
            ]);
        }

        $sponsor = $sponsor->sponsor_id ? User::find($sponsor->sponsor_id) : null;
        $level++;
    }
}




public function addCTPToUplinesold(User $buyer, int $ctpGained, bool $isUpgrade = false, $previousPackageId = null): void
{
    // Ensure CPT is effective (already calculated before calling)
    if ($isUpgrade && $previousPackageId) {
        $previous = Package::find($previousPackageId);
        if ($previous) {
            $ctpGained = max($ctpGained, 0);
        }
    }

    $currentUplineId = $buyer->parent_id;
    $position        = $buyer->position;
    $isDirect        = true;

    while ($currentUplineId) {
        $upline = User::find($currentUplineId);
        if (!$upline) break;

         
         if ($upline->status !== 'active' || $upline->is_muted == 1) {
        $currentUplineId = $upline->parent_id;
        continue;
    }

        $upline->increment('downline_cpt', $ctpGained);
        $upline->increment('total_ctp', $ctpGained);

        if ($isDirect) {
            if ($position === 'left') {
                $upline->increment('downline_left_cpt', $ctpGained);
            } elseif ($position === 'right') {
                $upline->increment('downline_right_cpt', $ctpGained);
            }
            $isDirect = false;
        }

        $upline->save();
        $this->evaluateUserIncentives($upline);

        $currentUplineId = $upline->parent_id;
    }
}




public function handleMatchingBonus(User $user, int $ctpGained, int $generation = 1, bool $isUpgrade = false, $previousPackageId = null, $transactionId = null): void
{
    if ($generation > 9 || !$user->sponsor_id) {
        return;
    }

    $upline = User::find($user->sponsor_id);
    if (!$upline) return;

    // Ensure CPT is effective for upgrade
    if ($isUpgrade && $previousPackageId) {
        $previous = Package::find($previousPackageId);
        if ($previous) {
            $ctpGained = max($ctpGained, 0);
        }
    }

   if ($upline->status !== 'active' || $upline->is_muted == 1) {
        $this->handleMatchingBonus($upline, $ctpGained, $generation + 1, $isUpgrade, $previousPackageId);
        return;
    }

    $currentSide = $this->getSideRelativeToAncestor($upline, $user);
    if (!in_array($currentSide, ['left', 'right'])) {
        return;
    }

    if ($currentSide === 'left') {
        $upline->increment('left_ctp_for_matching', $ctpGained);
    } elseif ($currentSide === 'right') {
        $upline->increment('right_ctp_for_matching', $ctpGained);
    }

    $upline->refresh();

    $left  = $upline->left_ctp_for_matching;
    $right = $upline->right_ctp_for_matching;
    $pairCPT = 16;

    if ($left >= $pairCPT && $right >= $pairCPT) {
        $possiblePairs = floor(min($left, $right) / $pairCPT);
        $usedCPT       = $possiblePairs * $pairCPT;

        $upline->decrement('left_ctp_for_matching', $usedCPT);
        $upline->decrement('right_ctp_for_matching', $usedCPT);

        $activePackage = userpackage::where('user_id', $upline->id)
            ->where('status', 'approved')
            ->latest('created_at')
            ->first();

        if ($activePackage) {
            $package = Package::find($activePackage->package_id);

            if ($package) {
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

                $dailyMatchedCount = Bonus::where('user_id', $upline->id)
                    ->where('type', 'matching')
                    ->whereDate('created_at', now())
                    ->count();

                $remainingPairsAllowedToday = max(0, $matchLimitPairs - $dailyMatchedCount);
                $baseAmount = 24000;

                for ($i = 0; $i < $possiblePairs; $i++) {
                    $amount = 0;

                    if ($i < $remainingPairsAllowedToday) {
                        $amount = $baseAmount * ($matchPercentage / 100);
                        $upline->increment('withdraw_wallet_balance', $amount);
                    }

                  // Credit record for the upline (receiver)
\App\Models\Bonus::create([
    'user_id'        => $upline->id,       // receiver
    'amount'         => $amount,
    'type'           => 'matching',
    'source_user_id' => $user->id,         // payer
    'transaction_id' => $transactionId,
    'status'        => 'credit',             // credit
 'description'    => "Matching bonus for 1 pair — CPT deducted {$pairCPT} from {$user->username} to {$upline->username}",
    'is_approved'    => false,
]);

// Debit record for the payer (buyer)
\App\Models\Bonus::create([
    'user_id'        => $user->id,         // payer
    'amount'         => $amount,
    'type'           => 'matching',
    'source_user_id' => $upline->id,       // receiver
    'transaction_id' => $transactionId,
     'status'        => 'debit',                 // debit
     'description'    => "Matching bonus for 1 pair — CPT deducted {$pairCPT} paid to {$upline->username}",
    'is_approved'    => false,
]);

                }
            }
        }
    }

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





 
}
