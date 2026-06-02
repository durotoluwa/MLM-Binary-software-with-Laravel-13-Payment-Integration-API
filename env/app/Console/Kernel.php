<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected function schedule(Schedule $schedule): void
    {
        // Run daily — logic inside command will check if it's 21 days since registration
        $schedule->command('users:delete-unpaid')->daily();

       $schedule->command('incentives:evaluate')->everyMinute();

       $schedule->command('withdraw:auto') ->timezone('Africa/Lagos') ->sundays() ->at('00:00');


        // Pay unilevel bonuses automatically at 00:05 on the 28th of every month
        $schedule->command('bonus:pay-unilevel')->monthlyOn(28, '00:05');

            $schedule->call(function () {
        User::query()->update([
            'current_c_cpts' => 0,
            'current_p_c_cpts' => 0,
        ]);
    })->monthlyOn(1, '00:00');
    }

    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }

 

 

}
