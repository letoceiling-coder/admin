<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class SubscriptionApplication extends Model
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';

    protected $fillable = ['domain', 'name', 'email', 'api_token', 'expires_at', 'status'];

    protected $casts = [
        'expires_at' => 'datetime',
    ];

    public static function generateToken(): string
    {
        return Str::random(64);
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }
}
