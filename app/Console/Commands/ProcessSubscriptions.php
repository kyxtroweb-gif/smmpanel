<?php

namespace App\Console\Commands;

use App\Models\Subscription;
use App\Services\OrderDispatchService;
use Illuminate\Console\Command;

class ProcessSubscriptions extends Command
{
    protected $signature = 'orders:process-subscriptions';
    protected $description = 'Process due subscription auto-orders';

    public function handle(OrderDispatchService $dispatch): int
    {
        $subscriptions = Subscription::where('status', 'active')
            ->where(function ($q) {
                $q->whereNull('expiry')->orWhere('expiry', '>', now());
            })
            ->where(function ($q) {
                $q->where('posts', -1)
                    ->orWhereRaw('posts_completed < posts');
            })
            ->where(function ($q) {
                $q->whereNull('last_order_at')
                    ->orWhere('last_order_at', '<=', now()->subMinutes(30));
            })
            ->limit(50)
            ->get();

        if ($subscriptions->isEmpty()) {
            $this->info('No subscriptions to process.');
            return 0;
        }

        $this->info("Processing {$subscriptions->count()} subscriptions...");
        foreach ($subscriptions as $subscription) {
            try {
                $dispatch->processSubscription($subscription);
                $this->line("Processed subscription #{$subscription->id}");
            } catch (\Exception $e) {
                $this->error("Subscription #{$subscription->id} failed: " . $e->getMessage());
            }
        }

        return 0;
    }
}
