<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Subscriber extends Model
{
    protected $fillable = [
        'domain',
        'login',
        'subscription_start',
        'subscription_end',
        'is_active',
        'plan_id',
        'api_token',
        'payment_data',
    ];

    protected $hidden = ['api_token'];

    protected $casts = [
        'subscription_start' => 'date',
        'subscription_end' => 'date',
        'is_active' => 'boolean',
        'payment_data' => 'array',
    ];

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }
}
