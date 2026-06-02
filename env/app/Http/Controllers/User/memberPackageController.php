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

    return redirect()->route('member.package.select', ['username' => $validated['member']]);
}



public function checkUsername(Request $request)
{
    $request->validate([
        'member' => 'required|string',
    ]);

    $exists = User::where('username', $request->member)->exists();

    return response()->json([
        'exists' => $exists,
        'message' => $exists ? 'Username found ✅' : 'Username not found ❌',
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
    $lastUserPkg = \App\Models\UserPackage::where('user_id', $user->id)
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
    $user = User::findOrFail($userId); // target member

    // Check if this member already purchased this package
    $existing = UserPackage::where('user_id', $user->id)
        ->where('package_id', $packageId)
        ->first();

    if ($existing) {
        return redirect()->back()->with('error', 'This member has already purchased this package.');
    }

    // Get the member's last purchased package (previous upgrade)
    $previous = UserPackage::where('user_id', $user->id)
        ->latest('id')
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

    return view('user.member-purchase-package', compact('package', 'user', 'finalPrice', 'products'));
}








public function memberPurchase(Request $request)
{
    // Logged-in buyer (who is initiating the purchase)
    $buyer = Auth::user();

    // Target member (the one receiving the package)
    $targetUser = User::findOrFail($request->member_id);

    $validator = Validator::make($request->all(), [
        'member_id' => 'required|exists:users,id',   // ✅ validate target member
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
 


    $previousPackage = UserPackage::where('user_id', $targetUser->id)
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

    DB::transaction(function () use ($buyer, $targetUser, $newPackage, $previousPackageId, $amountToPay, $ctpToAdd, $request, $isUpgrade) {

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
        $this->addCTPToUplines($targetUser, $ctpToAdd, function ($uplineUser) use ($ctpToAdd) {
            $this->handleMatchingBonus($uplineUser, $ctpToAdd);
        });

        // Trigger matching for target member
        $this->handleMatchingBonus($targetUser, $ctpToAdd);

        // Create package order for target member
     $userPackage = UserPackage::create([
    'user_id'             => $targetUser->id,
    'package_id'          => $newPackage->id,
    'previous_package_id' => $previousPackageId,
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
        $this->payReferralBonus($targetUser, $newPackage->price, $isUpgrade, $previousPackageId);
    });

    return redirect()->route('user.package')->with('success', 'Package purchased successfully using wallet.');
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

        $previousPackage = UserPackage::where('user_id', $targetUser->id)
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

        DB::transaction(function () use ($targetUser, $newPackage, $previousPackageId, $amountPaid, $ctpToAdd, $productSelection, $isUpgrade) {

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
            $this->addCTPToUplines($targetUser, $ctpToAdd, function ($uplineUser) use ($ctpToAdd) {
                $this->handleMatchingBonus($uplineUser, $ctpToAdd);
            });

            if ($targetUser->parent_id) {
                $parent = User::find($targetUser->parent_id);
                if ($parent) {
                    $this->evaluateUserIncentives($parent);
                }
            }

            // Create package order
            $userPackage = UserPackage::create([
                'user_id'             => $targetUser->id,
                'package_id'          => $newPackage->id,
                'previous_package_id' => $previousPackageId,
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
            $this->payReferralBonus($targetUser, $newPackage->price, $isUpgrade, $previousPackageId);
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








public function payReferralBonus(User $targetUser, float $amount, bool $isUpgrade = false, $previousPackageId = null): void
{
    $sponsor = $targetUser->sponsor_id ? User::find($targetUser->sponsor_id) : null;
    $levels = [21, 2, 1, 1]; // % per level
    $level = 0;

    \Log::info("==> Starting payReferralBonus for user ID {$targetUser->id} | Amount: {$amount} | IsUpgrade: " . ($isUpgrade ? 'Yes' : 'No'));

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
            $description = "Upgrade bonus from user {$targetUser->username}";
        } else {
            $bonusAmount = $amount * ($percentage / 100);
            $type = 'referral';
            $description = "Referral bonus from level " . ($level + 1) . " user {$targetUser->username}";
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





public function handleMatchingBonus(User $targetUser, $ctpGained, $generation = 1)
{
    if ($generation > 9 || !$targetUser->sponsor_id) {
        return;
    }

    $upline = User::find($targetUser->sponsor_id);
    if (!$upline) return;

    // Determine side relative to THIS upline via binary tree
    $currentSide = $this->getSideRelativeToAncestor($upline, $targetUser);

    if (!in_array($currentSide, ['left', 'right'])) {
        return; // Not inside this sponsor's binary subtree
    }

    // Increment matching counters
    if ($currentSide === 'left') {
        $upline->increment('left_ctp_for_matching', $ctpGained);
    } elseif ($currentSide === 'right') {
        $upline->increment('right_ctp_for_matching', $ctpGained);
    }

    $upline->refresh();

    // Matching bonus logic
    $left  = $upline->left_ctp_for_matching;
    $right = $upline->right_ctp_for_matching;
    $pairCPT = 16;

    if ($left >= $pairCPT && $right >= $pairCPT) {
        $possiblePairs = floor(min($left, $right) / $pairCPT);
        $usedCPT = $possiblePairs * $pairCPT;

        $upline->decrement('left_ctp_for_matching', $usedCPT);
        $upline->decrement('right_ctp_for_matching', $usedCPT);

        $activePackage = UserPackage::where('user_id', $upline->id)
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

                    Bonus::create([
                        'user_id'     => $upline->id,
                        'amount'      => $amount,
                        'type'        => 'matching',
                        'is_paid'     => 1,
                        'description' => "Matching payout for 1 pair — CPT deducted {$pairCPT}",
                        'is_approved' => false,
                    ]);
                }
            }
        }
    }

    // Move up sponsor chain
    $this->handleMatchingBonus($upline, $ctpGained, $generation + 1);
}




public function evaluateUserIncentives(User $targetUser): void
{
    $ranks = DB::table('incentive_settings')
        ->orderBy('required_ctp', 'asc')
        ->get();

    // Get direct downlines (placements) of the target member
    $directDownlines = User::where('parent_id', $targetUser->id)->get();

    $legs = [];
    foreach ($directDownlines as $downline) {
        // Calculate cumulative CPT for each placement leg
        $legs[] = $this->calculateLegCpt($downline);
    }

    rsort($legs);

    foreach ($ranks as $r) {
        // Get required number of top legs
        $selectedLegs = array_slice($legs, 0, $r->required_downline_count);
        $topSum = array_sum($selectedLegs);

        if ($topSum >= $r->required_ctp) {
            // Check weaker leg percentage condition
            if (count($selectedLegs) >= 2 && $r->min_lesser_leg_percent > 0) {
                $strongerLeg = max($selectedLegs);
                $weakerLeg   = min($selectedLegs);

                $requiredWeakerValue = ($r->min_lesser_leg_percent / 100) * $strongerLeg;

                if ($weakerLeg < $requiredWeakerValue) {
                    continue; // Skip this rank, weaker leg not qualified
                }
            }

            // Update target member’s rank
            $targetUser->user_rank = $r->rank;
            $targetUser->save();

            Incentives::updateOrCreate(
                ['user_id' => $targetUser->id, 'rank' => $r->rank],
                ['status' => 'achieved', 'achieved_at' => now()]
            );

            break;
        }
    }
}




public function addCTPToUplines(User $targetUser, int $ctpGained): void
{
    $currentUplineId = $targetUser->parent_id;
    $position        = $targetUser->position; // 'left' or 'right'
    $isDirect        = true;

    while ($currentUplineId) {
        $upline = User::find($currentUplineId);
        if (!$upline) break;

        // Update overall downline CPT
        $upline->increment('downline_cpt', $ctpGained);
        $upline->increment('total_ctp', $ctpGained);

        // Only direct upline gets leg-specific CPT
        if ($isDirect) {
            if ($position === 'left') {
                $upline->increment('downline_left_cpt', $ctpGained);
            } elseif ($position === 'right') {
                $upline->increment('downline_right_cpt', $ctpGained);
            }
            $isDirect = false;
        }

        $upline->save();

        // Evaluate incentives for this upline
        $this->evaluateUserIncentives($upline);

        // Move up the chain
        $currentUplineId = $upline->parent_id;
    }

    // Trigger matching bonus ONCE for the target member
    // If you want to propagate matching bonus from the target member upward:
    $this->handleMatchingBonus($targetUser, $ctpGained);
}





private function calculateLegCpt(User $root): int
{
    $total = $root->total_ctp ?? 0;

    $descendants = $this->getAllDescendants($root);
    foreach ($descendants as $d) {
        $total += $d->total_ctp ?? 0;
    }

    return $total;
}





private function getAllDescendants($user, $level = 1, &$all = null, $inheritedLeg = null)
{
    if ($all === null) {
        $all = collect();
    }

    $children = User::where('parent_id', $user->id)->get();

    foreach ($children as $child) {
        //  At level 1, decide leg based on placement under root
        if ($level === 1) {
            if ($child->id === optional($user->leftChild)->id) {
                $child->leg = 'Left';
            } elseif ($child->id === optional($user->rightChild)->id) {
                $child->leg = 'Right';
            } else {
                $child->leg = null;
            }
        } else {
            //  Deeper descendants inherit ancestor’s leg
            $child->leg = $inheritedLeg;
        }

        $child->generation_level = $level;
        $child->upline  = User::find($child->parent_id);
        $child->sponsor = User::find($child->sponsor_id);

        $all->push($child);

        $this->getAllDescendants($child, $level + 1, $all, $child->leg);
    }

    return $all;
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

    return null; // not in subtree
}




 
}
