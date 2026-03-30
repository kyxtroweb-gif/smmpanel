<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Payment extends Model
{
    protected $fillable = [
        'user_id',
        'payment_method_id',
        'method',         // fallback: method slug string
        'amount',         // original amount user paid
        'amount_bonus',
        'bonus_percent',
        'net_amount',     // final credited (amount + bonus - fees)
        'transaction_id', // our internal reference
        'user_txn_id',    // user's TXN ID they entered (for manual methods)
        'user_amount',    // amount user claims they paid
        'status',
        'note',
        'payment_data',   // JSON: extra data
        'completed_at',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:4',
            'amount_bonus' => 'decimal:4',
            'bonus_percent' => 'decimal:2',
            'net_amount' => 'decimal:4',
            'user_amount' => 'decimal:4',
            'completed_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }

    protected $hidden = ['payment_data'];

    protected static function booted(): void
    {
        static::creating(function (Payment $payment) {
            if (empty($payment->transaction_id)) {
                $payment->transaction_id = 'DEP-' . strtoupper(Str::random(10));
            }
        });
    }

    public const STATUS_PENDING = 'pending';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_FAILED = 'failed';
    public const STATUS_APPROVED = 'approved'; // approved by admin, auto-credits

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function paymentMethod()
    {
        return $this->belongsTo(PaymentMethod::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'pending' => '<span class="badge bg-warning text-dark">Pending</span>',
            'approved' => '<span class="badge bg-info">Approved</span>',
            'completed' => '<span class="badge bg-success">Completed</span>',
            'failed' => '<span class="badge bg-danger">Failed</span>',
            default => $this->status,
        };
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    /**
     * Credit user balance after successful payment.
     */
    public function creditUser(): void
    {
        $user = $this->user;
        if (!$user) return;

        $user->balance += (float) $this->net_amount;
        $user->total_deposited = ($user->total_deposited ?? 0) + (float) $this->net_amount;
        $user->save();

        // Create transaction record
        Transaction::create([
            'user_id' => $user->id,
            'payment_id' => $this->id,
            'type' => 'deposit',
            'amount' => (float) $this->net_amount,
            'balance' => $user->balance,
            'reference' => 'payment:' . $this->transaction_id,
            'description' => "Deposit via {$this->method} ({$this->transaction_id})",
        ]);

        $this->status = self::STATUS_COMPLETED;
        $this->completed_at = now();
        $this->save();
    }
}
