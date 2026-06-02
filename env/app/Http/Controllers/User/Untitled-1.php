if ($request->payment_method === 'online') {
    $buyer = Auth::user(); // logged-in user
    $targetUser = User::findOrFail($request->member_id); // member receiving package

    try {
        $request->validate([
            'paystack_reference' => 'required|string',
            'member_id' => 'required|exists:users,id',
            'package_id' => 'required|exists:package,id',
            'product' => 'required|array',
        ]);

        $reference = $request->paystack_reference;

        // Verify Paystack payment
        $verifyResponse = //Http::withHeaders([
           // 'Authorization' => 'Bearer ' . env('PAYSTACK_SECRET_KEY'),
          //  'Accept' => 'application/json',
       // ])->get("https://api.paystack.co/transaction/verify/{$reference}");

       Http::withOptions(['verify' => false])
    ->withHeaders([
        'Authorization' => 'Bearer ' . env('PAYSTACK_SECRET_KEY'),
        'Accept' => 'application/json',
    ])
    ->get("https://api.paystack.co/transaction/verify/{$reference}");


        if (!$verifyResponse->successful() || ($verifyResponse['data']['status'] ?? '') !== 'success') {
            return response()->json([
                'status' => 'error',
                'message' => 'Payment verification failed.'
            ], 400);
        }

        $amountPaid = $verifyResponse['data']['amount'] / 100;

        // ✅ Define package and CPTs before transaction
        $newPackage = Package::findOrFail($request->package_id);
        $ctpToAdd = $newPackage->cpts;
        $amountToPay = $newPackage->price;

        $previousPackage = UserPackage::where('user_id', $targetUser->id)
            ->where('status', 'approved')
            ->latest()
            ->first();

        $isUpgrade = $previousPackage && $newPackage->id > $previousPackage->package_id;
        $previousPackageId = $isUpgrade ? $previousPackage->package_id : null;

        if ($amountPaid < $amountToPay) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid payment amount.'
            ], 400);
        }

        DB::transaction(function () use (
            $targetUser,
            $newPackage,
            $previousPackageId,
            $amountToPay,
            $ctpToAdd,
            $request,
            $isUpgrade
        ) {
            // Update CPTs
            $targetUser->increment('total_ctp', $ctpToAdd);
            $targetUser->increment('p_c_cpts', $ctpToAdd);
            $targetUser->increment('current_p_c_cpts', $ctpToAdd);
            $targetUser->increment('current_c_cpts', $ctpToAdd);

            $incentiveRanks = DB::table('incentive_settings')->pluck('rank')->toArray();
            $rankToSet = in_array($targetUser->user_rank, $incentiveRanks)
                ? $targetUser->user_rank
                : 'Regular';

            $targetUser->update([
                'status' => 'active',
                'user_rank' => $rankToSet,
                'user_plan' => $newPackage->packageName,
            ]);

            // Uplines and bonuses
            $this->addCTPToUplines($targetUser, $ctpToAdd);
            $this->handleMatchingBonus($targetUser, $ctpToAdd);

            if ($targetUser->parent_id) {
                $parent = User::find($targetUser->parent_id);
                if ($parent) {
                    $this->evaluateUserIncentives($parent);
                }
            }

            $userPackage = UserPackage::create([
                'user_id' => $targetUser->id,
                'package_id' => $newPackage->id,
                'previous_package_id' => $previousPackageId,
                'amount_paid' => $amountToPay,
                'payment_method' => 'online',
                'status' => 'approved',
                'package_order_status' => 'approved',
            ]);

            foreach ($request->product as $row) {
                $qty = (int) ($row['qty'] ?? 0);
                if ($qty <= 0) continue;

                DB::table('package_product_orders')->insert([
                    'user_id' => $targetUser->id,
                    'package_id' => $newPackage->id,
                    'product_id' => $row['id'],
                    'package_order_id' => $userPackage->id,
                    'qty' => $qty,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            $this->payReferralBonus(
                $targetUser,
                $newPackage->price,
                $isUpgrade,
                $previousPackageId
            );
        });

        return response()->json([
            'status' => 'success',
            'message' => 'Package purchased successfully!',
            'redirect_url' => route('user.package'),
        ]);

} catch (\Exception $e) {
    \Log::error('Online purchase error', [
        'message' => $e->getMessage(),
        'line'    => $e->getLine(),
        'file'    => $e->getFile(),
        'trace'   => $e->getTraceAsString(),
    ]);

    return response()->json([
        'status'  => 'error',
        'message' => 'Something went wrong. Please try again.'
    ], 500);
}
}
}
