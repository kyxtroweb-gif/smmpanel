<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Dripfeed;
use App\Models\Subscription;
use App\Models\Refill;
use App\Models\Provider;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class OrderDispatchService
{
    /**
     * Dispatch an order to the provider API.
     */
    public function dispatch(Order $order): bool
    {
        $order->load('service', 'provider');

        // If no provider, mark as manual processing
        if (!$order->provider_id || !$order->provider) {
            $order->status = 'pending';
            $order->provider_order_id = 'MANUAL-' . $order->id;
            $order->save();

            Log::info("Order {$order->order_number} marked for manual processing (no provider)");
            return true;
        }

        $provider = $order->provider;

        // Skip if provider is inactive
        if (!$provider->isActive()) {
            $order->status = 'pending';
            $order->provider_order_id = 'PROVIDER_OFFLINE-' . $order->id;
            $order->save();

            Log::warning("Order {$order->order_number} - Provider {$provider->name} is offline");
            return true;
        }

        try {
            // Call provider API
            $result = $this->callProviderApi($provider, 'place', [
                'service' => $order->service->provider_service_id,
                'link' => $order->link,
                'quantity' => $order->quantity,
                'order_id' => $order->id,
            ]);

            if ($result['success']) {
                $order->provider_order_id = $result['order_id'] ?? null;
                $order->status = 'pending';
                $order->placed_at = now();

                // Update start count if returned
                if (isset($result['start_count'])) {
                    $order->start_count = $result['start_count'];
                }

                $order->save();

                Log::info("Order {$order->order_number} dispatched to provider {$provider->name}");
                return true;
            } else {
                // Handle provider error
                return $this->handleProviderError($order, $result);
            }

        } catch (\Exception $e) {
            Log::error("Order dispatch error for {$order->order_number}: " . $e->getMessage());

            // Queue for retry or mark as failed
            $order->status = 'pending';
            $order->error_message = $e->getMessage();
            $order->save();

            return false;
        }
    }

    /**
     * Sync order status with provider.
     */
    public function syncStatus(Order $order): bool
    {
        $order->load('service', 'provider');

        // Skip if no provider order ID
        if (!$order->provider_order_id || str_starts_with($order->provider_order_id, 'MANUAL-')) {
            return true;
        }

        $provider = $order->provider;

        if (!$provider || !$provider->isActive()) {
            return true;
        }

        try {
            $result = $this->callProviderApi($provider, 'status', [
                'order_id' => $order->provider_order_id,
            ]);

            if ($result['success']) {
                $this->updateOrderFromStatus($order, $result);
                return true;
            }

        } catch (\Exception $e) {
            Log::error("Order status sync error for {$order->order_number}: " . $e->getMessage());
        }

        return false;
    }

    /**
     * Update order based on status response.
     */
    protected function updateOrderFromStatus(Order $order, array $result): void
    {
        $oldStatus = $order->status;

        // Map provider status to our status
        $statusMap = [
            'pending' => 'pending',
            'in_progress' => 'in_progress',
            'processing' => 'in_progress',
            'completed' => 'completed',
            'completed' => 'completed',
            'partial' => 'partial',
            'cancelled' => 'cancelled',
            'cancelled' => 'cancelled',
            'refunded' => 'refunded',
        ];

        $newStatus = $statusMap[strtolower($result['status'] ?? '')] ?? $order->status;

        // Update order fields
        if (isset($result['remains'])) {
            $order->remains = (int) $result['remains'];
        }

        if (isset($result['start_count'])) {
            $order->start_count = (int) $result['start_count'];
        }

        if (isset($result['current_count'])) {
            $order->current_count = (int) $result['current_count'];
        }

        $order->status = $newStatus;

        // Set completion time
        if (in_array($newStatus, ['completed', 'cancelled', 'refunded']) && !$order->completed_at) {
            $order->completed_at = now();
        }

        $order->save();

        // Handle order completion
        if ($newStatus === 'completed' && $oldStatus !== 'completed') {
            $this->handleOrderCompletion($order);
        }

        // Handle partial delivery
        if ($newStatus === 'partial' && $oldStatus !== 'partial') {
            $this->handlePartialDelivery($order);
        }

        Log::info("Order {$order->order_number} status updated: {$oldStatus} -> {$newStatus}");
    }

    /**
     * Handle order completion.
     */
    protected function handleOrderCompletion(Order $order): void
    {
        // Update user stats
        $user = $order->user;
        $user->total_orders_completed = ($user->total_orders_completed ?? 0) + 1;
        $user->save();

        // Update service stats
        $service = $order->service;
        $service->total_orders = ($service->total_orders ?? 0) + 1;
        $service->total_delivered = ($service->total_delivered ?? 0) + $order->quantity;
        $service->save();
    }

    /**
     * Handle partial delivery.
     */
    protected function handlePartialDelivery(Order $order): void
    {
        // Calculate refund amount
        $delivered = $order->quantity - ($order->remains ?? 0);
        $refundQuantity = $order->quantity - $delivered;
        $refundAmount = $order->price * $refundQuantity;

        if ($refundAmount > 0) {
            try {
                DB::beginTransaction();

                // Refund user
                $user = $order->user;
                $user->balance += $refundAmount;
                $user->save();

                // Create refund transaction
                \App\Models\Transaction::create([
                    'user_id' => $user->id,
                    'type' => 'partial_refund',
                    'amount' => $refundAmount,
                    'balance' => $user->balance,
                    'reference' => 'partial_refund:order:' . $order->id,
                    'description' => "Partial refund for Order #{$order->order_number} ({$refundQuantity} not delivered)",
                ]);

                $order->refund_amount = $refundAmount;
                $order->save();

                DB::commit();

                Log::info("Order {$order->order_number} partial refund: \${$refundAmount}");

            } catch (\Exception $e) {
                DB::rollBack();
                Log::error("Partial refund failed for {$order->order_number}: " . $e->getMessage());
            }
        }
    }

    /**
     * Process refill request with provider.
     */
    public function processRefill(Order $order): bool
    {
        $order->load('service', 'provider');

        // Get pending refill
        $refill = Refill::where('order_id', $order->id)
            ->whereIn('status', ['pending', 'processing'])
            ->first();

        if (!$refill) {
            Log::warning("No pending refill found for order {$order->order_number}");
            return false;
        }

        // If no provider, mark as manual
        if (!$order->provider_id || !$order->provider) {
            $refill->status = 'pending_manual';
            $refill->save();
            return true;
        }

        $provider = $order->provider;

        if (!$provider->isActive()) {
            $refill->status = 'pending_manual';
            $refill->save();
            return true;
        }

        try {
            $result = $this->callProviderApi($provider, 'refill', [
                'order_id' => $order->provider_order_id,
                'refill_id' => $refill->id,
            ]);

            if ($result['success']) {
                $refill->status = 'in_progress';
                $refill->refill_order_id = $result['refill_order_id'] ?? null;
                $refill->save();

                Log::info("Refill for order {$order->order_number} sent to provider");
                return true;
            } else {
                $refill->status = 'rejected';
                $refill->notes = $result['error'] ?? 'Refill rejected by provider';
                $refill->save();

                return false;
            }

        } catch (\Exception $e) {
            Log::error("Refill processing error for {$order->order_number}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Process a single drip-feed run.
     */
    public function processDripfeed(Dripfeed $dripfeed): bool
    {
        $dripfeed->load('order.service', 'order.provider');

        if ($dripfeed->status !== 'active') {
            return false;
        }

        if ($dripfeed->remaining_runs <= 0) {
            $dripfeed->status = 'completed';
            $dripfeed->completed_at = now();
            $dripfeed->save();

            // Check parent order and update if all runs done
            $this->checkDripfeedOrderCompletion($dripfeed->order);
            return true;
        }

        $order = $dripfeed->order;
        $provider = $order->provider;

        try {
            if (!$provider || !$provider->isActive()) {
                // Mark for later processing
                $dripfeed->next_run_at = now()->addMinutes(5);
                $dripfeed->save();
                return false;
            }

            $result = $this->callProviderApi($provider, 'place', [
                'service' => $order->service->provider_service_id,
                'link' => $order->link,
                'quantity' => $dripfeed->quantity_per_run,
                'order_id' => $order->id,
                'dripfeed_id' => $dripfeed->id,
            ]);

            if ($result['success']) {
                $dripfeed->remaining_runs--;
                $dripfeed->runs_completed++;
                $dripfeed->last_run_at = now();

                if ($dripfeed->remaining_runs > 0) {
                    $dripfeed->next_run_at = now()->addMinutes($dripfeed->interval);
                } else {
                    $dripfeed->status = 'completed';
                    $dripfeed->completed_at = now();
                }

                $dripfeed->save();

                // Update order quantity
                $order->quantity = $dripfeed->quantity_per_run * $dripfeed->runs_completed;
                $order->save();

                Log::info("Dripfeed run completed for order {$order->order_number}. Remaining: {$dripfeed->remaining_runs}");

                // If this was the last run, check order completion
                if ($dripfeed->status === 'completed') {
                    $this->checkDripfeedOrderCompletion($order);
                }

                return true;
            }

        } catch (\Exception $e) {
            Log::error("Dripfeed processing error for {$dripfeed->id}: " . $e->getMessage());
        }

        // Retry in next interval
        $dripfeed->next_run_at = now()->addMinutes($dripfeed->interval);
        $dripfeed->save();

        return false;
    }

    /**
     * Check and update dripfeed parent order status.
     */
    protected function checkDripfeedOrderCompletion(Order $order): void
    {
        $dripfeed = $order->dripfeed;

        if (!$dripfeed) {
            return;
        }

        if ($dripfeed->status === 'completed') {
            $order->status = 'completed';
            $order->completed_at = now();
            $order->save();

            $this->handleOrderCompletion($order);
        }
    }

    /**
     * Process subscription/auto-order.
     */
    public function processSubscription(Subscription $subscription): bool
    {
        $subscription->load('order.service', 'order.provider');

        if ($subscription->status !== 'active') {
            return false;
        }

        // Check if max posts reached
        if ($subscription->posts_sent >= $subscription->posts_count) {
            $subscription->status = 'completed';
            $subscription->completed_at = now();
            $subscription->save();

            $this->checkSubscriptionOrderCompletion($subscription->order);
            return true;
        }

        // Check if user has balance
        $user = $subscription->user;
        if ($user->balance < $subscription->price_per_post) {
            $subscription->status = 'paused_no_balance';
            $subscription->save();
            Log::warning("Subscription {$subscription->id} paused - insufficient balance");
            return false;
        }

        $order = $subscription->order;
        $provider = $order->provider;

        try {
            if (!$provider || !$provider->isActive()) {
                $subscription->next_run_at = now()->addMinutes(5);
                $subscription->save();
                return false;
            }

            $result = $this->callProviderApi($provider, 'place', [
                'service' => $order->service->provider_service_id,
                'link' => $subscription->link,
                'quantity' => 1,
                'order_id' => $order->id,
                'subscription_id' => $subscription->id,
            ]);

            if ($result['success']) {
                // Deduct cost from user
                $user->balance -= $subscription->price_per_post;
                $user->save();

                // Create transaction
                \App\Models\Transaction::create([
                    'user_id' => $user->id,
                    'type' => 'subscription_charge',
                    'amount' => -$subscription->price_per_post,
                    'balance' => $user->balance,
                    'reference' => 'subscription:' . $subscription->id,
                    'description' => "Subscription post for Order #{$order->order_number}",
                ]);

                $subscription->posts_sent++;
                $subscription->total_spent += $subscription->price_per_post;
                $subscription->last_run_at = now();
                $subscription->next_run_at = now()->addMinutes($subscription->interval);

                if ($subscription->posts_sent >= $subscription->posts_count) {
                    $subscription->status = 'completed';
                    $subscription->completed_at = now();
                }

                $subscription->save();

                // Update order total
                $order->total = $subscription->total_spent;
                $order->save();

                Log::info("Subscription post {$subscription->posts_sent}/{$subscription->posts_count} for order {$order->order_number}");

                return true;
            }

        } catch (\Exception $e) {
            Log::error("Subscription processing error for {$subscription->id}: " . $e->getMessage());
        }

        $subscription->next_run_at = now()->addMinutes($subscription->interval);
        $subscription->save();

        return false;
    }

    /**
     * Check and update subscription parent order status.
     */
    protected function checkSubscriptionOrderCompletion(Order $order): void
    {
        $subscription = $order->subscription;

        if (!$subscription) {
            return;
        }

        if ($subscription->status === 'completed') {
            $order->status = 'completed';
            $order->total = $subscription->total_spent;
            $order->completed_at = now();
            $order->save();

            $this->handleOrderCompletion($order);
        }
    }

    /**
     * Call provider API with retry logic.
     */
    protected function callProviderApi(Provider $provider, string $action, array $data): array
    {
        $maxRetries = 3;
        $retryDelay = 1000; // milliseconds

        for ($attempt = 1; $attempt <= $maxRetries; $attempt++) {
            try {
                return $this->executeProviderCall($provider, $action, $data);
            } catch (\Exception $e) {
                Log::warning("Provider API attempt {$attempt} failed: " . $e->getMessage());

                if ($attempt < $maxRetries) {
                    usleep($retryDelay * 1000);
                    $retryDelay *= 2;
                } else {
                    throw $e;
                }
            }
        }

        return ['success' => false, 'error' => 'Max retries exceeded'];
    }

    /**
     * Execute actual HTTP call to provider API.
     */
    protected function executeProviderCall(Provider $provider, string $action, array $data): array
    {
        $url = $this->getProviderApiUrl($provider, $action);

        $headers = [
            'Authorization' => 'Bearer ' . $provider->api_key,
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ];

        $response = Http::withHeaders($headers)
            ->timeout(30)
            ->post($url, $data);

        if (!$response->successful()) {
            throw new \Exception("Provider API returned status " . $response->status());
        }

        $body = $response->json();

        // Standard response format handling
        if (isset($body['error'])) {
            return [
                'success' => false,
                'error' => $body['error'],
            ];
        }

        return [
            'success' => true,
            'order_id' => $body['order'] ?? $body['order_id'] ?? null,
            'status' => $body['status'] ?? null,
            'remains' => $body['remains'] ?? null,
            'start_count' => $body['start_count'] ?? null,
            'current_count' => $body['current_count'] ?? null,
        ];
    }

    /**
     * Get the appropriate API URL for provider action.
     */
    protected function getProviderApiUrl(Provider $provider, string $action): string
    {
        $baseUrl = rtrim($provider->api_url, '/');

        $endpoints = [
            'place' => '/orders',
            'status' => '/orders/status',
            'refill' => '/orders/refill',
        ];

        return $baseUrl . ($endpoints[$action] ?? '/orders');
    }

    /**
     * Handle provider error.
     */
    protected function handleProviderError(Order $order, array $result): bool
    {
        $error = $result['error'] ?? 'Unknown error';

        // Check if error is retryable
        $retryableErrors = ['rate_limit', 'timeout', 'server_error', 'try_again'];

        if (in_array(strtolower($result['error_code'] ?? ''), $retryableErrors)) {
            $order->status = 'pending';
            $order->error_message = $error;
            $order->save();

            // Schedule retry
            $this->scheduleRetry($order);
            return false;
        }

        // Non-retryable error - fail order and refund
        try {
            DB::beginTransaction();

            $order->status = 'failed';
            $order->error_message = $error;
            $order->completed_at = now();
            $order->save();

            // Refund user
            $user = $order->user;
            $user->balance += $order->total;
            $user->save();

            \App\Models\Transaction::create([
                'user_id' => $user->id,
                'type' => 'refund',
                'amount' => $order->total,
                'balance' => $user->balance,
                'reference' => 'failed_order:' . $order->id,
                'description' => "Refund for failed Order #{$order->order_number}",
            ]);

            DB::commit();

            Log::info("Order {$order->order_number} failed - refunded: \${$order->total}");

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Failed to refund order {$order->order_number}: " . $e->getMessage());
        }

        return false;
    }

    /**
     * Schedule order for retry.
     */
    protected function scheduleRetry(Order $order): void
    {
        // In production, dispatch to queue
        // For now, we'll set a retry timestamp
        $order->retry_at = now()->addMinutes(5);
        $order->save();

        Log::info("Order {$order->order_number} scheduled for retry");
    }

    /**
     * Batch sync multiple orders.
     */
    public function batchSyncStatus(array $orderIds): array
    {
        $orders = Order::whereIn('id', $orderIds)
            ->whereNotNull('provider_order_id')
            ->where('status', '!=', 'completed')
            ->where('status', '!=', 'cancelled')
            ->where('status', '!=', 'refunded')
            ->get();

        $results = [
            'synced' => 0,
            'failed' => 0,
            'completed' => 0,
        ];

        foreach ($orders as $order) {
            try {
                $oldStatus = $order->status;
                $this->syncStatus($order);

                if ($order->fresh()->status === 'completed') {
                    $results['completed']++;
                } else {
                    $results['synced']++;
                }
            } catch (\Exception $e) {
                $results['failed']++;
            }
        }

        return $results;
    }

    /**
     * Process pending dripfeeds (called by scheduler).
     */
    public function processPendingDripfeeds(): int
    {
        $dripfeeds = Dripfeed::where('status', 'active')
            ->where('next_run_at', '<=', now())
            ->limit(100)
            ->get();

        $processed = 0;

        foreach ($dripfeeds as $dripfeed) {
            if ($this->processDripfeed($dripfeed)) {
                $processed++;
            }
        }

        return $processed;
    }

    /**
     * Process pending subscriptions (called by scheduler).
     */
    public function processPendingSubscriptions(): int
    {
        $subscriptions = Subscription::where('status', 'active')
            ->where('next_run_at', '<=', now())
            ->limit(100)
            ->get();

        $processed = 0;

        foreach ($subscriptions as $subscription) {
            if ($this->processSubscription($subscription)) {
                $processed++;
            }
        }

        return $processed;
    }

    /**
     * Retry failed orders.
     */
    public function retryFailedOrders(): int
    {
        $orders = Order::where('status', 'pending')
            ->where('retry_at', '<=', now())
            ->whereNotNull('provider_order_id')
            ->where('provider_order_id', 'NOT LIKE', 'MANUAL-%')
            ->where('provider_order_id', 'NOT LIKE', 'PROVIDER_OFFLINE-%')
            ->limit(50)
            ->get();

        $retried = 0;

        foreach ($orders as $order) {
            if ($this->dispatch($order)) {
                $retried++;
            }
        }

        return $retried;
    }
}
