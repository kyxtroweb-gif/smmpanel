<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class PaymentMethod extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'type',         // 'manual' (QR/TXN) or 'automatic' (gateway)
        'description',
        'logo',
        'qr_image',     // path to QR code image for manual methods
        'is_active',
        'is_automatic', // true = gateway, false = manual QR/TXN entry
        'min_amount',
        'max_amount',
        'fixed_charge',
        'percent_charge',
        'bonus_percent',
        'bonus_threshold', // min amount to qualify for bonus
        'instructions',
        'fields',       // JSON: dynamic fields e.g. [{name: "upi_id", label: "Your UPI ID"}]
        'credentials',  // JSON: API keys, wallet addresses, etc.
        'sort_order',
        'requires_admin_approval', // for manual methods, require admin approve
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'is_automatic' => 'boolean',
            'requires_admin_approval' => 'boolean',
            'min_amount' => 'decimal:2',
            'max_amount' => 'decimal:2',
            'fixed_charge' => 'decimal:2',
            'percent_charge' => 'decimal:2',
            'bonus_percent' => 'decimal:2',
            'bonus_threshold' => 'decimal:2',
            'sort_order' => 'integer',
        ];
    }

    protected $hidden = ['credentials', 'fields'];

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function getCredentialsArrayAttribute(): array
    {
        return json_decode($this->credentials ?? '{}', true);
    }

    public function getFieldsArrayAttribute(): array
    {
        return json_decode($this->fields ?? '[]', true);
    }

    /**
     * Is this a manual payment method where user enters TXN ID manually?
     */
    public function isManual(): bool
    {
        return !$this->is_automatic;
    }

    /**
     * Calculate net amount after fees and bonuses.
     */
    public function calculateNetAmount(float $amount): array
    {
        $fee = (float) $this->fixed_charge + ($amount * (float) ($this->percent_charge ?? 0) / 100);
        $bonus = 0;
        if ($this->bonus_percent > 0) {
            $threshold = (float) ($this->bonus_threshold ?? 0);
            if ($amount >= $threshold) {
                $bonus = $amount * (float) $this->bonus_percent / 100;
            }
        }
        $net = $amount - $fee + $bonus;

        return [
            'fee' => round($fee, 4),
            'bonus' => round($bonus, 4),
            'net' => round($net, 4),
        ];
    }
}
