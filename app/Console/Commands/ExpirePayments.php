<?php

namespace App\Console\Commands;

use App\Models\Payment;
use Illuminate\Console\Command;

class ExpirePayments extends Command
{
    protected $signature = 'payments:expire';
    protected $description = 'Expire pending payments that have passed their expiry time';

    public function handle(): int
    {
        $expired = Payment::where('status', 'pending')
            ->where('expires_at', '<=', now())
            ->update(['status' => 'failed', 'note' => \DB::raw("CONCAT(IFNULL(note,''), '\n[Expired: auto-expired by system]')")]);

        if ($expired > 0) {
            $this->info("Expired {$expired} payments.");
        } else {
            $this->info('No payments to expire.');
        }

        return 0;
    }
}
