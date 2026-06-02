<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\userpackage;

class EvaluateIncentives extends Command
{
    protected $signature = 'incentives:evaluate';
    protected $description = 'Evaluate incentives for all users with recent package purchases';

    public function handle()
    {
        $users = User::whereHas('packages', function ($query) {
            $query->whereDate('created_at', today());
        })->get();

        foreach ($users as $user) {
            $this->info("Evaluating incentives for user: {$user->username}");

            app('App\Http\Controllers\User\UserPackageController')->evaluateUserIncentives($user);

            if (!\App\Models\incentives::where('user_id', $user->id)->whereDate('achieved_at', today())->exists()) {
                $user->user_rank = 'Regular';
                $user->save();
            }
        }

        $this->info('Incentive evaluation completed.');
    }
}

