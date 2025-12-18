<?php

namespace App\Console\Commands;

use App\Models\UserSubscription;
use App\Models\SubscriptionPlan;
use Illuminate\Console\Command;

class ExpireSubscriptions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'subscriptions:expire';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Expire subscriptions that have passed their expiry date and assign free plan';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Checking for expired subscriptions...');

        // Find all active subscriptions that have expired
        $expiredSubscriptions = UserSubscription::where('status', 'active')
            ->whereNotNull('expires_at')
            ->where('expires_at', '<', now())
            ->get();

        if ($expiredSubscriptions->isEmpty()) {
            $this->info('No expired subscriptions found.');
            return 0;
        }

        $freePlan = SubscriptionPlan::where('name', 'free')->first();

        foreach ($expiredSubscriptions as $subscription) {
            // Mark as expired
            $subscription->update(['status' => 'expired']);

            // Assign free plan to user
            if ($freePlan) {
                $subscription->user->subscriptions()->create([
                    'plan_id' => $freePlan->id,
                    'starts_at' => now(),
                    'expires_at' => null, // Free plan never expires
                    'status' => 'active',
                    'amount_paid' => 0,
                ]);

                $this->info("User {$subscription->user->name} (ID: {$subscription->user_id}) subscription expired. Assigned free plan.");
            } else {
                $this->warn("User {$subscription->user_id} subscription expired but no free plan found!");
            }
        }

        $this->info("Expired {$expiredSubscriptions->count()} subscriptions.");

        return 0;
    }
}
