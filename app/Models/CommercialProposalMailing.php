<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CommercialProposalMailing extends Model
{
    protected $fillable = ['email', 'sent_at'];

    protected $casts = [
        'sent_at' => 'datetime',
    ];
}
