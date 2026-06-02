<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Payout;
use Illuminate\Support\Facades\DB;

class AutoWithdraw extends Command
{
    /**
     * The name and signature of the console command.
     *
     * You can run it manually with: php artisan withdraw:auto
     */
    protected $signature = 'withdraw:auto';

    /**
     * The console command description.
     */
    protected $description = 'Auto withdraw all users with withdraw_wallet_balance > 0 every Sunday midnight';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $users = User::where('withdraw_wallet_balance', '>', 0)->get();

        foreach ($users as $user) {
            DB::transaction(function () use ($user) {
                // Create payout record
                Payout::create([
                    'user_id' => $user->id,
                    'amount'  => $user->withdraw_wallet_balance,
                    'status'  => 'pending',
                ]);
            });
        }

        $this->info('Auto withdrawal created for all eligible users.');
    }
}
