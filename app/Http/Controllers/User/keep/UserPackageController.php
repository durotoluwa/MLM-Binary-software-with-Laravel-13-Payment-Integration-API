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
        //$ctpToAdd -= $previous->cpts;
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
    $user->total_ctp += $ctpToAdd;
    $user->p_c_cpts += $ctpToAdd;
    $user->current_p_c_cpts += $ctpToAdd;
    $user->current_c_cpts += $ctpToAdd;
       
              // Normalize rank: reset to Regular if not in incentive ranks
                $incentiveRanks = DB::table('incentive_settings')->pluck('rank')->toArray();
                $rankToSet = in_array($user->user_rank, $incentiveRanks) ? $user->user_rank : 'Regular';

                // Update buyer CPT and status
                 $user->update([

        'status'                 => 'active',
        'user_rank'              => $rankToSet,
        'user_plan'              => $newPackage->packageName,
    ]);

    // Trigger incentive check for direct upline
    if ($user->parent_id) {
        $parent = User::find($user->parent_id);
        if ($parent) {
            $this->evaluateUserIncentives($parent);
        }
    }

    // Add CPT to uplines + matching bonus
  ///  $this->addCTPToUplines($user, $ctpToAdd, function ($uplineUser) use ($ctpToAdd) {
      //  $this->handleMatchingBonus($uplineUser, $ctpToAdd);
   // });

    $this->addCTPToUplines($user, $ctpToAdd, function ($uplineUser) use ($ctpToAdd) {
    $this->handleMatchingBonus($uplineUser, $ctpToAdd);
});

    // Trigger matching for buyer
    $this->handleMatchingBonus($user, $ctpToAdd);

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
                //$ctpToAdd -= $previous->cpts;
            }

    $user->total_ctp += $ctpToAdd;
    $user->p_c_cpts += $ctpToAdd;
    $user->current_p_c_cpts += $ctpToAdd;
    $user->current_c_cpts += $ctpToAdd;

            DB::transaction(function () use ($user, $newPackage, $previousPackageId, $amountPaid, $ctpToAdd, $productSelection, $isUpgrade) {

                // Normalize rank: reset to Regular if not in incentive ranks
                $incentiveRanks = DB::table('incentive_settings')->pluck('rank')->toArray();
                $rankToSet = in_array($user->user_rank, $incentiveRanks) ? $user->user_rank : 'Regular';

                // Update buyer CPT and status
                 $user->update([

        'status'                 => 'active',
        'user_rank'              => $rankToSet,
        'user_plan'              => $newPackage->packageName,
    ]);

                // Update uplines (once — fixed) + evaluate parent incentives
               // $this->addCTPToUplines($user, $ctpToAdd, function ($uplineUser) use ($ctpToAdd) {
                 //   $this->handleMatchingBonus($uplineUser, $ctpToAdd);
               // });

                $this->addCTPToUplines($user, $ctpToAdd, function ($uplineUser) use ($ctpToAdd) {
    $this->handleMatchingBonus($uplineUser, $ctpToAdd);
});

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
                    'amount_paid' => $amountPaid,
                    'payment_method' => 'online',
                    'status' => 'approved',
                    'package_order_status' => 'approved',
                ]);

                // Save ordered products
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

                // Trigger matching for buyer
                $this->handleMatchingBonus($user, $ctpToAdd);

                // Referral bonus
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


 

public function addCTPToUplinesold(User $buyer, int $ctpGained): void
{
    $currentUplineId = $buyer->parent_id;
    $position        = $buyer->position; // 'left' or 'right'
    $isDirect        = true; // flag to mark the first upline only

    while ($currentUplineId) {
        $upline = User::find($currentUplineId);
        if (!$upline) break;

        // Ensure numeric defaults
        if ($upline->downline_cpt === null) $upline->downline_cpt = 0;
        if ($upline->total_ctp === null) $upline->total_ctp = 0;

        //  Add CPT to overall downline_cpt
        $upline->increment('downline_cpt', $ctpGained);

        //  Add CPT to total_ctp
        $upline->increment('total_ctp', $ctpGained);

        //  Only the direct upline gets left/right leg CPT
        if ($isDirect) {
            if ($upline->downline_left_cpt === null) $upline->downline_left_cpt = 0;
            if ($upline->downline_right_cpt === null) $upline->downline_right_cpt = 0;

            if ($position === 'left') {
                $upline->increment('downline_left_cpt', $ctpGained);
            } elseif ($position === 'right') {
                $upline->increment('downline_right_cpt', $ctpGained);
            }

            $isDirect = false; // after first upline, stop updating leg-specific columns
        }

        $upline->save();

        // Move up the chain
        $currentUplineId = $upline->parent_id;
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




public function addCTPToUplinesnew(User $buyer, int $ctpGained): void
{
    $currentUplineId = $buyer->parent_id;
    $position        = $buyer->position; // 'left' or 'right'
    $isDirect        = true;

    while ($currentUplineId) {
        $upline = User::find($currentUplineId);
        if (!$upline) break;

        // Centralized CPT update + incentive evaluation
        $this->addCTP($upline, $ctpGained);

        // Direct upline gets leg-specific CPT
        if ($isDirect) {
            if ($position === 'left') {
                $upline->increment('downline_left_cpt', $ctpGained);
            } elseif ($position === 'right') {
                $upline->increment('downline_right_cpt', $ctpGained);
            }
            $isDirect = false;
        }

        $currentUplineId = $upline->parent_id;
    }
}


public function addCTPToUplines(User $buyer, int $ctpGained): void
{
    $currentUplineId = $buyer->parent_id;
    $position        = $buyer->position; // 'left' or 'right'
    $isDirect        = true;

    while ($currentUplineId) {
        $upline = User::find($currentUplineId);
        if (!$upline) break;

        //  Update overall downline CPT
        $upline->increment('downline_cpt', $ctpGained);
        $upline->increment('total_ctp', $ctpGained);

        //  Only direct upline gets leg-specific CPT
        if ($isDirect) {
            if ($position === 'left') {
                $upline->increment('downline_left_cpt', $ctpGained);
            } elseif ($position === 'right') {
                $upline->increment('downline_right_cpt', $ctpGained);
            }
            $isDirect = false;
        }

        $upline->save();

        //  Evaluate incentives for this upline
        $this->evaluateUserIncentives($upline);

        // Move up the chain
        $currentUplineId = $upline->parent_id;
    }

    //  Trigger matching bonus ONCE for the buyer
    //$this->handleMatchingBonus($buyer, $ctpGained);
}



private function getBranchSide(User $child, User $root): string
{
    $current = $child;

    // Walk up until we reach the root
    while ($current->parent_id && $current->parent_id != $root->id) {
        $current = User::find($current->parent_id);
        if (!$current) break;
    }

    // Now $current is the direct child of $root
    return strtolower($current->position ?? 'unknown');
}
public function addCTP(User $user, int $ctpToAdd): void
{
    // Update CPT counters
    $user->increment('total_ctp', $ctpToAdd);
    $user->increment('p_c_cpts', $ctpToAdd);
    $user->increment('current_p_c_cpts', $ctpToAdd);
    $user->increment('current_c_cpts', $ctpToAdd);

    $this->evaluateUserIncentives($user);

    //  Lock branch side relative to root ancestor
    $rootUpline = User::find($user->parent_id);
    if ($rootUpline) {
        $current = $user;
        while ($current->parent_id && $current->parent_id != $rootUpline->id) {
            $current = User::find($current->parent_id);
            if (!$current) break;
        }

        // Direct child of root decides the branch side
        $side = strtolower($current->position ?? 'unknown');

        // Pass locked side into matching bonus
        //$this->handleMatchingBonus($user, $ctpToAdd, 1, $side);
    }

    // Evaluate uplines
    $currentUplineId = $user->parent_id;
    while ($currentUplineId) {
        $upline = User::find($currentUplineId);
        if (!$upline) break;

        $this->evaluateUserIncentives($upline);
        $currentUplineId = $upline->parent_id;
    }
}



 



public function evaluateUserIncentives(User $user): void
{
    $ranks = DB::table('incentive_settings')
        ->orderBy('required_ctp', 'asc')
        ->get();

    // Get direct downlines (placements)
    $directDownlines = User::where('parent_id', $user->id)->get();

    $legs = [];
    foreach ($directDownlines as $downline) {
        // Calculate cumulative CPT for each placement leg
        $legs[] = $this->calculateLegCpt($downline);
    }

    rsort($legs);

    foreach ($ranks as $r) {
        $topSum = array_sum(array_slice($legs, 0, $r->required_downline_count));

        if ($topSum >= $r->required_ctp) {
            $user->user_rank = $r->rank;
            $user->save();

            incentives::updateOrCreate(
                ['user_id' => $user->id, 'rank' => $r->rank],
                ['status' => 'achieved', 'achieved_at' => now()]
            );

            break;
        }
    }
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




private function calculateLegCpt(User $root): int
{
    $total = $root->total_ctp ?? 0;

    $descendants = $this->getAllDescendants($root);
    foreach ($descendants as $d) {
        $total += $d->total_ctp ?? 0;
    }

    return $total;
}













private function getPlacementSide(User $child, User $root): string
{
    $current = $child;

    // Walk up until we reach the root
    while ($current->parent_id && $current->parent_id != $root->id) {
        $current = User::find($current->parent_id);
        if (!$current) break;
    }

    // Now $current is the direct child of $root
    return strtolower($current->position ?? 'unknown');
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




public function handleMatchingBonus(User $user, $ctpGained, $generation = 1)
{
    if ($generation > 9 || !$user->sponsor_id) {
        return;
    }

    $upline = User::find($user->sponsor_id);
    if (!$upline) return;

    //  FIXED: Determine side relative to THIS upline via binary tree
    $currentSide = $this->getSideRelativeToAncestor($upline, $user);

    if (!in_array($currentSide, ['left', 'right'])) {
        return; // Not inside this sponsor's binary subtree
    }

    // Use increment to avoid runtime duplication issues
    if ($currentSide === 'left') {
        $upline->increment('left_ctp_for_matching', $ctpGained);
    } elseif ($currentSide === 'right') {
        $upline->increment('right_ctp_for_matching', $ctpGained);
    }

    $upline->refresh();

    // Matching bonus logic (unchanged structure)
    $left  = $upline->left_ctp_for_matching;
    $right = $upline->right_ctp_for_matching;
    $pairCPT = 16;

    if ($left >= $pairCPT && $right >= $pairCPT) {

        $possiblePairs = floor(min($left, $right) / $pairCPT);
        $usedCPT = $possiblePairs * $pairCPT;

        $upline->decrement('left_ctp_for_matching', $usedCPT);
        $upline->decrement('right_ctp_for_matching', $usedCPT);

        $activePackage = \App\Models\UserPackage::where('user_id', $upline->id)
            ->where('status', 'approved')
            ->latest('created_at')
            ->first();

        if ($activePackage) {

            $package = \App\Models\Package::find($activePackage->package_id);

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

                $dailyMatchedCount = \App\Models\Bonus::where('user_id', $upline->id)
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

                    \App\Models\Bonus::create([
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


 



//public function distributeCTPsToUplines($user, $cpt)
//{
  ///  $upline = User::find($user->parent_id);
  //  $position = $user->position;

   // for ($level = 1; $upline && $level <= 10; $level++) {
   //     if ($position === 'left') {
    //        $upline->left_ctp_balance += $cpt;
      //  } elseif ($position === 'right') {
      //      $upline->right_ctp_balance += $cpt;
      //  }

     //   $upline->save();

        // Attempt matching
      //  $this->handleMatching($upline);

        // Move up
      //  $position = $upline->position;
      //  $upline = User::find($upline->parent_id);
   // }
//}



}
