<?php

namespace App\Console\Commands;

use App\Models\Provider;
use Illuminate\Console\Command;

class SyncProviderBalances extends Command
{
    protected $signature = 'providers:sync-balances';
    protected $description = 'Sync balances from all active providers';

    public function handle(): int
    {
        $providers = Provider::where('is_active', true)->get();

        if ($providers->isEmpty()) {
            $this->info('No active providers.');
            return 0;
        }

        $this->info("Syncing {$providers->count()} provider balances...");
        foreach ($providers as $provider) {
            try {
                $provider->syncBalance();
                $this->line("{$provider->name}: balance = {$provider->balance}");
            } catch (\Exception $e) {
                $this->error("{$provider->name}: " . $e->getMessage());
            }
        }

        return 0;
    }
}
