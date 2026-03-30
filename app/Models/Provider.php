<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class Provider extends Model
{
    protected $fillable = [
        'name',
        'api_url',
        'api_key',
        'is_active',
        'balance',
        'last_sync_at',
    ];

    protected $hidden = ['api_key'];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'balance' => 'decimal:4',
            'last_sync_at' => 'datetime',
        ];
    }

    public function services()
    {
        return $this->hasMany(Service::class);
    }

    /**
     * Fetch available services from the provider API.
     */
    public function fetchServices(): array
    {
        try {
            $response = Http::timeout(30)->post($this->api_url, [
                'key' => $this->api_key,
                'action' => 'services',
            ]);

            if ($response->successful()) {
                return $response->json() ?: [];
            }

            Log::error("Provider API error [{$this->name}]: " . $response->body());
            return [];
        } catch (\Exception $e) {
            Log::error("Provider API exception [{$this->name}]: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Place an order with the provider.
     */
    public function placeOrder(string $serviceId, string $link, int $quantity): ?array
    {
        try {
            $response = Http::timeout(30)->post($this->api_url, [
                'key' => $this->api_key,
                'action' => 'add',
                'service' => $serviceId,
                'link' => $link,
                'quantity' => $quantity,
            ]);

            if ($response->successful()) {
                return $response->json();
            }

            Log::error("Provider order error [{$this->name}]: " . $response->body());
            return null;
        } catch (\Exception $e) {
            Log::error("Provider order exception [{$this->name}]: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Check order status with the provider.
     */
    public function checkOrder(string $providerOrderId): ?array
    {
        try {
            $response = Http::timeout(30)->post($this->api_url, [
                'key' => $this->api_key,
                'action' => 'status',
                'order' => $providerOrderId,
            ]);

            if ($response->successful()) {
                return $response->json();
            }

            return null;
        } catch (\Exception $e) {
            Log::error("Provider status check exception [{$this->name}]: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Request a refill from the provider.
     */
    public function requestRefill(string $providerOrderId): ?array
    {
        try {
            $response = Http::timeout(30)->post($this->api_url, [
                'key' => $this->api_key,
                'action' => 'refill',
                'order' => $providerOrderId,
            ]);

            if ($response->successful()) {
                return $response->json();
            }

            return null;
        } catch (\Exception $e) {
            Log::error("Provider refill exception [{$this->name}]: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Sync provider balance.
     */
    public function syncBalance(): bool
    {
        try {
            $response = Http::timeout(30)->post($this->api_url, [
                'key' => $this->api_key,
                'action' => 'balance',
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $this->balance = $data['balance'] ?? 0;
                $this->last_sync_at = now();
                $this->save();
                return true;
            }

            return false;
        } catch (\Exception $e) {
            Log::error("Provider balance sync exception [{$this->name}]: " . $e->getMessage());
            return false;
        }
    }
}
