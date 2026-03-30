<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Dripfeed extends Model
{
    protected $fillable = [
        'user_id',
        'order_id',
        'service_id',
        'link',
        'runs',
        'interval',
        'quantity',
        'total_quantity',
        'total_charged',
        'status',
        'runs_completed',
        'next_run_at',
    ];

    protected function casts(): array
    {
        return [
            'total_charged' => 'decimal:4',
            'runs_completed' => 'integer',
            'runs' => 'integer',
            'interval' => 'integer',
            'quantity' => 'integer',
            'total_quantity' => 'integer',
            'next_run_at' => 'datetime',
        ];
    }

    public const STATUS_ACTIVE = 'active';
    public const STATUS_PAUSED = 'paused';
    public const STATUS_COMPLETED = 'completed';

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function subOrders()
    {
        return $this->hasMany(Order::class, 'drip_id');
    }

    public function isDue(): bool
    {
        return $this->status === self::STATUS_ACTIVE
            && $this->next_run_at !== null
            && $this->next_run_at->isPast();
    }

    public function calculateNextRun(): void
    {
        $this->increment('runs_completed');
        $this->next_run_at = now()->addMinutes($this->interval);
        if ($this->runs_completed >= $this->runs) {
            $this->status = self::STATUS_COMPLETED;
        }
        $this->save();
    }
}
