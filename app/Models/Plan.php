<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Plan extends Model
{
    protected $fillable = ['name', 'cost', 'is_active', 'limits'];

    protected $casts = [
        'cost' => 'decimal:2',
        'is_active' => 'boolean',
        'limits' => 'array',
    ];

    public function subscribers(): HasMany
    {
        return $this->hasMany(Subscriber::class);
    }
}
