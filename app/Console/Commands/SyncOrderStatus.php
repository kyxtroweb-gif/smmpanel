<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Models\Transaction;
use App\Services\OrderDispatchService;
use Illuminate\Console\Command;

class SyncOrderStatus extends Command
{
    protected $signature = 'orders:sync-status';
    protected $description = 'Sync order statuses from providers (pending/processing orders only)';

    public function handle(OrderDispatchService $dispatch): int
    {
        $orders = Order::whereIn('status', ['pending', 'processing'])
            ->whereNotNull('provider_order_id')
            ->where('created_at', '>', now()->subDays(7))
            ->limit(100)
            ->get();

        if ($orders->isEmpty()) {
            $this->info('No orders to sync.');
            return 0;
        }

        $this->info("Syncing {$orders->count()} orders...");
        $count = $dispatch->batchSyncStatus($orders);
        $this->info("Synced {$count} orders.");

        return 0;
    }
}
