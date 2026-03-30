<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Subscription extends Model
{
    protected $fillable = [
        'sub_id',
        'user_id',
        'service_id',
        'link',
        'posts',
        'quantity',
        'delay',
        'expiry',
        'total_charged',
        'status',
        'posts_completed',
        'last_order_at',
    ];

    protected function casts(): array
    {
        return [
            'total_charged' => 'decimal:4',
            'posts' => 'integer',
            'quantity' => 'integer',
            'delay' => 'integer',
            'posts_completed' => 'integer',
            'last_order_at' => 'datetime',
            'expiry' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Subscription $sub) {
            if (empty($sub->sub_id)) {
                $sub->sub_id = 'SUB-' . strtoupper(Str::random(8));
            }
        });
    }

    public const STATUS_ACTIVE = 'active';
    public const STATUS_PAUSED = 'paused';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_CANCELLED = 'cancelled';

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function isDue(): bool
    {
        if ($this->status !== self::STATUS_ACTIVE) {
            return false;
        }
        if ($this->expiry && $this->expiry->isPast()) {
            return false;
        }
        if ($this->posts !== -1 && $this->posts_completed >= $this->posts) {
            return false;
        }
        // Run every 30 minutes if posts = -1 (infinite)
        if ($this->last_order_at === null) {
            return true;
        }
        return $this->last_order_at->addMinutes(30)->isPast();
    }
}
