<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CommercialProposalUnsubscribe extends Model
{
    protected $fillable = ['email'];

    public static function isUnsubscribed(string $email): bool
    {
        return self::query()->where('email', $email)->exists();
    }
}
