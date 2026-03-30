<?php

namespace App\Console\Commands;

use App\Models\Dripfeed;
use App\Services\OrderDispatchService;
use Illuminate\Console\Command;

class ProcessDripfeeds extends Command
{
    protected $signature = 'orders:process-dripfeeds';
    protected $description = 'Process due dripfeed orders';

    public function handle(OrderDispatchService $dispatch): int
    {
        $dripfeeds = Dripfeed::where('status', 'active')
            ->where('next_run_at', '<=', now())
            ->where('runs_completed', '<', \DB::raw('runs'))
            ->limit(50)
            ->get();

        if ($dripfeeds->isEmpty()) {
            $this->info('No dripfeeds to process.');
            return 0;
        }

        $this->info("Processing {$dripfeeds->count()} dripfeeds...");
        foreach ($dripfeeds as $dripfeed) {
            try {
                $dispatch->processDripfeed($dripfeed);
                $this->line("Processed dripfeed #{$dripfeed->id}");
            } catch (\Exception $e) {
                $this->error("Dripfeed #{$dripfeed->id} failed: " . $e->getMessage());
            }
        }

        return 0;
    }
}
