<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TelegramCallRequest extends Model
{
    public const STATUS_NEW = 'new';
    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_DONE = 'done';

    protected $table = 'telegram_call_requests';

    protected $fillable = [
        'telegram_user_id',
        'username',
        'name',
        'phone',
        'preferred_time',
        'comment',
        'status',
    ];
}
