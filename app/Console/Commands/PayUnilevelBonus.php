<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Bonus;
use Carbon\Carbon;
use DB;

class PayUnilevelBonus extends Command
{
    protected $signature = 'bonus:pay-unilevel';
    protected $description = 'Pay unilevel bonuses on the 28th of the month';

    public function handle()
    {
        $today = Carbon::today();
        
        if ($today->day != 28) {
            $this->info('Today is not the 28th. No payouts.');
            return;
        }

        DB::beginTransaction();
        try {
            $users = User::where('unilevel_wallet_balance', '>', 0)->get();

            foreach ($users as $user) {
                $amount = $user->unilevel_wallet_balance;

                // Move bonus to withdraw wallet
                $user->withdraw_wallet_balance += $amount;
                $user->unilevel_wallet_balance = 0;
                $user->save();

                // Mark bonus records as paid
                Bonus::where('user_id', $user->id)
                    ->where('type', 'unilevel')
                    ->where('is_paid', 0)
                    ->update(['is_paid' => 1]);

                $this->info("Paid ₦{$amount} to user ID {$user->id}");
            }

            DB::commit();
            $this->info('All unilevel bonuses paid successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('Error: ' . $e->getMessage());
        }
    }
}
