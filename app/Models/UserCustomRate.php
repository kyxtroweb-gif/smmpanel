<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserCustomRate extends Model
{
    protected $fillable = ['user_id', 'service_id', 'custom_rate'];

    protected function casts(): array
    {
        return ['custom_rate' => 'decimal:4'];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }
}
