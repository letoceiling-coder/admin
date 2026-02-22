<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TelegramEvent extends Model
{
    protected $table = 'telegram_events';

    public $timestamps = false;

    protected $fillable = ['event_type', 'telegram_user_id', 'payload'];

    protected $casts = [
        'payload' => 'array',
    ];
}
