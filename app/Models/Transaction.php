<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $fillable = [
        'user_id',
        'payment_id',
        'type',       // deposit, order, refund, bonus
        'amount',
        'balance',    // balance after this transaction
        'reference',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:4',
            'balance' => 'decimal:4',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function payment()
    {
        return $this->belongsTo(Payment::class);
    }

    public function getTypeLabelAttribute(): string
    {
        return match ($this->type) {
            'deposit' => '<span class="badge bg-success">Deposit</span>',
            'order' => '<span class="badge bg-primary">Order</span>',
            'refund' => '<span class="badge bg-info">Refund</span>',
            'bonus' => '<span class="badge bg-warning text-dark">Bonus</span>',
            default => $this->type,
        };
    }
}
