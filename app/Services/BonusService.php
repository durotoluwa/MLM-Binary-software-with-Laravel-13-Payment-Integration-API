<?php

namespace App\Services;

use App\Models\User;
use App\Models\Bonus;

class BonusService
{
public static function distribute(User $user)
{
    \Log::info("==> Starting registration bonus distribution for user ID: {$user->id}");

    $sponsor = $user->sponsor;
    $percentages = [22.75, 6.125, 4.375, 1.75]; // For levels 1–4
    $registrationAmount = setting('registration_fee', 0);
    $level = 1;

    foreach ($percentages as $percentage) {
        if (!$sponsor) {
            \Log::info("No sponsor found at level {$level}. Stopping.");
            break;
        }

        $bonusAmount = ($registrationAmount * $percentage) / 100;

        // Credit sponsor's withdraw wallet
        $sponsor->withdraw_wallet_balance += $bonusAmount;
        $sponsor->save();

     // Record the bonus
// Credit record for the sponsor (receiver)
Bonus::create([
    'user_id'        => $sponsor->id,     // sponsor/upline gets credit
    'amount'         => $bonusAmount,
    'source_user_id' => $user->id,        // buyer is the source
    'status'         => 'credit',
    'type'           => 'registration',
    'description'    => "Registration bonus (level {$level}) from user {$user->username}",
]);

// Debit record for the buyer (payer)
Bonus::create([
    'user_id'        => $user->id,        // buyer gets debit
    'amount'         => $bonusAmount,
    'source_user_id' => $sponsor->id,     // sponsor is the receiver
    'status'         => 'debit',
    'type'           => 'registration',
    'description'    => "Registration debit (level {$level}) paid to sponsor {$sponsor->username}",
]);

        \Log::info("Paid ₦{$bonusAmount} as registration bonus to sponsor ID {$sponsor->id} (level {$level})");

        // Move to next sponsor
        $sponsor = $sponsor->sponsor;
        $level++;
    }
}


}
