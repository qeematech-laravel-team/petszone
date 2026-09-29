<?php

namespace App\Console\Commands;

use App\Models\VendorSubscription;
use Illuminate\Console\Command;

class ActivateScheduledSubscriptions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'subscriptions:activate-scheduled';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Activate scheduled subscriptions that have reached their start date';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Checking for scheduled subscriptions to activate...');

        $today = now()->startOfDay();

        // Get inactive subscriptions that should start today or earlier
        $scheduledSubscriptions = VendorSubscription::where('status', 'inactive')
            ->whereDate('start_date', '<=', $today)
            ->whereDate('end_date', '>=', $today)
            ->with(['vendor', 'plan'])
            ->get();

        if ($scheduledSubscriptions->isEmpty()) {
            $this->info('No scheduled subscriptions found to activate.');

            return Command::SUCCESS;
        }

        $count = $scheduledSubscriptions->count();
        $this->info("Found {$count} scheduled subscription(s) to activate.");

        $bar = $this->output->createProgressBar($count);
        $bar->start();

        $activatedCount = 0;

        foreach ($scheduledSubscriptions as $subscription) {
            try {
                $vendor = $subscription->vendor;
                $plan = $subscription->plan;

                // Deactivate any other active subscriptions for this vendor
                $activeSubscriptions = VendorSubscription::where('vendor_id', $vendor->id)
                    ->where('status', 'active')
                    ->where('id', '!=', $subscription->id)
                    ->get();

                foreach ($activeSubscriptions as $activeSub) {
                    $activeSub->update(['status' => 'inactive']);
                }

                // Check if this is a downgrade and apply restrictions
                $currentActive = VendorSubscription::where('vendor_id', $vendor->id)
                    ->where('status', 'active')
                    ->where('id', '!=', $subscription->id)
                    ->with('plan')
                    ->first();

                if ($currentActive && $currentActive->plan) {
                    $isDowngrade = $this->isDowngrade($currentActive->plan, $plan);

                    if ($isDowngrade) {
                        // Remove featured products if not allowed
                        if (! $plan->can_feature_products) {
                            $vendor->products()->featured()->update(['is_featured' => false]);
                        }

                        // Deactivate excess products if over limit
                        if ($plan->max_products_count) {
                            $excessCount = $vendor->products()->active()->count() - $plan->max_products_count;
                            if ($excessCount > 0) {
                                $vendor->products()->active()
                                    ->latest()
                                    ->limit($excessCount)
                                    ->update(['is_active' => false]);
                            }
                        }
                    }
                }

                // Activate the scheduled subscription
                $subscription->update(['status' => 'active']);

                // Update vendor plan info
                $vendor->update([
                    'plan_id' => $plan->id,
                    'subscription_start' => $subscription->start_date,
                    'subscription_end' => $subscription->end_date,
                ]);

                $activatedCount++;
                $bar->advance();
            } catch (\Exception $e) {
                $this->newLine();
                $this->error("Failed to activate subscription ID {$subscription->id}: {$e->getMessage()}");
            }
        }

        $bar->finish();
        $this->newLine(2);

        if ($activatedCount > 0) {
            $this->info("Successfully activated {$activatedCount} scheduled subscription(s).");
        }

        return Command::SUCCESS;
    }

    /**
     * Check if switching from current plan to new plan is a downgrade
     */
    private function isDowngrade($currentPlan, $newPlan): bool
    {
        if ($newPlan->getRawOriginal('price') < $currentPlan->getRawOriginal('price')) {
            return true;
        }

        if ($currentPlan->can_feature_products && ! $newPlan->can_feature_products) {
            return true;
        }

        $currentMax = $currentPlan->max_products_count; // null = unlimited
        $newMax = $newPlan->max_products_count;

        if ($currentMax === null && $newMax !== null) {
            return true;
        }

        if ($currentMax !== null && $newMax !== null && $currentMax > $newMax) {
            return true;
        }

        return false;
    }
}
