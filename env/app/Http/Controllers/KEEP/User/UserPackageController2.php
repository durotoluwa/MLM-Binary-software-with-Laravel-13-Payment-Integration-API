<?php

namespace App\Http\Controllers\user;

use App\Http\Controllers\Controller;
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




 

class UserPackageController extends Controller
{



public function showPurchaseForm($id)
{
    $package = Package::findOrFail($id);
    $user = Auth::user();

    // Check if user already purchased this package
    $existing = UserPackage::where('user_id', $user->id)
        ->where('package_id', $id)
        ->first();

    if ($existing) {
        return redirect()->back()->with('error', 'You have already purchased this package.');
    }

    // Get the user's last purchased package (previous upgrade)
    $previous = UserPackage::where('user_id', $user->id)
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




public function handleMatchingBonus(User $user, $ctpGained)
{
    \Log::info("==> handleMatchingBonus() triggered for user ID: {$user->id}, gained CTP: {$ctpGained}");

    if (!$user->parent_id) {
        \Log::info("User has no upline. Skipping matching.");
        return;
    }

    $upline = User::find($user->parent_id);
    if (!$upline) {
        \Log::info("Upline not found for parent_id: {$user->parent_id}");
        return;
    }

    //  New Rule: Only sponsor-upline gets matching
    if ($upline->id !== $user->sponsor_id) {
        \Log::info("Skipping matching bonus. Upline ID {$upline->id} is not the sponsor of user ID {$user->id}");
        // still go up to next parent (in case higher ancestor is the sponsor)
        $this->handleMatchingBonus($upline, $ctpGained);
        return;
    }

    $position = $user->position;
    \Log::info("User position: {$position}");

    if (!in_array($position, ['left', 'right'])) {
        \Log::info("Invalid position: {$position}. Skipping.");
        return;
    }

    if ($position === 'left') {
        $upline->left_ctp_for_matching += $ctpGained;
        \Log::info("Added {$ctpGained} to LEFT CTP of user ID {$upline->id}");
    } else {
        $upline->right_ctp_for_matching += $ctpGained;
        \Log::info("Added {$ctpGained} to RIGHT CTP of user ID {$upline->id}");
    }

    // Fetch upline package
    $activePackage = userpackage::where('user_id', $upline->id)
        ->where('status', 'approved')
        ->latest('created_at')
        ->first();

    if (!$activePackage) {
        \Log::info("Upline has no active package.");
        return;
    }

    $package = Package::find($activePackage->package_id);
    if (!$package) {
        \Log::info("Package not found for ID: {$activePackage->package_id}");
        return;
    }

    $settings = getMatchingSettings(strtolower($package->packageName));
    $matchLimit = $settings['limit'] ?? 0;
    $matchPercentage = $settings['percentage'] ?? 0;

    $dailyMatched = Bonus::where('user_id', $upline->id)
        ->where('type', 'matching')
        ->whereDate('created_at', \Carbon\Carbon::today())
        ->count();

    $remainingMatches = $matchLimit - $dailyMatched;

    \Log::info("Upline ID {$upline->id} match count today: {$dailyMatched}/{$matchLimit}");

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

    $upline->save();

    //  Still continue recursion upwards
    \Log::info("Recursively checking next upline.");
    $this->handleMatchingBonus($upline, 0);
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

        // Products validation
        'product'       => 'required|array',
        'product.*.id'  => 'required|exists:product,id',
        'product.*.qty' => 'nullable|integer|min:0',
    ]);

    $newPackage = Package::find($request->package_id);
    $maxBottles = (int) $newPackage->bottle;
    $maxCpts    = (float) $newPackage->cpts;

    // Strict validation for bottles and CPTs
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

        // Bottles must match exactly
        if ($totalQty !== $maxBottles) {
            $validator->errors()->add('product', "You must select exactly {$maxBottles} bottles; you selected {$totalQty}.");
        }

        //  CPTs must match exactly
        if ($totalCpts !== $maxCpts) {
            $validator->errors()->add('product', "Total CPTs must be exactly {$maxCpts}; you selected {$totalCpts}.");
        }
    });

    if ($validator->fails()) {
        return back()->withErrors($validator)->withInput();
    }

    // === Existing package upgrade logic ===
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

    // --- WALLET PAYMENT ---
    if ($request->payment_method === 'wallet') {
        if ($request->transaction_pin !== $user->transaction_pin) {
            return back()->withErrors(['transaction_pin' => 'Invalid transaction PIN.'])->withInput();
        }

        if ($user->deposit_wallet_balance < $amountToPay) {
            return back()->with('error', 'Insufficient wallet balance.');
        }

        DB::transaction(function () use ($user, $newPackage, $previousPackageId, $amountToPay, $ctpToAdd, $request, $isUpgrade) {
            // Deduct from wallet
            $user->deposit_wallet_balance -= $amountToPay;
            $user->total_ctp += $ctpToAdd;
            $user->user_plan = $newPackage->packageName;

            if (empty($user->user_rank)) {
                $user->user_rank = 'Regular';
            }
            $user->save();

            $userPackage = UserPackage::create([
                'user_id'             => $user->id,
                'package_id'          => $newPackage->id,
                'previous_package_id' => $previousPackageId,
                'amount_paid'         => $amountToPay,
                'payment_method'      => 'wallet',
                'status'              => 'approved',
                'package_order_status'=> 'approved',  // approve immediately since product selected
            ]);

            // Save product selections
            foreach ($request->product as $row) {
                $qty = (int) ($row['qty'] ?? 0);
                if ($qty <= 0) continue;

                DB::table('package_product_orders')->insert([
                    'user_id'          => $user->id,
                    'package_id'       => $newPackage->id,
                    'product_id'       => $row['id'],
                    'package_order_id' => $userPackage->id,
                    'qty'              => $qty,
                    'created_at'       => now(),
                    'updated_at'       => now(),
                ]);
            }

            // Bonuses & uplines
            \addCtpToUplines($user->id, $ctpToAdd);
            $this->handleMatchingBonus($user, $ctpToAdd);
            $this->payReferralBonus($user, $newPackage->price, $isUpgrade, $previousPackageId);
        });

        return redirect()->route('user.package')->with('success', 'Package purchased and products selected successfully.');
    }

    // --- BANK TRANSFER ---
    if ($request->payment_method === 'bank') {
        if (!$request->hasFile('payment_proof')) {
            return back()->with('error', 'Please upload proof of payment.');
        }

        $image = $request->file('payment_proof');
        $filename = time() . '_' . Str::uuid() . '.' . $image->getClientOriginalExtension();
        $image->move(public_path('payment_proofs'), $filename);
        $paymentProofPath = 'payment_proofs/' . $filename;

        DB::transaction(function () use ($request, $user, $newPackage, $previousPackageId, $amountToPay, $paymentProofPath) {
            $userPackage = userpackage::create([
                'user_id' => $user->id,
                'package_id' => $newPackage->id,
                'previous_package_id' => $previousPackageId,
                'amount' => $amountToPay,
                'payment_method' => 'bank',
                'acctName' => $request->acctName,
                'bankName' => $request->bankName,
                'amount_paid' => $request->amount,
                'payment_proof' => $paymentProofPath,
                'status' => 'pending',
                'package_order_status' => 'pending',
            ]);

            // Save product selections but keep order pending
            foreach ($request->product as $row) {
                $qty = (int) ($row['qty'] ?? 0);
                if ($qty <= 0) continue;

                DB::table('package_product_orders')->insert([
                    'user_id'          => $user->id,
                    'package_id'       => $newPackage->id,
                    'product_id'       => $row['id'],
                    'package_order_id' => $userPackage->id,
                    'qty'              => $qty,
                    'created_at'       => now(),
                    'updated_at'       => now(),
                ]);
            }
        });

        return redirect()->route('user.package')->with('success', 'Package submitted for approval. Products saved.');
    }

    // --- ONLINE PLACEHOLDER ---
    if ($request->payment_method === 'online') {
        return redirect()->route('user.package')->with('info', 'Online payment not yet integrated.');
    }

    return back()->with('error', 'Invalid payment method.');
}




}
