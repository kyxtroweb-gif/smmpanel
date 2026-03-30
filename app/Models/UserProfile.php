<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class UserProfile extends Model
{
    protected $fillable = [
        'user_id',
        'api_key',
        'timezone',
        'language',
    ];

    protected $hidden = ['api_key'];

    protected static function booted(): void
    {
        static::creating(function (UserProfile $profile) {
            if (empty($profile->api_key)) {
                $profile->api_key = Str::random(64);
            }
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
