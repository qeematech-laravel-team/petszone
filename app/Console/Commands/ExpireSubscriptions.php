<?php

namespace App\Console\Commands;

use App\Models\VendorSubscription;
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
    protected $description = 'Mark subscriptions as expired when their end date has passed';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Checking for expired subscriptions...');

        // Get all active subscriptions that have passed their end date
        $expiredSubscriptions = VendorSubscription::shouldExpire()->get();

        if ($expiredSubscriptions->isEmpty()) {
            $this->info('No expired subscriptions found.');

            return Command::SUCCESS;
        }

        $count = $expiredSubscriptions->count();
        $this->info("Found {$count} subscription(s) to expire.");

        $bar = $this->output->createProgressBar($count);
        $bar->start();

        $expiredCount = 0;

        foreach ($expiredSubscriptions as $subscription) {
            try {
                $subscription->update(['status' => 'expired']);
                $expiredCount++;
                $bar->advance();
                // check if the vendor
            } catch (\Exception $e) {
                $this->newLine();
                $this->error("Failed to expire subscription ID {$subscription->id}: {$e->getMessage()}");
            }
        }

        $bar->finish();
        $this->newLine(2);

        if ($expiredCount > 0) {
            $this->info("Successfully expired {$expiredCount} subscription(s).");
        }

        return Command::SUCCESS;
    }
}
