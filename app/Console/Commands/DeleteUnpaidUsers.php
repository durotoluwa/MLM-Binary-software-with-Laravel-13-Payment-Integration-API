<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class DeleteUnpaidUsers extends Command
{
    protected $signature = 'users:delete-unpaid';
    protected $description = 'Send warning to unpaid users and delete them after 21 days of registration';

    public function handle()
    {
        $today = Carbon::now();

        // Step 1: Send warning emails to unpaid users who are 18 days old
        $warnUsers = User::where('payment_status', 'unpaid')
            ->whereDate('created_at', '<=', $today->copy()->subDays(18))
            ->whereDate('created_at', '>', $today->copy()->subDays(19)) // exactly day 18
            ->get();

        foreach ($warnUsers as $user) {
            $data = [
                'fistname' => $user->first_name,
                'lastname' => $user->last_name,
                'username' => $user->username,
                'message' => "Your account will be deleted in 3 days if payment is not completed."
            ];

            Mail::send(
                ['html' => 'emails.payment_warning'], 
                ['data' => $data], 
                function ($message) use ($user) {
                    $message->to($user->email);
                    $message->subject("Payment Reminder - Your Account Will Be Deleted Soon");
                }
            );

            $this->info("Warning email sent to: {$user->email}");
        }

        // Step 2: Delete unpaid users older than 21 days
        $deleteUsers = User::where('payment_status', 'unpaid')
            ->whereDate('created_at', '<=', $today->copy()->subDays(21))
            ->get();

        if ($deleteUsers->isEmpty()) {
            $this->info('No unpaid users found for deletion.');
            return;
        }

        DB::beginTransaction();
        try {
            foreach ($deleteUsers as $user) {
                $this->info("Deleting unpaid user: {$user->username} ({$user->email})");
                $user->delete();
            }
            DB::commit();
            $this->info('Unpaid users deleted successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('Failed to delete unpaid users: ' . $e->getMessage());
        }
    }
}
