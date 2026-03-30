<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Order extends Model
{
    protected $fillable = [
        'order_id',
        'user_id',
        'service_id',
        'link',
        'quantity',
        'charge',
        'cost',
        'profit',
        'status',
        'start_count',
        'remains',
        'drip_id',
        'provider_order_id',
        'api_response',
    ];

    protected function casts(): array
    {
        return [
            'charge' => 'decimal:4',
            'cost' => 'decimal:4',
            'profit' => 'decimal:4',
            'quantity' => 'integer',
            'start_count' => 'integer',
            'remains' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Order $order) {
            if (empty($order->order_id)) {
                $order->order_id = strtoupper(Str::random(10));
            }
        });
    }

    public const STATUS_PENDING = 'pending';
    public const STATUS_PROCESSING = 'processing';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_PARTIAL = 'partial';
    public const STATUS_CANCELLED = 'cancelled';
    public const STATUS_REFUNDED = 'refunded';

    public static array $statuses = [
        self::STATUS_PENDING,
        self::STATUS_PROCESSING,
        self::STATUS_COMPLETED,
        self::STATUS_PARTIAL,
        self::STATUS_CANCELLED,
        self::STATUS_REFUNDED,
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function refills()
    {
        return $this->hasMany(Refill::class);
    }

    public function dripfeed()
    {
        return $this->belongsTo(Dripfeed::class, 'drip_id');
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'pending' => '<span class="badge bg-secondary">Pending</span>',
            'processing' => '<span class="badge bg-primary">Processing</span>',
            'completed' => '<span class="badge bg-success">Completed</span>',
            'partial' => '<span class="badge bg-warning text-dark">Partial</span>',
            'cancelled' => '<span class="badge bg-danger">Cancelled</span>',
            'refunded' => '<span class="badge bg-info">Refunded</span>',
            default => $this->status,
        };
    }

    public function canRefill(): bool
    {
        return $this->service && $this->service->refill
            && in_array($this->status, [self::STATUS_COMPLETED, self::STATUS_PARTIAL]);
    }

    public function canCancel(): bool
    {
        return $this->service && $this->service->cancel
            && in_array($this->status, [self::STATUS_PENDING, self::STATUS_PROCESSING]);
    }
}
