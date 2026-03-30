<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    protected $fillable = [
        'category_id',
        'provider_id',
        'name',
        'description',
        'provider_service_id',
        'price_per_1k',
        'cost_per_1k',
        'min_order',
        'max_order',
        'dripfeed',
        'refill',
        'cancel',
        'average_time',
        'description_extra',
        'is_active',
        'is_featured',
    ];

    protected function casts(): array
    {
        return [
            'price_per_1k' => 'decimal:4',
            'cost_per_1k' => 'decimal:4',
            'dripfeed' => 'boolean',
            'refill' => 'boolean',
            'cancel' => 'boolean',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
            'min_order' => 'integer',
            'max_order' => 'integer',
        ];
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function provider()
    {
        return $this->belongsTo(Provider::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function customRates()
    {
        return $this->hasMany(UserCustomRate::class);
    }

    /**
     * Calculate charge for a given quantity.
     */
    public function calculateCharge(int $quantity): float
    {
        return round(($quantity / 1000) * (float) $this->price_per_1k, 4);
    }

    /**
     * Calculate cost for a given quantity.
     */
    public function calculateCost(int $quantity): float
    {
        return round(($quantity / 1000) * (float) $this->cost_per_1k, 4);
    }

    /**
     * Validate order quantity.
     */
    public function validateQuantity(int $quantity): array
    {
        if ($quantity < $this->min_order) {
            return [false, "Minimum order is {$this->min_order}"];
        }
        if ($quantity > $this->max_order) {
            return [false, "Maximum order is {$this->max_order}"];
        }
        return [true, 'OK'];
    }
}
