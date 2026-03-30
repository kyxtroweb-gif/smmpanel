<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Refill extends Model
{
    protected $fillable = [
        'refill_id',
        'order_id',
        'user_id',
        'quantity_requested',
        'provider_refill_id',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'quantity_requested' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Refill $refill) {
            if (empty($refill->refill_id)) {
                $refill->refill_id = 'REF-' . strtoupper(Str::random(8));
            }
        });
    }

    public const STATUS_PENDING = 'pending';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_FAILED = 'failed';

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
