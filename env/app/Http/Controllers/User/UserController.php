<?php

namespace App\Http\Controllers\User;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\product_order;
use App\Models\product_order_item;
use App\Models\Package;
use App\Models\Setting;
use App\Models\User;
use App\Models\Bonus;
use App\Models\wallettopup;
use App\Models\userpackage;
use App\Models\Withdrawal;
use App\Models\Transaction;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Mail;
 

class UserController extends Controller
{

 

protected function buildTree($user, $depth = 3)
{
    if (!$user || $depth === 0) return null;

    return [
        'id' => $user->id,
        'name' => $user->first_name . ' ' . $user->last_name,
        'username' => $user->username,
        'photo' => $user->photo ?? 'default.jpg',
        'position' => $user->position,
        'left' => $this->buildTree($user->leftChild, $depth - 1),
        'right' => $this->buildTree($user->rightChild, $depth - 1),
    ];
}


 


protected function buildSponsorTree($user, $depth = 3)
{
    if (!$user || $depth === 0) return null;

    // Always use sponsor_id for sponsor tree
    $sponsoredUsers = User::where('sponsor_id', $user->id)->get();

    return [
        'id' => $user->id,
        'name' => $user->first_name . ' ' . $user->last_name,
        'username' => $user->username,
        'photo' => $user->photo ?? 'default.jpg',
        'children' => $sponsoredUsers->map(function ($child) use ($depth) {
            return $this->buildSponsorTree($child, $depth - 1);
        })->toArray(),
    ];
}


protected function buildFullTree($user, $uplineLeg = null)
{
    if (!$user) {
        return null;
    }

    $binaryChildren = [];

    // Left child: always tagged as 'L' relative to root
    if ($user->leftChild) {
        $left = $this->buildFullTree($user->leftChild, 'L');
        if ($left) {
            $binaryChildren[] = $left;
        }
    }

    // Right child: always tagged as 'R' relative to root
    if ($user->rightChild) {
        $right = $this->buildFullTree($user->rightChild, 'R');
        if ($right) {
            $binaryChildren[] = $right;
        }
    }

    // Sponsor children: inherit parent’s leg, but only if no binary children exist
    $sponsorChildren = [];
    if (empty($binaryChildren)) {
        foreach ($user->sponsorChildren as $child) {
            $built = $this->buildFullTree($child, $uplineLeg);
            if ($built) {
                $sponsorChildren[] = $built;
            }
        }
    }

    return [
        'id'       => $user->id,
        'name'     => trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? '')),
        'username' => $user->username ?? '',
        'photo'    => $user->photo ?? 'default.jpg',
        'position' => $user->position ?? '',
        'leg'      => $uplineLeg, //  propagate root leg downwards
        'children' => array_values(array_merge($binaryChildren, $sponsorChildren)),
    ];
}


protected function buildFullTree_last($user)
{
    if (!$user) {
        return null;
    }

    $binaryChildren = [];

    // Handle left child
    if ($user->leftChild) {
        $left = $this->buildFullTree($user->leftChild);
        if ($left) {
            $left['leg'] = 'L';
            $binaryChildren[] = $left;
        }
    }

    // Handle right child
    if ($user->rightChild) {
        $right = $this->buildFullTree($user->rightChild);
        if ($right) {
            $right['leg'] = 'R';
            $binaryChildren[] = $right;
        }
    }

    // Handle sponsor children (only if not already left/right)
    $sponsorChildren = [];
    foreach ($user->sponsorChildren as $child) {
        if ($child
            && $child->id !== optional($user->leftChild)->id
            && $child->id !== optional($user->rightChild)->id
        ) {
            $built = $this->buildFullTree($child);
            if ($built) {
                $sponsorChildren[] = $built;
            }
        }
    }

    //  Only merge sponsor children if no binary children exist
    $children = !empty($binaryChildren) ? $binaryChildren : $sponsorChildren;

    return [
        'id'       => $user->id,
        'name'     => trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? '')),
        'username' => $user->username ?? '',
        'photo'    => $user->photo ?? 'default.jpg',
        'position' => $user->position ?? '',
        'children' => array_values($children),
    ];
}










public function paginate($id, Request $request)
{
    $offset = (int) $request->query('offset', 0);
    $user = User::find($id);

    if (!$user) {
        return response()->json(['error' => 'User not found'], 404);
    }

    $tree = $this->buildFullTree($user, 3, 3, $offset); // Load next 3 sponsor children

    return response()->json($tree);
}





    public function expand($id, Request $request)
    {
        $depth = (int) $request->query('depth', 3); // allow client to request depth
        $user = User::find($id);

        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        $subtree = $this->buildFullTree($user, $depth);

        return response()->json($subtree);
    }
 
 

 
public function index()
{
    $user = auth()->user();
        // Count binary downlines (based on parent_id)
    $downlineCount = User::where('parent_id', $user->id)->count();

    // Count sponsored users (based on sponsor_id)
    $sponsoredCount = User::where('sponsor_id', $user->id)->count();
    $withdrawaCount = Withdrawal::where('user_id', $user->id)->count();
    $bonuses = Bonus::where('user_id', $user->id)->latest()->get();
     $userpack = userpackage::where('user_id', $user->id)->latest()->get();
    $walletropup = wallettopup::where('user_id', $user->id)->latest()->get();
  $tree = $this->buildFullTree($user);
 $withdrawals = Withdrawal::where('user_id', $user->id)->latest()->get();
  $pendingPayments = User::where('userreg_id', $user->id)
        ->where('payment_status', 'pending')
        ->get();
 

   $downlines = User::where('parent_id', $user->id)
        ->with(['sponsor', 'upline'])
        ->get();

         

           $genelogy = $this->getAllDescendants($user);
 

   $orders = auth()->user()->productOrders()->with('items')->latest()->get();


   return view('user.dashboard', compact('pendingPayments','genelogy','downlines','orders','user','downlineCount', 'sponsoredCount', 'withdrawaCount', 'bonuses', 'userpack', 'withdrawals', 'walletropup','tree'));
}

private function getAllDescendants($user, $level = 1, &$all = null, $inheritedLeg = null)
{
    if ($all === null) {
        $all = collect();
    }

    // Get direct children
    $children = User::where('parent_id', $user->id)->get();

    foreach ($children as $child) {
        // Determine leg
        if ($level === 1) {
            // Direct children of the root: check left/right explicitly
            if ($child->id === optional($user->leftChild)->id) {
                $child->leg = 'Left';
            } elseif ($child->id === optional($user->rightChild)->id) {
                $child->leg = 'Right';
            } else {
                $child->leg = null; // fallback
            }
        } else {
            // Deeper descendants: inherit leg from ancestor branch
            $child->leg = $inheritedLeg;
        }

        // Attach generation level for display
        $child->generation_level = $level;

        // Upline and sponsor
        $child->upline  = User::find($child->parent_id);
        $child->sponsor = User::find($child->sponsor_id);

        $all->push($child);

        // Recurse deeper, passing down the leg
        $this->getAllDescendants($child, $level + 1, $all, $child->leg);
    }

    return $all;
}


public function showPaymentPage()
{
    $userCountry = auth()->user()->country;
    $nairaFee = setting('registration_fee'); // This is in Naira
    $usdRate = \App\Models\Setting::getUsdConversionRate();
    $usdFee = $nairaFee / $usdRate;
 $user = auth()->user();
    $registrationFee = Setting::getValue('registration_fee');
    return view('user.paymentPage', compact('registrationFee', 'user','userCountry', 'nairaFee', 'usdFee'));
}




public function processBankPayment(Request $request)
{
    $request->validate([
        'sendername' => 'required|string|max:255',
        'bank_name' => 'required|string|max:255',
        'transaction_no' => 'nullable|string|max:255|unique:transaction,transaction_no',
        'proof' => 'nullable|image|max:2048',
    ]);

    try {
        $transaction = new Transaction();
        $transaction->type = 'registration';
        $transaction->method = 'bank';
        $transaction->status = 'pending';
        $transaction->user_id = $request->user_id;
        $transaction->sendername = $request->sendername;
        $transaction->bank_name = $request->bank_name;
        $transaction->transaction_no = $request->transaction_no;

        if ($request->hasFile('proof')) {
            $image = $request->file('proof');
            $filename = time() . '_' . Str::uuid() . '.' . $image->getClientOriginalExtension();
            $image->move(public_path('payment_proofs'), $filename);
            $transaction->proof = 'payment_proofs/' . $filename;
        }

        $transaction->save();

        //  Send email to admin
        $adminEmail = 'devophostsolutions@gmail.com'; 
        $user = \App\Models\User::find($request->user_id);

        $data = [
            'user' => $user,
            'transaction' => $transaction,
        ];

        Mail::send(['html' => 'emails.registration_payment_proof'], ['data' => $data], function($message) {
    $message->to('devophostsolutions@gmail.com'); 
    $message->subject("New Registration Payment Proof Submitted");
});

        return redirect()->back()->with('success', 'Payment submitted successfully. Awaiting admin approval.');

    } catch (\Exception $e) {
        \Log::error('Payment submission failed: ' . $e->getMessage());
        return back()->with('error', 'Error: ' . $e->getMessage());
    }
}



public function initiatePaystackPayment(Request $request)
{
    $user = Auth::user();
    $reference = Str::uuid(); // unique transaction reference

    // Save pending transaction in DB
    $transaction = Transaction::create([
        'user_id' => $user->id,
        'type' => 'registration',
        'method' => 'paystack',
        'status' => 'pending',
        'transaction_no' => $reference,
    ]);

    $amount = setting('registration_fee', 5000) * 100; // Paystack uses kobo (multiply by 100)

    $data = [
        'email' => $user->email,
        'amount' => $amount,
        'reference' => $reference,
        'callback_url' => route('payment.paystack.callback'),
        'metadata' => [
            'user_id' => $user->id,
            'transaction_id' => $transaction->id,
            'custom_fields' => [
                [
                    'display_name' => 'Payment For',
                    'variable_name' => 'payment_type',
                    'value' => 'MLM Registration Fee'
                ],
            ]
        ],
    ];

    // Call Paystack API
    $response = Http::withHeaders([
        'Authorization' => 'Bearer ' . env('PAYSTACK_SECRET_KEY'),
        'Accept' => 'application/json',
    ])->post('https://api.paystack.co/transaction/initialize', $data);

    $res = $response->json();

    if (isset($res['status']) && $res['status'] === true && isset($res['data']['authorization_url'])) {
        return redirect($res['data']['authorization_url']);
    }

    return back()->with('error', 'Unable to initiate Paystack payment. Please try again.');
}



public function handlePaystackCallback(Request $request)
{
    $reference = $request->get('reference');

    $response = Http::withHeaders([
        'Authorization' => 'Bearer ' . env('PAYSTACK_SECRET_KEY'),
    ])->get("https://api.paystack.co/transaction/verify/{$reference}");

    $res = $response->json();

    if (isset($res['status']) && $res['status'] === true && $res['data']['status'] === 'success') {
        $transaction = Transaction::where('transaction_no', $reference)->first();

        if ($transaction && $transaction->status !== 'approved') {
            $transaction->update(['status' => 'approved']);

            // Mark user as paid
            $user = $transaction->user;
            $user->update(['payment_status' => 'approved']);

            // You can trigger MLM bonus distribution here
        }

        return redirect()->route('dashboard')->with('success', 'Payment successful!');
    }

    return redirect()->route('dashboard')->with('error', 'Payment verification failed.');
}


public function showPaystackPage()
{
    $user = Auth::user();
    $amount = setting('registration_fee', 5000); // in Naira

    return view('user.paymentPage', compact('user', 'amount'));
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


public function verifyPaystackPayment(Request $request)
{
    $reference = $request->query('reference');

    if (!$reference) {
        return redirect()->route('user.paymentPage')->with('error', 'No payment reference found.');
    }

    $response = Http::withOptions([
        'verify' => storage_path('cacert.pem'),
    ])->withToken(env('PAYSTACK_SECRET_KEY'))
      ->get("https://api.paystack.co/transaction/verify/{$reference}");

    if ($response->successful() && $response['data']['status'] === 'success') {
        $user = Auth::user();

        //  Create transaction record
        $transaction = Transaction::create([
            'user_id' => $user->id,
            'type' => 'registration',
            'method' => 'paystack',
            'status' => 'approved',
            'transaction_no' => $reference,
        ]);

        //  Assign binary position if missing
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

        // Approve transaction
        $transaction->update(['status' => 'approved']);

        //  Promote user to Starter
        $user->update([
            'payment_status'   => 'approved',
            'status'           => 'inactive',
            'transaction_pin'  => self::generateTransactionPin(),
            'user_rank'        => 'starter',
        ]);

        //  Trigger registration bonus
        \App\Services\BonusService::distribute($user);

        //  Notify user
        $data = ['user' => $user];
        Mail::send(['html' => 'emails.registration_approved'], ['data' => $data], function($message) use ($user) {
            $message->to($user->email);
            $message->subject("Your Registration Payment is Approved");
        });

        //  Notify uplines (up to 4 levels)
        $upline = $user->upline;
        $levels = [
            1 => '1st',
            2 => '2nd',
            3 => '3rd',
            4 => '4th',
        ];

        foreach ($levels as $level => $label) {
            if (!$upline) break;

            $bonusAmount = setting("starter_bonus_{$label}", 0);

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

        //  Log out user and redirect
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'Payment successful! Please log in with your details.');
    }

    return redirect()->route('user.paymentPage')->with('error', 'Payment verification failed.');
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


 
public function pacakgepage()
{
    $user = Auth::user();

    // Lowest → Highest
    $rank = ['standard','basic','classic','premium','executive','vip'];

    // Current plan index (or -1 if none)
    $currentIndex = -1;
    if ($user && $user->user_plan) {
        $normalized = strtolower(trim($user->user_plan));
        $idx = array_search($normalized, $rank);
        $currentIndex = ($idx === false) ? -1 : $idx;
    }

    // All packages (optionally sort by rank)
    $all = \App\Models\Package::all()
        ->sortBy(fn($p) => array_search(strtolower(trim($p->packageName)), $rank))
        ->values();

    // Find the user's last approved package and its price
    $lastPackagePrice = 0.0;
    $lastUserPkg = \App\Models\userpackage::where('user_id', $user->id ?? 0)
        ->where('status', 'approved')
        ->latest('id')
        ->first();

    if ($lastUserPkg) {
        $prevPkg = \App\Models\Package::find($lastUserPkg->package_id);
        if ($prevPkg) {
            $lastPackagePrice = (float) $prevPkg->price;
        }
    }

    return view('user.package', compact('all','rank','currentIndex','lastPackagePrice'));
}




public function productpage()
{
   return view('user.order_product');
}

public function topWalletPage()
{
   return view('user.topup_wallet');
}

public function withdrawalPage()
{
   return view('user.withdrawal');
}


public function withdrawaluserPage()
{
   return view('user.withdrawal_page');
}

 
 public function manualWithdraw(Request $request)
{
    $request->validate([
        'amount' => 'required|numeric|min:100',
        'transaction_pin' => 'required|string',
    ]);

    $user = Auth::user();

    // Check transaction pin
    if ($user->transaction_pin !== $request->transaction_pin) {
        return back()->withErrors(['transaction_pin' => 'Invalid transaction pin.']);
    }

    // Check if user already has a pending withdrawal
    $pendingWithdrawal = DB::table('payout')
        ->where('user_id', $user->id)
        ->where('status', 'pending')
        ->first();

    if ($pendingWithdrawal) {
        return back()->withErrors(['amount' => 'You already have a pending withdrawal request.']);
    }

    // Check wallet balance
    if ($request->amount > $user->withdraw_wallet_balance) {
        return back()->withErrors(['amount' => 'Insufficient wallet balance.']);
    }

    // Withdrawal fee
    $withdrawalFee = 500;
    $netAmount = $request->amount - $withdrawalFee;

    if ($netAmount <= 0) {
        return back()->withErrors(['amount' => 'Withdrawal amount must be greater than the fee of ₦500.']);
    }

    // Create withdrawal record
    $withdrawalId = DB::table('payout')->insertGetId([
        'user_id'        => $user->id,
        'amount'         => $request->amount,   // original requested amount
        'withdrawal_fee' => $withdrawalFee,     // store fee
        'amount_payable' => $netAmount,         // net amount after fee
        'status'         => 'pending',
        'type'           => 'manual',
        'created_at'     => now(),
        'updated_at'     => now(),
    ]);

    // Update user's amount_payable field
    $user->amount_payable = $netAmount;
    $user->save();

    $withdrawal = DB::table('payout')->where('id', $withdrawalId)->first();

    // Notify user
    Mail::send(
        ['html' => 'emails.withdrawal_user_notify'],
        ['user' => $user, 'withdrawal' => $withdrawal],
        function ($message) use ($user) {
            $message->to($user->email)
                ->subject('Withdrawal Request Submitted');
        }
    );

    // Notify admin
    Mail::send(
        ['html' => 'emails.withdrawal_admin_notify'],
        ['user' => $user, 'withdrawal' => $withdrawal],
        function ($message) {
            $message->to('devophostsolutions@gmail.com')
                ->subject('New Withdrawal Request Submitted');
        }
    );

    return back()->with('success', 'Withdrawal request submitted and pending approval.');
}





public function requestWithdrawal(Request $request)
{
    $user = Auth::user();

    // Check if user has a pending withdrawal
    $hasPending = Withdrawal::where('user_id', $user->id)
        ->where('status', 'pending')
        ->exists();

    if ($hasPending) {
        return back()->with('error', 'You already have a pending withdrawal request. Please wait for admin approval.');
    }

    // Validate request
    $request->validate([
        'amount' => 'required|numeric|min:100',
        'transaction_pin' => 'required|string',
    ]);

    // Check sufficient balance
    if ($request->amount > $user->withdraw_wallet_balance) {
        return back()->with('error', 'Insufficient withdrawable balance.');
    }

    // Check transaction pin
    if ($user->transaction_pin !== $request->transaction_pin) {
        return back()->with('error', 'Invalid transaction PIN.');
    }

    // Create withdrawal request
    $withdrawal = Withdrawal::create([
        'user_id' => $user->id,
        'amount' => $request->amount,
        'status' => 'approved',

    ]);

    // Send email to admin and user
    Mail::send(['html' => 'emails.withdrawal_user_notify'], ['user' => $user, 'withdrawal' => $withdrawal], function ($message) use ($user) {
        $message->to($user->email)
            ->subject('Withdrawal Request Submitted');
    });

    Mail::send(['html' => 'emails.withdrawal_admin_notify'], ['user' => $user, 'withdrawal' => $withdrawal], function ($message) {
        $message->to('devophostsolutions@gmail.com')  
            ->subject('New Withdrawal Request Submitted');
    });

    return back()->with('success', 'Withdrawal request submitted successfully.');
}



public function withdraw(Request $request)
{
    $request->validate([
        'amount' => 'required|numeric|min:100',
        'transaction_pin' => 'required|string',
    ]);

    $user = Auth::user();

    // Check transaction pin
    if ($user->transaction_pin !== $request->transaction_pin) {
        return back()->withErrors(['transaction_pin' => 'Invalid transaction pin.']);
    }

    // Check wallet balance
    if ($request->amount > $user->withdraw_wallet_balance) {
        return back()->withErrors(['amount' => 'Insufficient wallet balance.']);
    }

    // Create Paystack transfer recipient
   // $recipientResponse = Http::withToken(env('PAYSTACK_SECRET_KEY'))
     //   ->post('https://api.paystack.co/transferrecipient', [
      //      'type' => 'nuban',
       //     'name' => $user->account_name,
      //      'account_number' => $user->account_no,
        //    'bank_code' => $user->bank_code,
//'currency' => 'NGN',
    //    ]);
    $recipientResponse = Http::withOptions([
    'verify' => false // Only for local development
])
->withToken(env('PAYSTACK_SECRET_KEY'))
->post('https://api.paystack.co/transferrecipient', [
    'type' => 'nuban',
    'name' => $user->account_name,
    'account_number' => $user->account_no,
    'bank_code' => $user->bank_code,
    'currency' => 'NGN',
]);


    if (!$recipientResponse->successful()) {
        return back()->withErrors(['amount' => 'Failed to create transfer recipient.']);
    }

    $recipientCode = $recipientResponse['data']['recipient_code'];

    // Initiate Paystack transfer
  //  $transferResponse = Http::withToken(env('PAYSTACK_SECRET_KEY'))
      //  ->post('https://api.paystack.co/transfer', [
      //      'source' => 'balance',
      //      'amount' => $request->amount * 100, // Paystack uses kobo
      //      'recipient' => $recipientCode,
    //        'reason' => 'User withdrawal',
   //     ]);

$transferResponse = Http::withOptions([
    'verify' => false // Only for local development
])
->withToken(env('PAYSTACK_SECRET_KEY'))
->post('https://api.paystack.co/transfer', [
    'source' => 'balance',
    'amount' => $request->amount * 100, // Paystack uses kobo
    'recipient' => $recipientCode,
    'reason' => 'User withdrawal',
]);

        

    if (!$transferResponse->successful()) {
        return back()->withErrors(['amount' => 'Transfer failed.']);
    }

    // Deduct from wallet
    $user->withdraw_wallet_balance -= $request->amount;
    $user->save();

    // Log withdrawal
    $withdrawal = Withdrawal::create([
        'user_id' => $user->id,
        'amount' => $request->amount,
        'status' => 'approved',
    ]);

    // Notify user
    Mail::send(['html' => 'emails.withdrawal_user_notify'], ['user' => $user, 'withdrawal' => $withdrawal], function ($message) use ($user) {
        $message->to($user->email)
            ->subject('Withdrawal Request Submitted');
    });

    // Notify admin
    Mail::send(['html' => 'emails.withdrawal_admin_notify'], ['user' => $user, 'withdrawal' => $withdrawal], function ($message) {
        $message->to('devophostsolutions@gmail.com')
            ->subject('New Withdrawal Request Submitted');
    });

    return back()->with('success', 'Withdrawal successful. Funds are on the way!');
}




public function withdraw2(Request $request)
{
    $user = Auth::user();

    // Validate input
    $request->validate([
        'amount' => 'required|numeric|min:100|max:' . $user->withdraw_wallet_balance,
        'transaction_pin' => 'required|string',
    ]);

    // Check transaction pin
    if ($request->transaction_pin !== $user->transaction_pin) {
        return back()->with('error', 'Invalid transaction pin.');
    }

    // Check bank details
    if (!$user->account_name || !$user->account_no || !$user->bank_code) {
        return back()->with('error', 'Bank details are incomplete.');
    }

    // Step 1: Create transfer recipient
    $recipientResponse = Http::withToken(env('PAYSTACK_SECRET_KEY'))->post('https://api.paystack.co/transferrecipient', [
        'type' => 'nuban',
        'name' => $user->account_name,
        'account_number' => $user->account_no,
        'bank_code' => $user->bank_code,
        'currency' => 'NGN',
    ]);

    if (!$recipientResponse->successful() || empty($recipientResponse['data']['recipient_code'])) {
        return back()->with('error', 'Failed to create transfer recipient.');
    }

    $recipientCode = $recipientResponse['data']['recipient_code'];

    // Step 2: Initiate transfer
    $transferResponse = Http::withToken(env('PAYSTACK_SECRET_KEY'))->post('https://api.paystack.co/transfer', [
        'source' => 'balance',
        'amount' => $request->amount * 100, // in kobo
        'recipient' => $recipientCode,
        'reason' => 'Wallet withdrawal for ' . $user->name,
    ]);

    if (!$transferResponse->successful() || $transferResponse['data']['status'] !== 'success') {
        return back()->with('error', 'Paystack transfer failed.');
    }

    // Step 3: Deduct wallet balance
    $user->withdraw_wallet_balance -= $request->amount;
    $user->save();

    // Step 4: Record withdrawal
    $withdrawal = Withdrawal::create([
        'user_id' => $user->id,
        'amount' => $request->amount,
        'status' => 'approved',
        'reference' => $transferResponse['data']['reference'] ?? null,
        'paid_at' => now(),
    ]);

    // Step 5: Notify user
    Mail::send('emails.withdrawal_approved', ['user' => $user, 'withdrawal' => $withdrawal], function ($message) use ($user) {
        $message->to($user->email);
        $message->subject("Withdrawal Approved & Paid");
    });

    return back()->with('success', 'Withdrawal successful. Funds have been sent to your bank account.');

}






  


public function submitTopup(Request $request)
{
    $user = Auth::user();

    // Check if user already has a pending top-up
    $hasPending = wallettopup::where('user_id', $user->id)
        ->where('status', 'pending')
        ->exists();

    if ($hasPending) {
        return back()->with('error', 'You already have a pending wallet top-up request. Please wait for admin approval.');
    }

    $request->validate([
        'amount' => 'required|numeric|min:100',
        'payment_method' => 'required|in:bank,online',
        'bank_name' => 'required_if:payment_method,bank',
        'account_name' => 'required_if:payment_method,bank',
        'payment_proof' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
    ]);

    $data = $request->only(['amount', 'payment_method', 'bank_name', 'account_name']);
    $data['user_id'] = $user->id;

    if ($request->hasFile('payment_proof')) {
        $filename = time().'_'.$request->file('payment_proof')->getClientOriginalName();
        $request->file('payment_proof')->move(public_path('payment_proofs'), $filename);
        $data['payment_proof'] = 'payment_proofs/'.$filename;
    }

    $data['status'] = $request->payment_method === 'online' ? 'approved' : 'pending';

    $topup = wallettopup::create($data);

    //  Notify Admin if payment method is bank
    if ($request->payment_method === 'bank') {
        $adminEmail = 'devophostsolutions@gmail.com'; 

        \Mail::send('emails.wallet_topup_notification', ['user' => $user, 'topup' => $topup], function ($message) use ($adminEmail) {
            $message->to($adminEmail)
                    ->subject('New Wallet Top-up Proof Submitted');
        });
    }

    //  Auto-credit for online payment
    if ($data['status'] === 'approved') {
        $user->deposit_wallet_balance += $data['amount'];
        $user->save();
    }

    return back()->with('success', 'Top-up request submitted successfully.');
}



private function creditWalletAndNotify(User $user, wallettopup $topup)
{
    $user->deposit_wallet_balance += $topup->amount;
    $user->save();

    \Mail::send('emails.wallet_topup_approved', ['user' => $user, 'topup' => $topup], function ($message) use ($user) {
        $message->to($user->email);
        $message->subject('Wallet Top-up Approved');
    });
}





public function verifyPaystackTopup(Request $request)
{
    $user = Auth::user();
    $reference = $request->reference;
    $amount = $request->amount;

   // $response = Http::withToken(env('PAYSTACK_SECRET_KEY'))
       // ->get("https://api.paystack.co/transaction/verify/{$reference}");

        $response = Http::withOptions([
    'verify' => storage_path('cacert.pem'),
])->withToken(env('PAYSTACK_SECRET_KEY'))
  ->get("https://api.paystack.co/transaction/verify/{$reference}");

    if ($response->successful() && $response['data']['status'] === 'success') {
        $hasPending = wallettopup::where('user_id', $user->id)
            ->where('status', 'pending')
            ->exists();

        if ($hasPending) {
            return redirect()->route('user.topup_wallet')->with('error', 'You already have a pending wallet top-up request.');
        }

        $topup = wallettopup::create([
            'user_id' => $user->id,
            'amount' => $amount,
            'payment_method' => 'online',
            'status' => 'approved',
        ]);

        $this->creditWalletAndNotify($user, $topup);

        return redirect()->route('user.dashboard')->with('success', 'Wallet topped up successfully!');
    }

    return redirect()->route('user.topup_wallet')->with('error', 'Payment verification failed.');
}





 













public function profilePage()
{
        $user = Auth::user();

    // Fetch bank list from Paystack
    //$response = Http::withToken(env('PAYSTACK_SECRET_KEY'))
       // ->get('https://api.paystack.co/bank', ['country' => 'nigeria']);

       $response = Http::withOptions([
    'verify' => false
])
->withToken(env('PAYSTACK_SECRET_KEY'))
->get('https://api.paystack.co/bank', ['country' => 'nigeria']);


    $banks = $response->json()['data'] ?? [];

    return view('user.myprofile', [
        'user' => $user,
        'banks' => $banks,
    ]);
 
}






public function verifyAccount(Request $request)
{
    $request->validate([
        'account_no' => 'required|string|size:10',
        'bank_code' => 'required|string',
    ]);

    $response = Http::withOptions([
        'verify' => false // Use cacert.pem for production
    ])
    ->withToken(env('PAYSTACK_SECRET_KEY'))
    ->get('https://api.paystack.co/bank/resolve', [
        'account_number' => $request->account_no,
        'bank_code' => $request->bank_code,
    ]);

    if ($response->successful() && isset($response['data']['account_name'])) {
        return response()->json([
            'success' => true,
            'account_name' => $response['data']['account_name'],
        ]);
    }

    return response()->json([
        'success' => false,
        'message' => 'Account verification failed.',
    ], 422);
}





public function updateBankDetails(Request $request)
{
    $request->validate([
        'bank_code' => 'required|string',
        'account_no' => 'required|string|size:10',
        'account_name' => 'required|string',
    ]);

    $user = Auth::user();

    // Only update if values are different
    $user->update([
        'bank_code' => $request->bank_code,
        'account_no' => $request->account_no,
        'account_name' => $request->account_name,
'bank_name' => $request->bank_name,
        
    ]);

    return back()->with('success', 'Bank details updated successfully.');
}



  public function updateProfile(Request $request, User $User)
    {
        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|string',
            'profile_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:5048',
        ]);

        try {
            $data = $request->only([
                'name', 'profile_image', 'email'    
            ]);

            foreach ($data as $key => $value) {
                $data[$key] = Purify::clean($value);
            }

            
     if ($request->hasFile('profile_photo_path')) {
    $data['profile_photo_path'] = uploadProfileImage($request->file('profile_photo_path'));
}

            $User->update($data);

            return redirect()->back()->with('success', 'Profile updated successfully!');
        } catch (\Exception $e) {
            Log::error('Profile update failed: ' . $e->getMessage());

            return redirect()->back()->with('error', 'Something went wrong.');
        }
    }


    public function updatePassword(Request $request)
{
    $request->validate([
        'password' => 'required|string|min:8|confirmed',
    ]);

    $user = Auth::user();
    $user->password = Hash::make($request->password);
    $user->save();

    // Log the user out
    Auth::logout();

    // Invalidate session and redirect to login
    $request->session()->invalidate();
    $request->session()->regenerateToken();

    return redirect()->route('login')->with('success', 'Password changed successfully. Please log in again.');
}


public function createMember(Request $request)
{
 return view('user.create_member');
}


public function registerPost(Request $request)
{
    $request->validate([
        'first_name' => 'required|string',
        'last_name'  => 'required|string',
        'email'      => 'required|email|unique:users,email',
        'phone'      => 'required|string|unique:users,phone',
        'state'      => 'required|string',
        'city'       => 'required|string',
        'country'    => 'required|string',
        'address'    => 'required|string',
        'upline_username' => 'required|exists:users,username',
        'sponsor_username' => 'nullable|string|exists:users,username',
        'kin_name'   => 'nullable|string',
        'kin_phone'  => 'nullable|string',
        'kin_email'  => 'nullable|email',
        'kin_address'=> 'nullable|string',
        'bank_name'  => 'required|string',
        'account_no' => 'required|string',
        'account_name' => 'required|string',
        'username'   => 'required|string|unique:users,username',
        'password'   => 'required|string|confirmed|min:6',
    ]);

    $uplineUser = User::where('username', $request->upline_username)->first();
    $sponsorUser = $request->sponsor_username ? User::where('username', $request->sponsor_username)->first() : null;

    // Determine parent/position logic
    $leftOccupied = User::where('parent_id', $uplineUser->id)->where('position', 'left')->exists();
    $rightOccupied = User::where('parent_id', $uplineUser->id)->where('position', 'right')->exists();
    if ($leftOccupied && $rightOccupied) {
        return back()->withErrors(['upline_username' => 'Selected upline already has both legs occupied.'])->withInput();
    }
    $parentId = $uplineUser->id;
    $position = $leftOccupied ? 'right' : 'left';

    $user = User::create([
        'first_name' => $request->first_name,
        'last_name'  => $request->last_name,
        'email'      => $request->email,
        'phone'      => $request->phone,
        'state'      => $request->state,
        'city'       => $request->city,
        'country'    => $request->country,
        'address'    => $request->address,
        'username'   => $request->username,
        'password'   => Hash::make($request->password),
        'parent_id'  => $parentId,
        'sponsor_id' => $sponsorUser ? $sponsorUser->id : $uplineUser->id,
        'position'   => $position,
        'kin_name'   => $request->kin_name,
        'kin_phone'  => $request->kin_phone,
        'kin_email'  => $request->kin_email,
        'kin_address'=> $request->kin_address,
        'bank_name'  => $request->bank_name,
        'account_no' => $request->account_no,
        'account_name' => $request->account_name,
    ]);

    $user->assignRole('user');
    Auth::login($user);

    return redirect()->route('user.paymentPage')->with('success', 'Registration successful!');
}


// For admin
public function completeAdminRegistration($finalData) {
    $user = $this->createUser($finalData);
    $this->triggerBonuses($user);
    Auth::login($user);
    return redirect()->route('superadmin.dashboard')
        ->with('success', 'User registered successfully by admin.');
}

// For user
public function completeUserRegistration($finalData) {
    $user = $this->createUser($finalData);
    Auth::login($user);
    return redirect()->route('user.paymentPage')
        ->with('success', 'Proceed to payment to complete registration.');
}


 
}

